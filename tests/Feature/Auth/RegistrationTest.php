<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+229 00 00 00 00',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'client',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    // Le lien de parrainage (?ref=CODE) est capturé via un champ caché du formulaire, quel que
    // soit le rôle choisi à l'inscription — voir User::maybeRewardReferrer() pour la suite
    // (récompense déclenchée à la première commande du filleul).
    public function test_registering_with_a_valid_referral_code_links_the_new_user_to_its_owner(): void
    {
        $referrer = User::factory()->create(['role' => 'prestataire']);

        $this->post('/register', [
            'name' => 'Filleul Test',
            'email' => 'filleul@example.com',
            'phone' => '+229 00 00 00 00',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'client',
            'terms' => '1',
            'ref' => $referrer->referral_code,
        ]);

        $newUser = User::where('email', 'filleul@example.com')->firstOrFail();
        $this->assertEquals($referrer->id, $newUser->referred_by);
    }

    public function test_registering_with_an_invalid_referral_code_does_not_block_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test-invalid-ref@example.com',
            'phone' => '+229 00 00 00 00',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'client',
            'terms' => '1',
            'ref' => 'DOESNOTEXIST',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $newUser = User::where('email', 'test-invalid-ref@example.com')->firstOrFail();
        $this->assertNull($newUser->referred_by);
    }
}
