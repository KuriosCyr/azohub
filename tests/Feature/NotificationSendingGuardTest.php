<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AccountStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

// Corrigé suite à un 8e audit externe : depuis que Order::client()/prestataire() utilisent
// withTrashed() (7e audit), des notifications sont adressées à des comptes anonymisés/supprimés
// — leur e-mail devenu "...@azohub.invalid" fait échouer l'envoi dans la file (failed_jobs) sans
// but utile. Un listener central (AppServiceProvider::boot()) annule maintenant l'envoi (tous
// canaux) dès que le destinataire est un compte supprimé.
class NotificationSendingGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_is_cancelled_for_a_deleted_account(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $result = Event::until(new NotificationSending($user->fresh(), new AccountStatusChanged(false, 'Test'), 'mail'));

        $this->assertFalse($result, 'L\'envoi doit être annulé pour un compte supprimé.');
    }

    public function test_sending_proceeds_normally_for_an_active_account(): void
    {
        $user = User::factory()->create();

        $result = Event::until(new NotificationSending($user, new AccountStatusChanged(false, 'Test'), 'mail'));

        $this->assertNotFalse($result, 'L\'envoi ne doit pas être annulé pour un compte actif.');
    }
}
