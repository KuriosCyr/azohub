<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'BTP & Travaux',
                'slug' => 'btp-travaux',
                'description' => 'Maçonnerie, plomberie, électricité, menuiserie, peinture',
                'icon' => 'Hammer',
                'color' => '#F59E0B',
                'order' => 1,
            ],
            [
                'name' => 'Digital & Tech',
                'slug' => 'digital-tech',
                'description' => 'Développement web/mobile, design graphique, marketing digital, rédaction',
                'icon' => 'Code',
                'color' => '#3B82F6',
                'order' => 2,
            ],
            [
                'name' => 'Maison & Jardinage',
                'slug' => 'maison-jardinage',
                'description' => 'Nettoyage, jardinage, déménagement, réparations domestiques',
                'icon' => 'Home',
                'color' => '#10B981',
                'order' => 3,
            ],
            [
                'name' => 'Éducation & Formation',
                'slug' => 'education-formation',
                'description' => 'Cours particuliers, soutien scolaire, formation professionnelle',
                'icon' => 'GraduationCap',
                'color' => '#8B5CF6',
                'order' => 4,
            ],
            [
                'name' => 'Événementiel',
                'slug' => 'evenementiel',
                'description' => 'Organisation d\'événements, traiteur, décoration, photographie',
                'icon' => 'Calendar',
                'color' => '#EC4899',
                'order' => 5,
            ],
            [
                'name' => 'Transport & Livraison',
                'slug' => 'transport-livraison',
                'description' => 'Livraison, déménagement, transport de personnes',
                'icon' => 'Truck',
                'color' => '#EF4444',
                'order' => 6,
            ],
            [
                'name' => 'Beauté & Bien-être',
                'slug' => 'beaute-bien-etre',
                'description' => 'Coiffure, maquillage, massage, esthétique',
                'icon' => 'Sparkles',
                'color' => '#F97316',
                'order' => 7,
            ],
            [
                'name' => 'Mécanique & Automobile',
                'slug' => 'mecanique-automobile',
                'description' => 'Réparation auto/moto, entretien, dépannage',
                'icon' => 'Wrench',
                'color' => '#64748B',
                'order' => 8,
            ],
            [
                'name' => 'Administration & Juridique',
                'slug' => 'administration-juridique',
                'description' => 'Services administratifs, conseil juridique, comptabilité',
                'icon' => 'FileText',
                'color' => '#0891B2',
                'order' => 9,
            ],
            [
                'name' => 'Santé & Social',
                'slug' => 'sante-social',
                'description' => 'Aide à domicile, garde d\'enfants, soins infirmiers',
                'icon' => 'Heart',
                'color' => '#DC2626',
                'order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✅ 10 catégories créées avec succès !');
    }
}