<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Proposal;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $clients = User::where('role', 'client')->get();

        if ($clients->isEmpty()) {
            $this->command->warn('⚠️ Pas de clients. Lancez d\'abord UserSeeder.');
            return;
        }

        $cities = ['Cotonou', 'Porto-Novo', 'Abomey-Calavi', 'Parakou', 'Bohicon', 'Ouidah'];

        $demandes = [
            'btp-travaux' => [
                'title' => 'Rénovation complète d\'une salle de bain (8m²)',
                'description' => "Je souhaite rénover entièrement ma salle de bain : carrelage, plomberie, faïence murale et installation d'une douche à l'italienne. La pièce fait environ 8m². Je fournis le matériel si besoin, sinon vous pouvez me faire une proposition avec fourniture incluse.",
                'budget' => 450000,
                'deadline' => 14,
            ],
            'digital-tech' => [
                'title' => 'Création d\'un site vitrine pour mon entreprise',
                'description' => "Je gère une petite entreprise de vente d'articles ménagers et j'ai besoin d'un site vitrine professionnel avec présentation des produits, formulaire de contact et intégration WhatsApp. Je n'ai pas encore de logo, un accompagnement sur ce point serait un plus.",
                'budget' => 180000,
                'deadline' => 10,
            ],
            'maison-jardinage' => [
                'title' => 'Entretien régulier d\'un jardin de 200m²',
                'description' => "Je recherche un jardinier pour l'entretien mensuel de mon jardin : tonte, taille des haies et arrosage. Le jardin fait environ 200m² et se trouve dans une résidence avec accès facile.",
                'budget' => 25000,
                'deadline' => 7,
            ],
            'education-formation' => [
                'title' => 'Cours particuliers de mathématiques niveau terminale',
                'description' => "Mon fils est en classe de terminale scientifique et a besoin d'un soutien régulier en mathématiques avant le bac. Je recherche 2 séances par semaine, à domicile de préférence.",
                'budget' => 60000,
                'deadline' => 5,
            ],
            'evenementiel' => [
                'title' => 'Organisation d\'un anniversaire pour 50 personnes',
                'description' => "Je prépare l'anniversaire de mes 30 ans et je cherche un prestataire pour la décoration, la sonorisation et la coordination le jour J. L'événement aura lieu dans une salle déjà réservée, pour environ 50 invités.",
                'budget' => 300000,
                'deadline' => 20,
            ],
            'transport-livraison' => [
                'title' => 'Déménagement d\'un appartement 3 pièces',
                'description' => "Je déménage d'un appartement 3 pièces vers un autre quartier de la même ville. Il me faut un camion et 2 personnes pour aider au chargement/déchargement. Pas d'objets fragiles particuliers.",
                'budget' => 80000,
                'deadline' => 3,
            ],
            'beaute-bien-etre' => [
                'title' => 'Coiffure et maquillage pour un mariage',
                'description' => "Je me marie dans quelques semaines et je cherche une professionnelle pour la coiffure et le maquillage le jour J, avec un essai au préalable si possible.",
                'budget' => 70000,
                'deadline' => 21,
            ],
            'mecanique-automobile' => [
                'title' => 'Diagnostic et réparation d\'un bruit moteur',
                'description' => "Ma voiture (berline, environ 8 ans) fait un bruit suspect au démarrage depuis quelques jours. Je souhaite un diagnostic complet et un devis avant toute réparation.",
                'budget' => 50000,
                'deadline' => 4,
            ],
            'administration-juridique' => [
                'title' => 'Accompagnement pour la création d\'une entreprise',
                'description' => "Je souhaite créer une petite entreprise individuelle et j'ai besoin d'accompagnement pour les démarches administratives et juridiques : statuts, immatriculation, formalités fiscales.",
                'budget' => 100000,
                'deadline' => 15,
            ],
            'sante-social' => [
                'title' => 'Aide à domicile pour une personne âgée',
                'description' => "Je cherche une aide à domicile fiable pour accompagner ma mère (75 ans) quelques heures par jour : préparation des repas, compagnie, petites courses. Expérience avec les personnes âgées appréciée.",
                'budget' => 40000,
                'deadline' => 7,
            ],
        ];

        $proposalMessages = [
            "Bonjour, je suis intéressé par votre demande. J'ai plusieurs années d'expérience dans ce domaine et je peux vous garantir un travail soigné dans les délais annoncés.",
            "Bonjour, votre projet correspond exactement à mon domaine d'expertise. Je vous propose un devis détaillé et reste disponible pour en discuter avant de commencer.",
            "Bonjour, je peux prendre en charge votre demande rapidement. N'hésitez pas à me contacter pour plus de précisions sur votre besoin.",
            "Bonjour, j'ai déjà réalisé plusieurs projets similaires avec satisfaction client. Je vous propose ce tarif tout compris, matériel et déplacement inclus.",
        ];

        $createdRequests = 0;
        $createdProposals = 0;

        foreach ($demandes as $slug => $data) {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                continue;
            }

            $client = $clients->random();
            $createdAt = Carbon::now()->subDays(rand(1, 20));

            $serviceRequest = ServiceRequest::create([
                'client_id' => $client->id,
                'category_id' => $category->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'budget' => $data['budget'],
                'deadline' => $data['deadline'],
                'city' => $cities[array_rand($cities)],
                'status' => 'open',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $createdRequests++;

            // Prestataires ayant au moins un service dans cette catégorie
            $prestataireIds = Service::where('category_id', $category->id)
                ->pluck('user_id')
                ->unique()
                ->values();

            $proposalsCount = min($prestataireIds->count(), rand(0, 3));

            if ($proposalsCount > 0) {
                $chosenPrestataires = $prestataireIds->shuffle()->take($proposalsCount);
                $basePrice = (float) $data['budget'];

                foreach ($chosenPrestataires as $prestataireId) {
                    $variance = rand(-15, 10) / 100;
                    $proposedPrice = max(2000, round($basePrice * (1 + $variance), -2));

                    Proposal::create([
                        'service_request_id' => $serviceRequest->id,
                        'user_id' => $prestataireId,
                        'message' => $proposalMessages[array_rand($proposalMessages)],
                        'proposed_price' => $proposedPrice,
                        'delivery_time' => max(1, $data['deadline'] - rand(0, 3)),
                        'status' => 'pending',
                        'created_at' => $createdAt->copy()->addHours(rand(1, 48)),
                        'updated_at' => $createdAt->copy()->addHours(rand(1, 48)),
                    ]);

                    $createdProposals++;
                }

                $serviceRequest->update(['proposals_count' => $proposalsCount]);
            }
        }

        $this->command->info("✅ $createdRequests demandes créées avec $createdProposals proposition(s) au total");
    }
}
