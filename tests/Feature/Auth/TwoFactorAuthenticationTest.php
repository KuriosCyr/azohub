<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\TwoFactorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Double authentification par email, pour tous les comptes (demandée explicitement par le
// client, maintenant qu'un envoi d'email fiable existe — cf. le passage des notifications en
// file d'attente). Le mot de passe seul (POST /login) n'ouvre plus de session ; il faut ensuite
// saisir le code à 6 chiffres reçu par email.
class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function loginWithPassword(User $user): void
    {
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        // LoginRequest::authenticate() utilise Auth::once() (délibérément : aucune session ne
        // doit être ouverte avant validation du code). Contrairement à une vraie requête HTTP
        // (un processus PHP-FPM distinct à chaque fois), les appels successifs de ce test
        // partagent la même instance de guard : sans ce logout, l'utilisateur "once"-authentifié
        // resterait vu comme connecté par les appels suivants dans CE test, ce qu'aucune vraie
        // requête HTTP ultérieure ne verrait jamais.
        \Illuminate\Support\Facades\Auth::logout();
    }

    public function test_challenge_page_is_inaccessible_without_a_pending_login(): void
    {
        $response = $this->get(route('two-factor.challenge'));
        $response->assertRedirect(route('login'));
    }

    public function test_the_correct_code_completes_the_login(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->loginWithPassword($user);
        $code = session('2fa_code');
        $this->assertNotNull($code, 'Le code aurait dû être stocké en session après le mot de passe.');

        $response = $this->post(route('two-factor.store'), ['code' => $code]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));

        Notification::assertSentTo($user, TwoFactorCode::class);
    }

    public function test_an_incorrect_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->loginWithPassword($user);

        $response = $this->post(route('two-factor.store'), ['code' => '000000']);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }

    public function test_an_expired_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->loginWithPassword($user);
        $code = session('2fa_code');

        // Simule l'expiration (10 minutes) sans attendre.
        session(['2fa_expires_at' => now()->subMinute()->timestamp]);

        $response = $this->post(route('two-factor.store'), ['code' => $code]);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }

    public function test_too_many_wrong_attempts_forces_a_fresh_login(): void
    {
        $user = User::factory()->create();
        $this->loginWithPassword($user);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('two-factor.store'), ['code' => '000000']);
        }

        // Le 6e essai (même avec le bon code, resté en session) doit être bloqué.
        $code = session('2fa_code');
        $response = $this->post(route('two-factor.store'), ['code' => $code]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_resend_sends_a_new_code(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->loginWithPassword($user);

        $firstCode = session('2fa_code');

        $this->post(route('two-factor.resend'));

        $this->assertNotSame($firstCode, session('2fa_code'));
        Notification::assertSentTo($user, TwoFactorCode::class, 2);
    }
}
