<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Double authentification par email pour le panel admin, rendue obligatoire (demande explicite
// du client, reprend une suggestion d'un audit externe). Utilise le mécanisme officiel de
// Filament (Filament\Auth\MultiFactor\Email\EmailAuthentication, isRequired: true) plutôt qu'une
// implémentation maison — voir AdminPanelProvider.
class AdminMultiFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_mfa_configured_is_redirected_to_set_it_up_before_entering_the_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'has_email_authentication' => false]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertRedirect();
        $this->assertStringContainsString('multi-factor', $response->headers->get('Location'));
    }

    public function test_admin_with_mfa_configured_can_access_the_panel_normally(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'has_email_authentication' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_user_model_satisfies_the_has_email_authentication_contract(): void
    {
        $user = User::factory()->create(['has_email_authentication' => false]);
        $this->assertFalse($user->hasEmailAuthentication());

        $user->toggleEmailAuthentication(true);
        $this->assertTrue($user->fresh()->hasEmailAuthentication());
    }
}
