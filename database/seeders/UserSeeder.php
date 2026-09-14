<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Mot de passe commun aux comptes de démo. Surchargeable via DEMO_PASSWORD
     * dans .env. Ne jamais lancer ce seeder sur une base accessible publiquement.
     */
    private function demoPassword(): string
    {
        return env('DEMO_PASSWORD', 'Azohub@Demo2026');
    }

    public function run(): void
    {
        $password = Hash::make($this->demoPassword());

        // 1. Admin
        User::create([
            'name' => 'Admin Azohub',
            'email' => 'admin@azohub.bj',
            'password' => $password,
            'phone' => '+229 97 00 00 01',
            'role' => 'admin',
            'city' => 'Cotonou',
            'is_active' => true,
            'identity_verified' => true,
        ]);

        $this->command->info('✅ Admin créé');

        // 2. Prestataires (10, un profil par ville/niveau différent)
        $prestataires = [
            ['name' => 'Yves Adjovi', 'city' => 'Cotonou', 'level' => 'expert', 'completed_orders' => 68],
            ['name' => 'Sylvie Hounkpatin', 'city' => 'Porto-Novo', 'level' => 'confirme', 'completed_orders' => 24],
            ['name' => 'Marc Gbaguidi', 'city' => 'Abomey-Calavi', 'level' => 'expert', 'completed_orders' => 91],
            ['name' => 'Rachel Dossou', 'city' => 'Parakou', 'level' => 'nouveau', 'completed_orders' => 2],
            ['name' => 'Daniel Zinsou', 'city' => 'Bohicon', 'level' => 'confirme', 'completed_orders' => 15],
            ['name' => 'Angèle Sessou', 'city' => 'Ouidah', 'level' => 'confirme', 'completed_orders' => 33],
            ['name' => 'Pierre Medenou', 'city' => 'Natitingou', 'level' => 'nouveau', 'completed_orders' => 0],
            ['name' => 'Florence Akpaki', 'city' => 'Lokossa', 'level' => 'expert', 'completed_orders' => 54],
            ['name' => 'Emmanuel Kplé', 'city' => 'Savalou', 'level' => 'nouveau', 'completed_orders' => 4],
            ['name' => 'Nadège Assogba', 'city' => 'Cotonou', 'level' => 'confirme', 'completed_orders' => 19],
        ];

        foreach ($prestataires as $i => $p) {
            $email = strtolower(str_replace(' ', '.', $p['name'])) . '@prestataire.bj';

            User::create([
                'name' => $p['name'],
                'email' => $email,
                'password' => $password,
                'phone' => '+229 9' . rand(0, 9) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'role' => 'prestataire',
                'bio' => "Professionnel expérimenté basé à {$p['city']}. Satisfaction client garantie !",
                'city' => $p['city'],
                'availability' => 'disponible',
                'rating' => $p['completed_orders'] > 0 ? round(rand(38, 50) / 10, 1) : 0,
                'total_reviews' => 0,
                'completed_orders' => $p['completed_orders'],
                'level' => $p['level'],
                'identity_verified' => $p['level'] !== 'nouveau',
                'wallet_balance' => 0,
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ ' . count($prestataires) . ' prestataires créés');

        // 3. Clients (8)
        $clients = [
            ['name' => 'Jean Akpaki', 'city' => 'Cotonou'],
            ['name' => 'Marie Assogba', 'city' => 'Porto-Novo'],
            ['name' => 'Paul Dossou', 'city' => 'Abomey-Calavi'],
            ['name' => 'Esther Hounkonnou', 'city' => 'Parakou'],
            ['name' => 'André Behanzin', 'city' => 'Bohicon'],
            ['name' => 'Chantal Glele', 'city' => 'Ouidah'],
            ['name' => 'Georges Sagbo', 'city' => 'Cotonou'],
            ['name' => 'Odette Monteiro', 'city' => 'Porto-Novo'],
        ];

        foreach ($clients as $c) {
            $email = strtolower(str_replace(' ', '.', $c['name'])) . '@client.bj';

            User::create([
                'name' => $c['name'],
                'email' => $email,
                'password' => $password,
                'phone' => '+229 6' . rand(0, 9) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'role' => 'client',
                'city' => $c['city'],
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ ' . count($clients) . ' clients créés');
        $this->command->info('✅ Total: 1 admin + ' . count($prestataires) . ' prestataires + ' . count($clients) . ' clients = ' . (1 + count($prestataires) + count($clients)) . ' utilisateurs');
    }
}
