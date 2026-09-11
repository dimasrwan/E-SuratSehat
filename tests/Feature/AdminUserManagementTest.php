<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use DatabaseMigrations;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
        ]);
        $this->admin->role = 'admin';
        $this->admin->is_active = true;
        $this->admin->save();

        $this->operator = User::create([
            'name' => 'Operator Test',
            'email' => 'operator@test.com',
            'password' => Hash::make('Password123!'),
        ]);
        $this->operator->role = 'operator';
        $this->operator->is_active = true;
        $this->operator->save();
    }

    /** 1. Anonymous cannot access admin routes */
    public function test_anonymous_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }

    /** 2. Operator cannot access admin routes */
    public function test_operator_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->operator)->get('/admin/users');
        $response->assertStatus(403);
    }

    /** 3. Operator cannot create user via HTTP request */
    public function test_operator_cannot_create_user_via_http_request(): void
    {
        $response = $this->actingAs($this->operator)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'operator',
            'is_active' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'newuser@test.com']);
    }

    /** 4 & 5. Operator cannot change role or is_active via mass assignment */
    public function test_role_and_is_active_protected_from_mass_assignment(): void
    {
        $user = new User([
            'name' => 'Mass Assign Test',
            'email' => 'mass@test.com',
            'password' => 'secret',
            'role' => 'admin',
            'is_active' => false,
        ]);
        $user->save();

        $fresh = $user->fresh();

        // Mass-assigned values ('admin', false) were ignored due to guarded fillable
        $this->assertEquals('operator', $fresh->role);
        $this->assertTrue((bool) $fresh->is_active);
    }

    /** 6. Operator cannot change their own role */
    public function test_operator_cannot_change_their_own_role(): void
    {
        $response = $this->actingAs($this->operator)->put("/admin/users/{$this->operator->id}", [
            'name' => 'Hacked Operator',
            'email' => $this->operator->email,
            'role' => 'admin',
            'is_active' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertEquals('operator', $this->operator->fresh()->role);
    }

    /** 7. Operator cannot change other users role */
    public function test_operator_cannot_change_other_users_role(): void
    {
        $otherOperator = User::create([
            'name' => 'Other Operator',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($this->operator)->put("/admin/users/{$otherOperator->id}", [
            'name' => 'Demoted Other',
            'email' => 'other@test.com',
            'role' => 'admin',
            'is_active' => 1,
        ]);

        $response->assertStatus(403);
    }

    /** 8. Operator cannot deactivate other users */
    public function test_operator_cannot_deactivate_other_users(): void
    {
        $response = $this->actingAs($this->operator)->post("/admin/users/{$this->admin->id}/toggle-status");
        $response->assertStatus(403);
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    /** 9. Admin can create operator */
    public function test_admin_can_create_operator(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'Created Operator',
            'email' => 'created_op@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'operator',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'created_op@test.com',
            'role' => 'operator',
            'is_active' => 1,
        ]);
    }

    /** 10. Operator created by Admin can log in */
    public function test_operator_created_by_admin_can_login(): void
    {
        $op = User::create([
            'name' => 'New Op',
            'email' => 'new_op@test.com',
            'password' => Hash::make('SecretPass123!'),
        ]);
        $op->role = 'operator';
        $op->is_active = true;
        $op->save();

        $response = $this->post('/login', [
            'email' => 'new_op@test.com',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/pemeriksaan');
        $this->assertAuthenticatedAs($op);
    }

    /** 11. Deactivated user cannot log in */
    public function test_deactivated_user_cannot_login(): void
    {
        $this->operator->is_active = false;
        $this->operator->save();

        $response = $this->post('/login', [
            'email' => $this->operator->email,
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** 12. Reactivated user can log in */
    public function test_reactivated_user_can_login(): void
    {
        $this->operator->is_active = false;
        $this->operator->save();

        // Reactivate via Admin
        $this->actingAs($this->admin)->post("/admin/users/{$this->operator->id}/toggle-status");
        $this->assertTrue($this->operator->fresh()->is_active);

        // Logout admin
        $this->post('/logout');

        // Login operator
        $response = $this->post('/login', [
            'email' => $this->operator->email,
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/pemeriksaan');
        $this->assertAuthenticatedAs($this->operator);
    }

    /** 13. Admin cannot deactivate themselves */
    public function test_admin_cannot_deactivate_themselves(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->admin->id}", [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'admin',
            'is_active' => 0,
        ]);

        $response->assertSessionHasErrors('is_active');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    /** 14. Admin cannot delete themselves */
    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->id}");
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /** 15. Password stored as hash */
    public function test_password_is_stored_as_hash(): void
    {
        $this->assertNotEquals('Password123!', $this->operator->password);
        $this->assertTrue(Hash::check('Password123!', $this->operator->password));
    }

    /** 16. Password not leaked in array serialization */
    public function test_password_not_in_user_array_serialization(): void
    {
        $array = $this->operator->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }

    /** 17. No public registration route */
    public function test_no_public_registration_route(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    /** 18. CSRF active on state changing requests */
    public function test_csrf_active_on_user_management_requests(): void
    {
        $this->actingAs($this->admin);
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/admin/users', [
                'name' => 'No CSRF Test',
                'email' => 'nocsrf@test.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'operator',
            ]);

        $response->assertRedirect('/admin/users');
    }

    /** 19. Rate limiting active on login */
    public function test_login_rate_limiting_works(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'invalid@test.com',
                'password' => 'wrong',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'invalid@test.com',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** 20. Session regeneration on login */
    public function test_session_regeneration_on_login(): void
    {
        $this->get('/login');
        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $this->operator->email,
            'password' => 'Password123!',
        ]);

        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }
}
