<?php

use App\Livewire\HomePage;
use App\Livewire\ServicesIndex;
use App\Livewire\ServiceShow;
use App\Livewire\OrderCreate;
use App\Livewire\PrestataireProfile;
use App\Livewire\PrestataireDashboard;
use App\Livewire\PrestataireWallet;
use App\Livewire\PrestataireSubscription;
use App\Livewire\PrestataireStatistics;
use App\Livewire\ClientDashboard;
use App\Livewire\FaqPage;
use App\Livewire\ServiceRequestCreate;
use App\Livewire\ServiceRequestsIndex;
use App\Livewire\ServiceRequestShow;
use App\Livewire\ProposalAccept;
use App\Livewire\ConversationsIndex;
use App\Livewire\ConversationShow;
use App\Livewire\CustomOfferAccept;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\DisputeController;
use Illuminate\Support\Facades\Route;

// ============================================
// PAGES PUBLIQUES
// ============================================

Route::get('/', HomePage::class)->name('home');
Route::get('/services', ServicesIndex::class)->name('services.index');
Route::get('/services/{service:slug}', ServiceShow::class)->name('services.show');

// Pages informatives
Route::view('/how-it-works', 'pages.how-it-works')->name('how-it-works');
Route::get('/faq', FaqPage::class)->name('faq');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');

Route::get('/ads/{advertisement}/click', [AdvertisementController::class, 'click'])->name('ads.click');

// ============================================
// AUTHENTIFICATION
// ============================================

require __DIR__.'/auth.php';

// ============================================
// ROUTES PROTÉGÉES (AUTH)
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {

    // Redirection dashboard selon le rôle
    Route::get('/dashboard', function () {
        if (auth()->user()->isPrestataire()) {
            return redirect()->route('prestataire.dashboard');
        }
        return redirect()->route('client.dashboard');
    })->name('dashboard');
    
    // Dashboard prestataire
    Route::get('/prestataire/dashboard', PrestataireDashboard::class)
        ->middleware('prestataire')
        ->name('prestataire.dashboard');

    // Portefeuille prestataire
    Route::get('/prestataire/wallet', PrestataireWallet::class)
        ->middleware('prestataire')
        ->name('prestataire.wallet');

    // Abonnement prestataire
    Route::get('/prestataire/abonnement', PrestataireSubscription::class)
        ->middleware('prestataire')
        ->name('prestataire.subscription');

    // Statistiques prestataire (avantage Pro/Premium)
    Route::get('/prestataire/statistiques', PrestataireStatistics::class)
        ->middleware('prestataire')
        ->name('prestataire.statistics');

    // Dashboard client
    Route::get('/client/dashboard', ClientDashboard::class)
        ->middleware('client')
        ->name('client.dashboard');
    
    // ============================================
    // PROFIL UTILISATEUR
    // ============================================
    
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password');
        Route::delete('/', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('destroy');
    });

    // ============================================
    // VÉRIFICATION D'IDENTITÉ
    // ============================================

    Route::prefix('identity-verification')->name('identity-verification.')->group(function () {
        Route::post('/', [\App\Http\Controllers\IdentityVerificationController::class, 'store'])->name('store');
        Route::get('/{user}/document', [\App\Http\Controllers\IdentityVerificationController::class, 'show'])->name('show');
    });
    
    // ============================================
    // GESTION SERVICES (PRESTATAIRES)
    // ============================================
    
    Route::middleware('prestataire')->prefix('prestataire')->name('prestataire.')->group(function () {
        Route::resource('services', ServiceController::class);
        Route::post('services/{service}/toggle', [ServiceController::class, 'toggleActive'])->name('services.toggle');
        Route::post('services/{service}/sponsor', [ServiceController::class, 'toggleSponsored'])->name('services.sponsor');
    });

    // ============================================
    // GESTION COMMANDES
    // ============================================
    
    Route::prefix('orders')->name('orders.')->group(function () {
        // Créer une commande
        Route::get('/create', OrderCreate::class)->middleware('client')->name('create');

        // Voir une commande
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        
        // Actions prestataire
        Route::middleware('prestataire')->group(function () {
            Route::post('/{order}/accept', [OrderController::class, 'accept'])->name('accept');
            Route::post('/{order}/refuse', [OrderController::class, 'refuse'])->name('refuse');
            Route::post('/{order}/deliver', [OrderController::class, 'markAsDelivered'])->name('deliver');
        });
        
        // Actions client
        Route::middleware('client')->group(function () {
            Route::post('/{order}/validate', [OrderController::class, 'validate'])->name('validate');
            Route::post('/{order}/request-revision', [OrderController::class, 'requestRevision'])->name('request-revision');
            Route::post('/{order}/pay', [PaymentController::class, 'initiate'])->name('pay');
        });
        
        // Actions communes
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{order}/deliverable/{index}', [OrderController::class, 'downloadDeliverable'])->name('deliverable.download');
        Route::post('/{order}/dispute', [DisputeController::class, 'store'])->name('dispute.store');
        
        // Messages
        Route::post('/{order}/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('/{order}/messages/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    });

    // ============================================
    // DEMANDES & PROPOSITIONS (NÉGOCIATION)
    // ============================================

    Route::prefix('demandes')->name('service-requests.')->group(function () {
        Route::get('/', ServiceRequestsIndex::class)->name('index');
        Route::get('/nouvelle', ServiceRequestCreate::class)->middleware('client')->name('create');
        Route::get('/{serviceRequest}', ServiceRequestShow::class)->name('show');
        Route::get('/{serviceRequest}/propositions/{proposal}/accepter', ProposalAccept::class)
            ->middleware('client')
            ->name('proposals.accept');
    });

    // ============================================
    // MESSAGERIE DIRECTE & OFFRES PERSONNALISÉES
    // ============================================

    Route::prefix('messages')->name('conversations.')->group(function () {
        Route::get('/', ConversationsIndex::class)->name('index');
        Route::get('/{conversation}', ConversationShow::class)->name('show');
        Route::get('/attachment/{message}/{index}', [ConversationController::class, 'downloadAttachment'])
            ->name('attachment.download');
    });

    Route::post('/messages/start', [ConversationController::class, 'start'])
        ->middleware('client')
        ->name('conversations.start');

    Route::get('/offres-personnalisees/{offer}/accepter', CustomOfferAccept::class)
        ->middleware('client')
        ->name('custom-offers.accept');

    // ============================================
    // GESTION AVIS
    // ============================================
    
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/orders/{order}/create', [ReviewController::class, 'create'])->name('create');
        Route::post('/orders/{order}', [ReviewController::class, 'store'])->name('store');
        Route::get('/{review}', [ReviewController::class, 'show'])->name('show');
    });

    // ============================================
    // SIGNALEMENTS
    // ============================================

    Route::post('/services/{service}/report', [ReportController::class, 'store'])->name('reports.store');

    // ============================================
    // TÉLÉCHARGEMENT PIÈCES JOINTES MESSAGES
    // ============================================
    
    Route::get('/messages/{message}/attachment/{index}', [MessageController::class, 'downloadAttachment'])
        ->name('messages.attachment.download');
});

// ============================================
// PAIEMENT (FEDAPAY)
// ============================================

Route::get('/payments/callback/{order}', [PaymentController::class, 'callback'])->name('payments.callback');
Route::get('/payments/subscription-callback/{subscription}', [PaymentController::class, 'subscriptionCallback'])->name('payments.subscription-callback');
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// ============================================
// PROFIL PUBLIC PRESTATAIRE (EN DERNIER)
// ============================================

Route::get('/prestataire/{username}', PrestataireProfile::class)->name('prestataire.profile');