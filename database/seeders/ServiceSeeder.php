<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $prestataires = User::where('role', 'prestataire')->get();
        $categories = Category::all();

        // Images Unsplash par catégorie (mots-clés de recherche)
        $categoryImages = [
            'BTP & Travaux' => 'construction,plumbing,electrical,painting,building',
            'Digital & Tech' => 'laptop,coding,design,website,technology',
            'Maison & Jardinage' => 'garden,cleaning,home,interior,plants',
            'Éducation & Formation' => 'education,learning,teacher,book,student',
            'Événementiel' => 'party,wedding,event,celebration,music',
            'Transport & Livraison' => 'truck,delivery,moving,transport,car',
            'Beauté & Bien-être' => 'beauty,massage,spa,wellness,fitness',
            'Mécanique & Automobile' => 'mechanic,car,repair,automotive,garage',
            'Administration & Juridique' => 'office,business,legal,accounting,documents',
            'Santé & Social' => 'healthcare,nursing,childcare,medical,care',
        ];

        // Services par catégorie avec descriptions détaillées
        $servicesData = [
            'BTP & Travaux' => [
                ['name' => 'Maçonnerie et construction', 'desc' => 'Construction de murs, fondations, dalles. Travaux de maçonnerie générale pour maisons et bâtiments.'],
                ['name' => 'Plomberie sanitaire', 'desc' => 'Installation et réparation de tuyauterie, robinetterie, WC, douches. Dépannage rapide.'],
                ['name' => 'Électricité générale', 'desc' => 'Installation électrique complète, mise aux normes, dépannage. Électricien professionnel certifié.'],
                ['name' => 'Peinture intérieure/extérieure', 'desc' => 'Peinture de qualité pour intérieur et extérieur. Devis gratuit, finition soignée.'],
                ['name' => 'Carrelage et faïence', 'desc' => 'Pose de carrelage sol et mur, faïence salle de bain. Travail précis et rapide.'],
                ['name' => 'Menuiserie bois/aluminium', 'desc' => 'Fabrication et pose de portes, fenêtres, placards. Menuiserie sur mesure.'],
                ['name' => 'Climatisation et froid', 'desc' => 'Installation, entretien et réparation de climatiseurs. Intervention rapide 24h/24.'],
                ['name' => 'Étanchéité toiture', 'desc' => 'Traitement étanchéité toiture, terrasse. Garantie anti-infiltration.'],
                ['name' => 'Ferraillage béton armé', 'desc' => 'Ferraillage pour dalles, poteaux, linteaux. Respect des normes de construction.'],
                ['name' => 'Pose de faux plafond', 'desc' => 'Installation faux plafond placoplatre, staff. Finition professionnelle.'],
            ],
            'Digital & Tech' => [
                ['name' => 'Création site web vitrine', 'desc' => 'Site web professionnel responsive, optimisé SEO. Livraison sous 7 jours.'],
                ['name' => 'Développement e-commerce', 'desc' => 'Boutique en ligne complète avec paiement mobile money. Solution clé en main.'],
                ['name' => 'Design logo et identité visuelle', 'desc' => 'Création logo unique + charte graphique. Plusieurs propositions, révisions illimitées.'],
                ['name' => 'Community management', 'desc' => 'Gestion réseaux sociaux, création de contenu, engagement communauté. Croissance garantie.'],
                ['name' => 'Référencement SEO/SEA', 'desc' => 'Optimisation Google, publicité Facebook/Instagram. Résultats mesurables.'],
                ['name' => 'Application mobile', 'desc' => 'Développement app iOS/Android. Design moderne, performance optimale.'],
                ['name' => 'Montage vidéo professionnel', 'desc' => 'Montage vidéo pro pour événements, publicité, réseaux sociaux. Rendu HD.'],
                ['name' => 'Photographie événementielle', 'desc' => 'Photos professionnelles mariage, baptême, anniversaire. Livraison rapide.'],
                ['name' => 'Infographie et flyers', 'desc' => 'Création flyers, affiches, bannières publicitaires. Impression disponible.'],
                ['name' => 'Maintenance informatique', 'desc' => 'Réparation PC, installation logiciels, récupération données. Intervention à domicile.'],
            ],
            'Maison & Jardinage' => [
                ['name' => 'Nettoyage complet maison', 'desc' => 'Ménage approfondi toutes pièces, vitres, sols. Produits écologiques fournis.'],
                ['name' => 'Entretien jardin', 'desc' => 'Tonte, désherbage, taille arbustes. Jardin impeccable toute l\'année.'],
                ['name' => 'Tonte de pelouse', 'desc' => 'Tonte régulière pelouse, ramassage déchets verts. Service hebdomadaire ou mensuel.'],
                ['name' => 'Taille arbres et haies', 'desc' => 'Élagage arbres, taille haies, évacuation branches. Équipement professionnel.'],
                ['name' => 'Désinsectisation', 'desc' => 'Traitement cafards, moustiques, termites. Produits certifiés, garantie 3 mois.'],
                ['name' => 'Décoration intérieure', 'desc' => 'Conseil déco, aménagement espaces, choix couleurs. Style personnalisé.'],
                ['name' => 'Repassage linge', 'desc' => 'Repassage professionnel à domicile. Linge impeccable, pliage soigné.'],
                ['name' => 'Grand ménage déménagement', 'desc' => 'Nettoyage après déménagement, remise en état. Logement prêt à habiter.'],
                ['name' => 'Paysagiste création', 'desc' => 'Création jardin, pelouse, massifs fleurs. Design paysager sur mesure.'],
                ['name' => 'Lavage vitrerie', 'desc' => 'Nettoyage vitres intérieur/extérieur, baies vitrées. Sans traces garanties.'],
            ],
            'Éducation & Formation' => [
                ['name' => 'Cours particuliers maths', 'desc' => 'Soutien maths collège/lycée. Progression rapide, méthodes efficaces.'],
                ['name' => 'Soutien scolaire primaire', 'desc' => 'Aide aux devoirs, révisions. Enseignant expérimenté, pédagogie adaptée.'],
                ['name' => 'Cours de français', 'desc' => 'Grammaire, orthographe, expression écrite. Tous niveaux, préparation examens.'],
                ['name' => 'Cours d\'anglais', 'desc' => 'Anglais conversationnel et business. Natif ou bilingue, méthode interactive.'],
                ['name' => 'Formation Excel/Word', 'desc' => 'Maîtrise Pack Office pour professionnels. Certification possible.'],
                ['name' => 'Coaching professionnel', 'desc' => 'Accompagnement carrière, reconversion, développement compétences.'],
                ['name' => 'Préparation concours', 'desc' => 'Préparation intensive concours administratifs. Méthodologie éprouvée.'],
                ['name' => 'Cours de guitare', 'desc' => 'Guitare acoustique/électrique, tous niveaux. Solfège ou tablatures.'],
                ['name' => 'Cours de danse', 'desc' => 'Danse moderne, afro, salsa. Cours individuels ou groupe.'],
                ['name' => 'Formation entrepreneuriat', 'desc' => 'Créer et gérer son entreprise. Business plan, marketing, finance.'],
            ],
            'Événementiel' => [
                ['name' => 'Animation mariage', 'desc' => 'Animation complète mariage: musique, jeux, ambiance. DJ professionnel.'],
                ['name' => 'DJ soirée privée', 'desc' => 'DJ pour anniversaire, fête. Playlist personnalisée, matériel sono inclus.'],
                ['name' => 'Traiteur buffet', 'desc' => 'Buffet africain/européen pour événements. Menu sur mesure, service inclus.'],
                ['name' => 'Location matériel sono', 'desc' => 'Sonorisation, éclairage, vidéoprojection. Livraison et installation.'],
                ['name' => 'Décoration salle fête', 'desc' => 'Décoration sur thème, fleurs, ballons, tissus. Montage et démontage.'],
                ['name' => 'Photographe mariage', 'desc' => 'Reportage photo mariage, album premium. Retouches professionnelles.'],
                ['name' => 'Organisation anniversaire', 'desc' => 'Organisation clé en main anniversaire enfant/adulte. Animation comprise.'],
                ['name' => 'Magicien anniversaire enfant', 'desc' => 'Spectacle magie interactive pour enfants. Rires et émerveillement garantis.'],
                ['name' => 'Orchestre live', 'desc' => 'Groupe musical live pour événements. Répertoire varié, acoustique ou amplifié.'],
                ['name' => 'Candy bar', 'desc' => 'Bar à bonbons personnalisé, décoration thème. Gourmandises à volonté.'],
            ],
            'Transport & Livraison' => [
                ['name' => 'Déménagement complet', 'desc' => 'Déménagement avec emballage, transport, déballage. Équipe professionnelle.'],
                ['name' => 'Coursier express', 'desc' => 'Livraison rapide colis, documents Cotonou. Suivi temps réel.'],
                ['name' => 'Chauffeur privé', 'desc' => 'Chauffeur disponible à la demande. Ponctuel, discret, véhicule climatisé.'],
                ['name' => 'Location voiture avec chauffeur', 'desc' => 'Véhicule + chauffeur journée/semaine. Carburant inclus.'],
                ['name' => 'Transport marchandises', 'desc' => 'Transport colis volumineux, meubles. Camion pick-up disponible.'],
                ['name' => 'Livraison colis', 'desc' => 'Livraison colis particuliers/entreprises. Service fiable et rapide.'],
                ['name' => 'Taxi aéroport', 'desc' => 'Transfert aéroport Cotonou, tarif fixe. Disponible 24h/24.'],
                ['name' => 'Transport scolaire', 'desc' => 'Navette scolaire sécurisée, trajets réguliers. Véhicule assure.'],
                ['name' => 'Location camionnette', 'desc' => 'Location camionnette déménagement, avec ou sans chauffeur.'],
                ['name' => 'Convoyage véhicule', 'desc' => 'Convoyage voiture longue distance. Chauffeur expérimenté, assurance.'],
            ],
            'Beauté & Bien-être' => [
                ['name' => 'Coiffure afro femme', 'desc' => 'Coiffure afro, tresses, locks, tissage. Salon ou à domicile.'],
                ['name' => 'Barbier à domicile', 'desc' => 'Coupe homme, barbe, rasage. Service professionnel à domicile.'],
                ['name' => 'Maquillage mariage', 'desc' => 'Maquillage mariée, demoiselles honneur. Essai inclus, tenue longue durée.'],
                ['name' => 'Manucure pédicure', 'desc' => 'Soins ongles mains/pieds, pose vernis, french. Produits de qualité.'],
                ['name' => 'Massage relaxant', 'desc' => 'Massage détente, anti-stress, huiles essentielles. À domicile possible.'],
                ['name' => 'Épilation cire', 'desc' => 'Épilation cire toutes zones, homme/femme. Cire orientale disponible.'],
                ['name' => 'Tresses africaines', 'desc' => 'Tresses collées, nattes, box braids. Durabilité garantie.'],
                ['name' => 'Défrisage cheveux', 'desc' => 'Défrisage professionnel, soin réparateur inclus. Cheveux lisses et brillants.'],
                ['name' => 'Soin visage', 'desc' => 'Nettoyage profond, gommage, masque, hydratation. Peau éclatante.'],
                ['name' => 'Coach sportif personnel', 'desc' => 'Programme sport personnalisé, suivi nutrition. Résultats visibles.'],
            ],
            'Mécanique & Automobile' => [
                ['name' => 'Révision automobile', 'desc' => 'Révision complète véhicule, contrôle points essentiels. Pièces d\'origine.'],
                ['name' => 'Vidange et filtres', 'desc' => 'Vidange huile moteur, changement filtres. Huile de qualité recommandée.'],
                ['name' => 'Diagnostic panne', 'desc' => 'Diagnostic électronique panne, valise multimarque. Devis gratuit.'],
                ['name' => 'Réparation climatisation', 'desc' => 'Recharge gaz clim, réparation fuite. Clim froide garantie.'],
                ['name' => 'Changement batterie', 'desc' => 'Fourniture et pose batterie neuve, test alternateur. Garantie constructeur.'],
                ['name' => 'Géométrie et équilibrage', 'desc' => 'Réglage géométrie, équilibrage roues. Usure pneus réduite.'],
                ['name' => 'Entretien moto', 'desc' => 'Révision moto complète, vidange, freins, chaîne. Moto prête à rouler.'],
                ['name' => 'Dépannage 24h/24', 'desc' => 'Remorquage, dépannage sur place jour/nuit. Intervention rapide.'],
                ['name' => 'Carrosserie peinture', 'desc' => 'Débosselage, peinture retouche ou complète. Cabine de peinture.'],
                ['name' => 'Lavage auto complet', 'desc' => 'Lavage extérieur/intérieur, polish, lustrage. Voiture comme neuve.'],
            ],
            'Administration & Juridique' => [
                ['name' => 'Comptabilité entreprise', 'desc' => 'Tenue comptabilité PME, bilan, liasse fiscale. Expert-comptable diplômé.'],
                ['name' => 'Déclaration fiscale', 'desc' => 'Déclaration impôts particuliers/entreprises. Optimisation fiscale légale.'],
                ['name' => 'Création d\'entreprise', 'desc' => 'Accompagnement création entreprise A à Z. Statuts, RCCM, IFU.'],
                ['name' => 'Traduction français-anglais', 'desc' => 'Traduction documents officiels, certifiée. Anglais/français natif.'],
                ['name' => 'Rédaction contrats', 'desc' => 'Rédaction contrats commerciaux, travail, bail. Juridiquement valable.'],
                ['name' => 'Conseil juridique', 'desc' => 'Consultation juridique tous domaines. Avocat expérimenté.'],
                ['name' => 'Secrétariat administratif', 'desc' => 'Gestion administrative, courriers, classement. Organisation efficace.'],
                ['name' => 'Tenue de livres', 'desc' => 'Enregistrement opérations comptables quotidiennes. Rigueur garantie.'],
                ['name' => 'Conseil fiscal', 'desc' => 'Optimisation fiscale, stratégie défiscalisation. Conformité assurée.'],
                ['name' => 'Rédaction CV', 'desc' => 'CV professionnel percutant, lettre motivation. Taux réponse amélioré.'],
            ],
            'Santé & Social' => [
                ['name' => 'Aide à domicile personnes âgées', 'desc' => 'Assistance quotidienne seniors, compagnie, ménage. Personnel formé.'],
                ['name' => 'Garde d\'enfants', 'desc' => 'Nounou diplômée, garde régulière ou occasionnelle. Références vérifiées.'],
                ['name' => 'Infirmier à domicile', 'desc' => 'Soins infirmiers domicile, pansements, injections. Diplôme d\'État.'],
                ['name' => 'Accompagnement médical', 'desc' => 'Accompagnement rendez-vous médicaux, aide déplacements. Bienveillance.'],
                ['name' => 'Baby-sitting occasionnel', 'desc' => 'Garde enfants soirée, weekend. Baby-sitter expérimentée, ludique.'],
                ['name' => 'Soutien scolaire handicap', 'desc' => 'Accompagnement scolaire enfants à besoins spéciaux. Pédagogie adaptée.'],
                ['name' => 'Livraison médicaments', 'desc' => 'Livraison ordonnance pharmacie à domicile. Service rapide et discret.'],
                ['name' => 'Soins après hospitalisation', 'desc' => 'Soins post-opératoire domicile, surveillance. Infirmier diplômé.'],
                ['name' => 'Aide ménagère senior', 'desc' => 'Ménage, courses, repas pour personnes âgées. Aide respectueuse.'],
                ['name' => 'Nounou résidente', 'desc' => 'Nounou à demeure, garde nuit, aide parentale. Formation petite enfance.'],
            ],
        ];

        $count = 0;

        foreach ($servicesData as $categoryName => $services) {
            $category = $categories->where('name', $categoryName)->first();
            
            if (!$category) continue;

            // Mots-clés Unsplash pour cette catégorie
            $imageKeyword = $categoryImages[$categoryName] ?? 'business';

            foreach ($services as $index => $serviceData) {
                $prestataire = $prestataires->random();

                // URL image Unsplash (800x600, thème aléatoire de la catégorie)
                $imageUrl = "https://source.unsplash.com/800x600/?{$imageKeyword}&sig=" . ($count + $index);

                Service::create([
                    'user_id' => $prestataire->id,
                    'category_id' => $category->id,
                    'title' => $serviceData['name'],
                    'slug' => Str::slug($serviceData['name'] . '-' . $prestataire->id),
                    'description' => $serviceData['desc'],
                    'what_included' => "✅ Matériel professionnel inclus\n✅ Déplacement dans la zone\n✅ Garantie satisfaction\n✅ Paiement sécurisé via Azohub",
                    'price' => rand(5000, 150000),
                    'price_type' => rand(0, 10) > 3 ? 'a_partir_de' : 'fixe',
                    'delivery_time' => rand(1, 14),
                    'city' => $prestataire->city,
                    'cover_image' => $imageUrl, // Image Unsplash
                    'rating' => rand(35, 50) / 10,
                    'total_orders' => rand(0, 100),
                    'total_reviews' => rand(0, 50),
                    'status' => 'active',
                    'is_active' => rand(0, 10) > 1, // 90% actifs
                    'is_featured' => rand(0, 10) > 8, // 20% featured
                ]);

                $count++;
            }
        }

        $this->command->info("✅ $count services créés avec images");
    }
}