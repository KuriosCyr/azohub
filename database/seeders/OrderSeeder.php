<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $clients = User::where('role', 'client')->get();
        $services = Service::where('is_active', true)->with('prestataire')->get();

        if ($clients->isEmpty() || $services->isEmpty()) {
            $this->command->warn('⚠️ Pas assez de clients ou services. Lancez d\'abord UserSeeder et ServiceSeeder.');
            return;
        }

        $statuses = [
            'pending_payment' => 5,  // 5 commandes
            'paid' => 10,            // 10 commandes
            'in_progress' => 15,     // 15 commandes
            'delivered' => 10,       // 10 commandes
            'completed' => 40,       // 40 commandes
            'cancelled' => 5,        // 5 commandes
        ];

        $count = 0;
        $orderNumber = 1;

        foreach ($statuses as $status => $quantity) {
            for ($i = 0; $i < $quantity; $i++) {
                $client = $clients->random();
                $service = $services->random();
                $prestataire = $service->prestataire;

                // Éviter qu'un prestataire commande son propre service
                while ($client->id === $prestataire->id) {
                    $client = $clients->random();
                }

                $amount = $service->price;
                $commission = round($amount * 0.10, 2); // 10% commission
                $prestataireAmount = $amount - $commission;

                // Dates selon le statut
                $createdAt = Carbon::now()->subDays(rand(1, 90));
                $expectedDelivery = $createdAt->copy()->addDays($service->delivery_time);
                $paidAt = null;
                $acceptedAt = null;
                $deliveredAt = null;
                $validatedAt = null;

                if (in_array($status, ['paid', 'in_progress', 'delivered', 'completed'])) {
                    $paidAt = $createdAt->copy()->addMinutes(rand(5, 120));
                }

                if (in_array($status, ['in_progress', 'delivered', 'completed'])) {
                    $acceptedAt = $paidAt->copy()->addHours(rand(1, 24));
                }

                if (in_array($status, ['delivered', 'completed'])) {
                    $deliveredAt = $createdAt->copy()->addDays(rand(1, $service->delivery_time + 2));
                }

                if ($status === 'completed') {
                    $validatedAt = $deliveredAt->copy()->addHours(rand(1, 72));
                }

                $paymentStatus = 'pending';
                if (in_array($status, ['paid', 'in_progress', 'delivered'])) {
                    $paymentStatus = 'held';
                }
                if ($status === 'completed') {
                    $paymentStatus = 'released';
                }
                if ($status === 'cancelled') {
                    $paymentStatus = 'refunded';
                }

                $order = Order::create([
                    'order_number' => 'AZH-' . date('Y') . '-' . str_pad($orderNumber, 5, '0', STR_PAD_LEFT),
                    'client_id' => $client->id,
                    'prestataire_id' => $prestataire->id,
                    'service_id' => $service->id,
                    'requirements' => "Intervention demandée pour $service->title. Merci de me contacter avant.",
                    'amount' => $amount,
                    'commission' => $commission,
                    'prestataire_amount' => $prestataireAmount,
                    'delivery_time' => $service->delivery_time,
                    'expected_delivery_at' => $expectedDelivery,
                    'accepted_at' => $acceptedAt,
                    'delivered_at' => $deliveredAt,
                    'validated_at' => $validatedAt,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Créer le paiement correspondant pour les commandes effectivement payées
                if ($paidAt !== null) {
                    Payment::create([
                        'order_id' => $order->id,
                        'user_id' => $client->id,
                        'transaction_id' => 'DEMO-' . Str::upper(Str::random(12)),
                        'payment_method' => collect(['mtn_momo', 'moov_money', 'celtiis_cash'])->random(),
                        'phone_number' => $client->phone,
                        'amount' => $amount,
                        'status' => 'success',
                        'type' => 'order_payment',
                        'paid_at' => $paidAt,
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]);
                }

                $orderNumber++;
                $count++;

                // Mettre à jour les stats du service
                if (in_array($status, ['completed'])) {
                    $service->increment('total_orders');
                    $service->increment('orders_count');
                }

                // Mettre à jour les stats du prestataire
                if ($status === 'completed') {
                    $prestataire->increment('completed_orders');
                }
            }

            $this->command->info("✅ $quantity commandes '$status' créées");
        }

        $this->command->info("✅ Total: $count commandes créées");
    }
}