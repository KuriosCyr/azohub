<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les commandes terminées
        $completedOrders = Order::where('status', 'completed')->get();

        if ($completedOrders->isEmpty()) {
            $this->command->warn('⚠️ Aucune commande terminée. Lancez d\'abord OrderSeeder.');
            return;
        }

        $comments = [
            5 => [
                'Excellent travail ! Très professionnel et ponctuel. Je recommande vivement.',
                'Service impeccable, prestataire sérieux. Travail bien fait dans les délais.',
                'Parfait ! Exactement ce que je cherchais. Merci beaucoup.',
                'Très satisfait du résultat. Communication fluide et efficace.',
                'Top ! Prestataire à l\'écoute et compétent. Je referai appel à ses services.',
                'Travail de qualité, propre et soigné. Rien à redire !',
                'Vraiment content ! Bon rapport qualité-prix et excellent service.',
            ],
            4 => [
                'Bon travail dans l\'ensemble. Quelques petits ajustements mais globalement satisfait.',
                'Bien réalisé. Prestataire compétent même si communication parfois lente.',
                'Service correct, conforme à mes attentes. Je recommande.',
                'Bon professionnel. Délais respectés et travail sérieux.',
                'Satisfait du résultat. Quelques détails à améliorer mais bon dans l\'ensemble.',
            ],
            3 => [
                'Travail correct mais pourrait être amélioré. Délais un peu longs.',
                'Moyen. Le service correspond à la description mais rien d\'exceptionnel.',
                'Correct sans plus. Quelques problèmes de communication.',
                'Acceptable. Le travail est fait mais j\'attendais mieux.',
            ],
            2 => [
                'Décevant. Travail bâclé et plusieurs retards.',
                'Pas satisfait. Communication difficile et résultat moyen.',
                'Service en dessous de mes attentes. Beaucoup de relances nécessaires.',
            ],
            1 => [
                'Très décevant. Travail non conforme et aucun professionnalisme.',
                'Mauvaise expérience. Je ne recommande pas.',
            ],
        ];

        $count = 0;

        foreach ($completedOrders as $order) {
            // 80% des commandes terminées ont un avis
            if (rand(1, 10) > 8) {
                continue;
            }

            // Distribution réaliste des notes (majoritairement 4-5)
            $ratingDistribution = [
                5 => 50, // 50% de 5 étoiles
                4 => 30, // 30% de 4 étoiles
                3 => 15, // 15% de 3 étoiles
                2 => 4,  // 4% de 2 étoiles
                1 => 1,  // 1% de 1 étoile
            ];

            $rand = rand(1, 100);
            $rating = 5;
            $cumul = 0;
            foreach ($ratingDistribution as $note => $percent) {
                $cumul += $percent;
                if ($rand <= $cumul) {
                    $rating = $note;
                    break;
                }
            }

            // Commentaire selon la note
            $comment = $comments[$rating][array_rand($comments[$rating])];

            // Notes détaillées (légèrement variables autour de la note globale)
            $qualityRating = max(1, min(5, $rating + rand(-1, 1)));
            $communicationRating = max(1, min(5, $rating + rand(-1, 1)));
            $timelinessRating = max(1, min(5, $rating + rand(-1, 1)));

            Review::create([
                'order_id' => $order->id,
                'reviewer_id' => $order->client_id,
                'reviewee_id' => $order->prestataire_id,
                'service_id' => $order->service_id,
                'rating' => $rating,
                'comment' => $comment,
                'quality_rating' => $qualityRating,
                'communication_rating' => $communicationRating,
                'timeliness_rating' => $timelinessRating,
                'review_type' => 'client_to_prestataire',
                'is_visible' => true,
                'created_at' => $order->validated_at->addHours(rand(1, 48)),
            ]);

            // Mettre à jour les stats du service
            $order->service->updateRating();

            // Mettre à jour les stats du prestataire
            $order->prestataire->updateRating();

            $count++;
        }

        $this->command->info("✅ $count avis créés sur " . $completedOrders->count() . " commandes terminées");
    }
}