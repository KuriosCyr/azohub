<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Suite à un audit externe : anonymizeAndDelete() ne vérifiait ni solde, ni retrait en attente,
// ni commande en cours — un prestataire pouvait supprimer son compte avec de l'argent qui lui
// était dû ou une commande en cours, devenant "Utilisateur supprimé" et injoignable.
class AccountDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_with_unwithdrawn_balance_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['wallet_balance' => 5000, 'password' => bcrypt('password')]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        $this->assertNotNull($user->fresh(), 'Le compte ne doit pas avoir été supprimé.');
    }

    public function test_account_with_a_pending_withdrawal_request_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['role' => 'prestataire', 'password' => bcrypt('password')]);

        WithdrawalRequest::create([
            'prestataire_id' => $user->id,
            'amount' => 10000,
            'payment_method' => 'mtn_momo',
            'phone_number' => '97000000',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertSessionHas('error');
        $this->assertNotNull($user->fresh());
    }

    public function test_account_with_an_active_order_cannot_be_deleted(): void
    {
        $client = User::factory()->create(['password' => bcrypt('password')]);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'in_progress',
            'payment_status' => 'held',
        ]);

        $response = $this->actingAs($client)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertSessionHas('error');
        $this->assertNotNull($client->fresh());
    }

    public function test_account_with_no_blocker_can_still_be_deleted(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertRedirect('/');
        $this->assertNull(User::find($user->id), 'Le compte aurait dû être supprimé (soft delete).');
    }

    // accountDeletionBlockers() déplacée de ProfileController (privée) vers User (publique) lors
    // du 7e audit externe, pour être réutilisable par l'action de suppression admin dans
    // Filament — elle doit rester directement appelable sur le modèle.
    public function test_account_deletion_blockers_is_callable_directly_on_the_user_model(): void
    {
        $blockedUser = User::factory()->create(['wallet_balance' => 5000]);
        $this->assertNotEmpty($blockedUser->accountDeletionBlockers());

        $freeUser = User::factory()->create();
        $this->assertEmpty($freeUser->accountDeletionBlockers());
    }
}
