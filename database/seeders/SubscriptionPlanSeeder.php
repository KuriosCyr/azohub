<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuit',
                'slug' => 'gratuit',
                'description' => 'Parfait pour débuter sur Azohub',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_services' => 2,
                'commission_rate' => 15.00,
                'features' => [
                    '2 services maximum',
                    'Commission de 15%',
                    'Support par email',
                    'Profil de base',
                ],
                'is_popular' => false,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Pour les prestataires réguliers',
                'price' => 5000,
                'billing_period' => 'monthly',
                'max_services' => 10,
                'commission_rate' => 10.00,
                'features' => [
                    '10 services maximum',
                    'Commission réduite à 10%',
                    'Badge "Pro" visible',
                    'Apparition prioritaire',
                    'Support prioritaire',
                    'Statistiques avancées',
                ],
                'is_popular' => true,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Pour les professionnels confirmés',
                'price' => 15000,
                'billing_period' => 'monthly',
                'max_services' => null, // Illimité
                'commission_rate' => 5.00,
                'features' => [
                    'Services illimités',
                    'Commission minimale de 5%',
                    'Badge "Premium" doré',
                    'Top position garantie',
                    'Support VIP 24/7',
                    'Statistiques complètes',
                    'Sponsorisation de services',
                    'Gestionnaire de compte dédié',
                ],
                'is_popular' => false,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $this->command->info('✅ 3 plans d\'abonnement créés avec succès !');
    }
}