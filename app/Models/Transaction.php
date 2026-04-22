<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     title="Transaction",
 *     description="Transaction bancaire (dépôt, retrait, transfert)",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="account_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", enum={"deposit", "withdrawal", "transfer"}, example="deposit"),
 *     @OA\Property(property="amount", type="number", format="float", example=500.00),
 *     @OA\Property(property="description", type="string", example="Dépôt espèces"),
 *     @OA\Property(property="related_account_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'description',
        'related_account_id',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function relatedAccount()
    {
        return $this->belongsTo(Account::class, 'related_account_id');
    }
}
