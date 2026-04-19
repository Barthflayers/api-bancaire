<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_account()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/accounts', [
            'type' => 'current',
        ]);

        $response->assertStatus(211);
        $this->assertCount(1, $user->accounts);
    }

    public function test_user_can_list_their_accounts()
    {
        $user = User::factory()->create();
        Account::factory()->count(3)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/accounts');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_user_can_deposit_money()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'balance' => 100]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/accounts/{$account->id}/deposit", [
            'amount' => 50,
            'description' => 'Test deposit'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(150, $account->fresh()->balance);
    }

    public function test_user_can_withdraw_money()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'balance' => 100]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/accounts/{$account->id}/withdraw", [
            'amount' => 40,
            'description' => 'Test withdrawal'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(60, $account->fresh()->balance);
    }

    public function test_user_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'balance' => 100]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/accounts/{$account->id}/withdraw", [
            'amount' => 150,
        ]);

        $response->assertStatus(400);
        $this->assertEquals(100, $account->fresh()->balance);
    }
}
