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
    /**
     * @OA\Get(
     *     path="/api/accounts",
     *     summary="Liste tous les comptes de l'utilisateur authentifié",
     *     tags={"Accounts"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Account")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $accounts = Auth::user()->accounts;
        return response()->json($accounts);
    }

    /**
     * @OA\Post(
     *     path="/api/accounts",
     *     summary="Créer un nouveau compte bancaire",
     *     tags={"Accounts"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"current", "savings"}, example="current")
     *         )
     *     ),
     *     @OA\Response(
     *         response=211,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Account")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/accounts/{account}",
     *     summary="Afficher les détails d'un compte avec ses transactions",
     *     tags={"Accounts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="account",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/Account"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="transactions",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/Transaction")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function show(Account $account)
    {
        $this->authorize('view', $account);
        return response()->json($account->load('transactions'));
    }

    /**
     * @OA\Post(
     *     path="/api/accounts/{account}/deposit",
     *     summary="Effectuer un dépôt sur un compte",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="account",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", minimum=0.01, example=100.00),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Dépôt mensuel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dépôt réussi",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dépôt réussi"),
     *             @OA\Property(property="account", ref="#/components/schemas/Account"),
     *             @OA\Property(property="transaction", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/accounts/{account}/withdraw",
     *     summary="Effectuer un retrait depuis un compte",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="account",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", minimum=0.01, example=50.00),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Retrait distributeur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retrait réussi",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Retrait réussi"),
     *             @OA\Property(property="account", ref="#/components/schemas/Account"),
     *             @OA\Property(property="transaction", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit"
     *     )
     * )
     */
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
