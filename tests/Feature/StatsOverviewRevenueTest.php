<?php

namespace Tests\Feature;

use App\Filament\Widgets\StatsOverview;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Corrigé suite à un audit externe : "Bénéfices Azohub" sommait commission + client_fee sans
// soustraire le crédit de parrainage ou le code promo consommé — surestimant le revenu réel.
// prestataire_amount n'étant jamais réduit par une remise, ce qu'Azohub garde vraiment est bien
// commission + frais - réductions, pas commission + frais seuls.
class StatsOverviewRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_subtracts_referral_credit_and_promo_discount(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        // Marge brute (commission + client_fee) = 1500, réduite de 800 (300 crédit + 500 promo)
        // : la marge réelle gardée par Azohub est donc 700, pas 1500.
        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'client_fee' => 500,
            'referral_credit_applied' => 300,
            'promo_discount_applied' => 500,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('700 FCFA')
            ->assertDontSee('1 500 FCFA');
    }

    // Corrigé suite à un 2e audit externe : un abonnement payé va intégralement à Azohub (pas de
    // part prestataire à en retirer, contrairement à une commande), mais n'était jamais compté
    // dans "Bénéfices Azohub".
    public function test_revenue_includes_subscription_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        Payment::create([
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 3000,
            'status' => 'success',
            'type' => 'subscription',
            'paid_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('3 000 FCFA');
    }

    // Corrigé suite à un 3e audit externe : le regroupement mensuel des abonnements se basait sur
    // created_at (lancement du paiement) au lieu de paid_at (confirmation) — un paiement lancé fin
    // de mois et confirmé le mois suivant était compté sur le mauvais mois. Reproduit exactement ce
    // cas : created_at le mois dernier, paid_at ce mois-ci.
    public function test_revenue_groups_subscription_payments_by_confirmation_date_not_launch_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $payment = Payment::create([
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 3000,
            'status' => 'success',
            'type' => 'subscription',
            'paid_at' => now(),
        ]);
        // Lancé le mois dernier : si le code se basait encore sur created_at, ce paiement
        // n'apparaîtrait pas dans "ce mois".
        \Illuminate\Support\Facades\DB::table('payments')->where('id', $payment->id)
            ->update(['created_at' => now()->subMonth()]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('3 000 FCFA ce mois');
    }

    // Corrigé suite à un 3e audit externe (suivi du point laissé de côté au 2e audit) : une
    // commande soldée par un remboursement PARTIEL (litige tranché "partial_refund") passe en
    // payment_status='refund_pending' comme une annulation complète, mais Azohub y garde une
    // marge réduite (le prestataire reçoit une part proportionnelle, jamais zéro) — l'exclure
    // purement du CA comme le reste de refund_pending sous-comptait ce revenu bien réel.
    public function test_revenue_includes_the_margin_kept_on_a_partially_refunded_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        // Sans frais client ni réduction, total_charged = amount = 10000. Marge totale
        // (commission) = 1000. Remboursement partiel de 4000 sur 10000 payés (40%) : Azohub garde
        // 60% de sa marge, soit 600.
        $order = Order::create([
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

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'success',
            'type' => 'order_payment',
            'paid_at' => now(),
        ]);

        $order->partialRefund(4000.0, 'Litige tranché en faveur partielle du client.');

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('600 FCFA');
    }

    // Corrigé suite à un 4e audit externe : la marge d'un remboursement partiel (test précédent)
    // était filtrée sur payment_status='refund_pending' — une fois l'admin confirme le
    // remboursement via Payment::confirmRefund() (qui fait alors passer order.payment_status à
    // 'refunded'), cette marge disparaissait du CA au moment précis où l'admin fait bien son
    // travail, alors qu'aucun argent supplémentaire n'a bougé à cet instant. Reproduit le même
    // scénario que le test précédent puis confirme le remboursement.
    public function test_revenue_from_a_partial_refund_is_unchanged_once_the_admin_confirms_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
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

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'success',
            'type' => 'order_payment',
            'paid_at' => now(),
        ]);

        $order->partialRefund(4000.0, 'Litige tranché en faveur partielle du client.');

        // Avant confirmation : la marge (600) apparaît déjà (test précédent).
        Livewire::actingAs($admin)->test(StatsOverview::class)->assertSee('600 FCFA');

        // L'admin rembourse le client sur FedaPay puis confirme — payment_status de la commande
        // passe alors à 'refunded'.
        $payment->fresh()->confirmRefund();
        $this->assertSame('refunded', $order->fresh()->payment_status);

        // Le CA ne doit PAS avoir bougé : c'est la même marge de 600, toujours gardée par Azohub.
        Livewire::actingAs($admin)->test(StatsOverview::class)->assertSee('600 FCFA');
    }

    // Corrigé suite à un 4e audit externe : symétrique au correctif déjà appliqué aux abonnements
    // (3e audit) — le CA des commandes se basait sur created_at (création) au lieu de paid_at
    // (confirmation du paiement). Une commande créée un mois et payée le mois suivant (reprise
    // via OrderCreate après un paiement abandonné) était comptée sur le mauvais mois.
    public function test_revenue_groups_order_payments_by_confirmation_date_not_creation_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);
        // Créée le mois dernier...
        \Illuminate\Support\Facades\DB::table('orders')->where('id', $order->id)
            ->update(['created_at' => now()->subMonth()]);

        // ...mais payée (confirmée) ce mois-ci.
        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'success',
            'type' => 'order_payment',
            'paid_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('1 000 FCFA ce mois');
    }

    // Le libellé précise maintenant la part encore en escrow (audit externe — 4e audit) : le CA
    // compte aussi les commandes 'held' (pas encore définitivement libérées au prestataire).
    public function test_revenue_description_mentions_the_amount_still_held_in_escrow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('encore en escrow');
    }

    // Une commande ENTIÈREMENT annulée (pas de remboursement partiel) ne doit toujours rien
    // ajouter au CA : Azohub n'y garde effectivement aucune marge.
    public function test_revenue_excludes_a_fully_refunded_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'client_fee' => 500,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'in_progress',
            'payment_status' => 'held',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'success',
            'type' => 'order_payment',
            'paid_at' => now(),
        ]);

        $order->refund('Annulée.');

        $component = Livewire::actingAs($admin)->test(StatsOverview::class);
        $component->assertDontSee('1 500 FCFA')->assertDontSee('1 050 FCFA');
    }
}
