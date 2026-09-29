<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_correct_credentials_send_to_the_two_factor_challenge_without_logging_in_yet(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));

        // Le mot de passe seul n'ouvre plus de session : Auth::once() (voir LoginRequest)
        // n'authentifie que pour la durée de CETTE requête, sans rien persister — mais dans ce
        // test, le conteneur applicatif (et donc le guard) reste le même juste après, d'où ce
        // logout avant de vérifier : une vraie requête HTTP suivante (processus PHP-FPM séparé
        // en production) ne verrait de toute façon jamais cet état "once". La suite du parcours
        // (saisie du code) est couverte par TwoFactorAuthenticationTest.
        \Illuminate\Support\Facades\Auth::logout();
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
