<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_are_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create account');
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_valid_credentials_authenticate_the_user(): void
    {
        $user = User::factory()->create([
            'password' => 'correct-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_do_not_authenticate_the_user(): void
    {
        $user = User::factory()->create([
            'password' => 'correct-password',
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_creates_a_user_and_authenticates_them(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Laundry Customer',
            'email' => 'customer@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Laundry Customer',
            'email' => 'customer@example.com',
            'role' => 'pelanggan',
        ]);
        $this->assertTrue(Hash::check('secure-password', User::where('email', 'customer@example.com')->value('password')));
    }

    public function test_logout_ends_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
