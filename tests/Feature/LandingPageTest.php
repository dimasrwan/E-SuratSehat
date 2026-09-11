<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 1. Guest opening / sees Landing Page (HTTP 200).
     */
    public function test_guest_sees_landing_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('E-Surat Sehat');
        $response->assertSee('Klinik UIN Ar-Raniry Banda Aceh');
        $response->assertSee('Masuk ke Sistem');
    }

    /**
     * 2. Authenticated user opening / receives Dashboard view.
     */
    public function test_authenticated_user_receives_dashboard_on_landing_route(): void
    {
        $user = User::create([
            'name' => 'Operator Test',
            'email' => 'operator@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    /**
     * 3. Guest opening /dashboard is redirected to /login.
     */
    public function test_guest_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * 4. Guest opening /pemeriksaan is redirected to /login.
     */
    public function test_guest_redirected_from_pemeriksaan_to_login(): void
    {
        $response = $this->get('/pemeriksaan');
        $response->assertRedirect('/login');
    }

    /**
     * 5. Guest opening /admin/users is redirected to /login.
     */
    public function test_guest_redirected_from_admin_users_to_login(): void
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }
}
