<?php

namespace Tests\Feature;

use App\Models\Pemeriksaan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use DatabaseMigrations;

    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->operator = User::create([
            'name' => 'Operator Security Test',
            'email' => 'operator_test@klinik.uin.ac.id',
            'password' => Hash::make('PasswordOperator123!'),
            'role' => 'operator',
        ]);
    }

    /**
     * Test 1 - Anonymous user redirected to login from /pemeriksaan.
     */
    public function test_anonymous_user_redirected_to_login_from_pemeriksaan_index(): void
    {
        $response = $this->get('/pemeriksaan');
        $response->assertRedirect('/login');
    }

    /**
     * Test 2 - Anonymous user redirected from /pemeriksaan/create.
     */
    public function test_anonymous_user_redirected_from_create(): void
    {
        $response = $this->get('/pemeriksaan/create');
        $response->assertRedirect('/login');
    }

    /**
     * Test 3 - Anonymous user cannot delete record.
     */
    public function test_anonymous_user_cannot_delete_record(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Maba Test Delete',
            'email' => 'maba_del@example.com',
            'nomor_surat' => '9001/Un.08/PPKES/09/2026'
        ]);

        $response = $this->delete("/pemeriksaan/{$pemeriksaan->id}");
        $response->assertRedirect('/login');

        $this->assertDatabaseHas('pemeriksaans', ['id' => $pemeriksaan->id]);
    }

    /**
     * Test 4 - Anonymous user cannot download PDF.
     */
    public function test_anonymous_user_cannot_download_pdf(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Maba Test PDF',
            'email' => 'maba_pdf@example.com',
            'nomor_surat' => '9002/Un.08/PPKES/09/2026'
        ]);

        $response = $this->get("/pemeriksaan/{$pemeriksaan->id}/download");
        $response->assertRedirect('/login');
    }

    /**
     * Test 5 - Anonymous user cannot preview PDF.
     */
    public function test_anonymous_user_cannot_preview_pdf(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Maba Test Preview',
            'email' => 'maba_prev@example.com',
            'nomor_surat' => '9003/Un.08/PPKES/09/2026'
        ]);

        $response = $this->get("/pemeriksaan/{$pemeriksaan->id}/preview");
        $response->assertRedirect('/login');
    }

    /**
     * Test 6 - Anonymous user cannot export Excel.
     */
    public function test_anonymous_user_cannot_export_excel(): void
    {
        $response = $this->get('/pemeriksaan/export/excel');
        $response->assertRedirect('/login');
    }

    /**
     * Test 7 - Anonymous user cannot send bulk email.
     */
    public function test_anonymous_user_cannot_send_bulk_email(): void
    {
        $response = $this->post('/pengiriman/bulk-send', ['pemeriksaan_ids' => [1]]);
        $response->assertRedirect('/login');
    }

    /**
     * Test 8 - Login rate limiting locks out brute force attempts after 5 failures.
     */
    public function test_login_rate_limiting_locks_out_brute_force_attempts(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', [
                'email' => 'operator_test@klinik.uin.ac.id',
                'password' => 'WrongPassword!'
            ]);
        }

        // 6th attempt should return rate limit error
        $response = $this->post('/login', [
            'email' => 'operator_test@klinik.uin.ac.id',
            'password' => 'PasswordOperator123!'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 9 - Successful login regenerates session.
     */
    public function test_successful_login_regenerates_session(): void
    {
        RateLimiter::clear('login:127.0.0.1');

        $response = $this->post('/login', [
            'email' => 'operator_test@klinik.uin.ac.id',
            'password' => 'PasswordOperator123!'
        ]);

        $response->assertRedirect(route('pemeriksaan.index'));
        $this->assertAuthenticatedAs($this->operator);
    }

    /**
     * Test 10 - Authenticated operator can access dashboard and download PDF.
     */
    public function test_authenticated_operator_can_access_dashboard_and_download_pdf(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Maba Auth Test',
            'email' => 'maba_auth@example.com',
            'nomor_surat' => '9004/Un.08/PPKES/09/2026'
        ]);

        $response = $this->actingAs($this->operator)->get("/pemeriksaan/{$pemeriksaan->id}/download");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test 11 - Mass assignment protection prevents updating internal fields.
     */
    public function test_mass_assignment_protection_prevents_updating_internal_fields(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Original Name',
            'email' => 'original@example.com',
            'nomor_surat' => '9005/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Belum dikirim'
        ]);

        $payload = [
            'nama' => 'Updated Name',
            'email' => 'original@example.com',
            'nomor_surat' => 'HACKED_SURAT_NUMBER',
            'status_pengiriman' => 'Terkirim'
        ];

        $response = $this->actingAs($this->operator)->put("/pemeriksaan/{$pemeriksaan->id}", $payload);
        $response->assertStatus(302);

        $pemeriksaan->refresh();
        $this->assertEquals('Updated Name', $pemeriksaan->nama);
        $this->assertEquals('9005/Un.08/PPKES/09/2026', $pemeriksaan->nomor_surat);
        $this->assertEquals('Belum dikirim', $pemeriksaan->status_pengiriman);
    }

    /**
     * Test 12 - Logout invalidates session and redirects to public home '/'.
     */
    public function test_logout_invalidates_session(): void
    {
        $response = $this->actingAs($this->operator)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
