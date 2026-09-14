<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding d\'Azohub...');
        $this->command->newLine();

        // 1. Catégories
        $this->command->info('📁 Création des catégories...');
        $this->call(CategorySeeder::class);
        $this->command->newLine();

        // 2. Plans d'abonnement
        $this->command->info('💎 Création des plans d\'abonnement...');
        $this->call(SubscriptionPlanSeeder::class);
        $this->command->newLine();

        // 3. Utilisateurs (Admin + Prestataires + Clients)
        $this->command->info('👥 Création des utilisateurs...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        // 4. Services
        $this->command->info('📦 Création des services...');
        $this->call(ServiceSeeder::class);
        $this->command->newLine();

        // 5. Commandes
        $this->command->info('🛒 Création des commandes...');
        $this->call(OrderSeeder::class);
        $this->command->newLine();

        // 6. Avis
        $this->command->info('⭐ Création des avis...');
        $this->call(ReviewSeeder::class);
        $this->command->newLine();

        // Récapitulatif
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->newLine();
        
        // Statistiques
        $this->command->table(
            ['📊 Modèle', 'Quantité'],
            [
                ['Categories', \App\Models\Category::count()],
                ['Plans abonnement', \App\Models\SubscriptionPlan::count()],
                ['Utilisateurs', \App\Models\User::count()],
                ['  → Admins', \App\Models\User::where('role', 'admin')->count()],
                ['  → Prestataires', \App\Models\User::where('role', 'prestataire')->count()],
                ['  → Clients', \App\Models\User::where('role', 'client')->count()],
                ['Services', \App\Models\Service::count()],
                ['  → Actifs', \App\Models\Service::where('is_active', true)->count()],
                ['Commandes', \App\Models\Order::count()],
                ['  → Terminées', \App\Models\Order::where('status', 'completed')->count()],
                ['Avis', \App\Models\Review::count()],
            ]
        );

        $this->command->newLine();

        // Identifiants de connexion
        $demoPassword = env('DEMO_PASSWORD', 'Azohub@Demo2026');
        $this->command->info('🔐 IDENTIFIANTS DE TEST (LOCAL UNIQUEMENT) :');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line('👨‍💼 Admin Panel Filament :');
        $this->command->line('   URL      : http://localhost:8000/admin');
        $this->command->line('   Email    : admin@azohub.bj');
        $this->command->line('   Password : ' . $demoPassword);
        $this->command->newLine();

        $this->command->line('👔 Prestataire (exemples) :');
        $this->command->line('   Email    : yves.adjovi@prestataire.bj');
        $this->command->line('   Email    : sylvie.hounkpatin@prestataire.bj');
        $this->command->line('   Password : ' . $demoPassword);
        $this->command->newLine();

        $this->command->line('🙋 Client (exemples) :');
        $this->command->line('   Email    : marie.assogba@client.bj');
        $this->command->line('   Email    : paul.dossou@client.bj');
        $this->command->line('   Password : ' . $demoPassword);
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->warn('⚠️  Ce mot de passe est pour le développement local uniquement.');
        $this->command->warn('    Ne jamais lancer ce seeder sur une base accessible publiquement.');

        $this->command->newLine();
        $this->command->info('🎉 Azohub est prêt à être utilisé !');
        $this->command->info('🌐 Visitez : http://localhost:8000');
    }
}