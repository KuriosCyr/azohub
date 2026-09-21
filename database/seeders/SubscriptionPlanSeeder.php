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
                    '6 à 15 services selon votre niveau',
                    'Commission de 15 % (10 % pendant vos 3 premiers mois)',
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
                'price' => 3000,
                'yearly_price' => 30000,
                'billing_period' => 'monthly',
                'max_services' => 10,
                'commission_rate' => 10.00,
                'features' => [
                    '10 services minimum (plus selon votre niveau)',
                    'Commission réduite à 10%',
                    'Badge "Pro" visible',
                    'Apparition prioritaire',
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
                'price' => 9000,
                'yearly_price' => 90000,
                'billing_period' => 'monthly',
                'max_services' => null, // Illimité
                'commission_rate' => 5.00,
                'features' => [
                    'Services illimités',
                    'Commission minimale de 5%',
                    'Badge "Premium" doré',
                    'Top position garantie',
                    'Statistiques complètes',
                    'Sponsorisation d\'un service',
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