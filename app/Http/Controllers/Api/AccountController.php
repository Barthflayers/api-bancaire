<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Auth::user()->accounts;
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:current,savings',
        ]);

        $account = Auth::user()->accounts()->create([
            'account_number' => 'ACC-' . strtoupper(Str::random(10)),
            'type' => $request->type,
            'balance' => 0,
        ]);

        return response()->json($account, 211);
    }

    public function show(Account $account)
    {
        $this->authorize('view', $account);
        return response()->json($account->load('transactions'));
    }

    public function deposit(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $account) {
            $account->increment('balance', $request->amount);

            $transaction = $account->transactions()->create([
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Dépôt réussi',
                'account' => $account,
                'transaction' => $transaction
            ]);
        });
    }

    public function withdraw(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($account->balance < $request->amount) {
            return response()->json(['message' => 'Solde insuffisant'], 400);
        }

        return DB::transaction(function () use ($request, $account) {
            $account->decrement('balance', $request->amount);

            $transaction = $account->transactions()->create([
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Retrait réussi',
                'account' => $account,
                'transaction' => $transaction
            ]);
        });
    }
}
