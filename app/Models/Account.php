<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Account",
 *     title="Account",
 *     description="Compte bancaire d'un utilisateur",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="account_number", type="string", example="ACC-ABC1234567"),
 *     @OA\Property(property="balance", type="number", format="float", example=1000.50),
 *     @OA\Property(property="type", type="string", enum={"current", "savings"}, example="current"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-22T10:00:00Z")
 * )
 */
class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'balance',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
