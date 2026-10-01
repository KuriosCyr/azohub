<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\CustomOffer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Proposal;
use App\Models\ServiceRequest;
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

    // Corrigé suite à un 8e audit externe : un client avec un paiement refund_pending (commande
    // annulée, remboursement pas encore traité par l'admin) pouvait être anonymisé avant que
    // l'admin ait traité le remboursement — pas de perte d'argent (payments.phone_number est
    // indépendant du compte), mais l'admin perd le contexte (nom, e-mail) pour le relancer.
    public function test_account_with_a_pending_refund_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        Payment::create([
            'user_id' => $user->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 5000,
            'status' => 'refund_pending',
            'refund_amount_due' => 5000,
            'type' => 'order_payment',
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertSessionHas('error');
        $this->assertNotNull($user->fresh());
    }

    public function test_account_with_no_blocker_can_still_be_deleted(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertRedirect('/');
        $this->assertNull(User::find($user->id), 'Le compte aurait dû être supprimé (soft delete).');
    }

    // Corrigé suite à un 8e audit externe : une proposition/offre encore 'pending' de ce
    // prestataire ne doit plus rester payable une fois son compte supprimé (anonymizeAndDelete()
    // appelle cancelPendingNegotiations()) — sans ça, le client pouvait quand même l'accepter.
    public function test_deleting_the_account_cancels_its_pending_proposals_and_offers(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'password' => bcrypt('password')]);
        $client = User::factory()->create(['role' => 'client']);
        $category = Category::create(['name' => 'Design', 'slug' => 'design-' . uniqid(), 'is_active' => true]);

        $serviceRequest = ServiceRequest::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'title' => 'Besoin d\'un logo',
            'description' => str_repeat('Détail de la demande. ', 5),
            'city' => 'Cotonou',
            'status' => 'open',
        ]);

        $proposal = Proposal::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $prestataire->id,
            'message' => 'Je peux le faire.',
            'proposed_price' => 5000,
            'delivery_time' => 3,
            'status' => 'pending',
        ]);

        $conversation = Conversation::create(['client_id' => $client->id, 'prestataire_id' => $prestataire->id]);
        $offer = CustomOffer::create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'title' => 'Offre sur mesure',
            'description' => 'Détail.',
            'price' => 8000,
            'delivery_days' => 5,
            'revisions_included' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($prestataire)
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $this->assertSame('rejected', $proposal->fresh()->status);
        $this->assertSame('expired', $offer->fresh()->status);
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
