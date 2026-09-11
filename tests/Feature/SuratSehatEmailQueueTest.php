<?php

namespace Tests\Feature;

use App\Jobs\SendSuratSehatJob;
use App\Mail\SuratSehatMail;
use App\Models\Pemeriksaan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SuratSehatEmailQueueTest extends TestCase
{
    use DatabaseMigrations;

    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->operator = User::create([
            'name' => 'Operator Email Queue Test',
            'email' => 'operator_mail@klinik.uin.ac.id',
            'password' => Hash::make('PasswordOperator123!'),
            'role' => 'operator',
        ]);
        $this->actingAs($this->operator);
    }

    /**
     * Test 1 - Individual Email: Dispatch async job and return fast response without synchronous SMTP block.
     */
    public function test_individual_email_dispatches_async_job_and_returns_fast_response(): void
    {
        Queue::fake();

        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Student Async 1',
            'email' => 'student_async1@example.com',
            'nomor_surat' => '0172/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Belum dikirim'
        ]);

        $response = $this->post(route('pemeriksaan.sendEmail', $pemeriksaan->id));
        $response->assertStatus(302);

        // Verify status updated to 'Dalam antrean'
        $this->assertEquals('Dalam antrean', $pemeriksaan->fresh()->status_pengiriman);

        // Verify job dispatched to Queue asynchronously
        Queue::assertPushed(SendSuratSehatJob::class, function ($job) use ($pemeriksaan) {
            return $job->pemeriksaan->id === $pemeriksaan->id;
        });

        // Verify NO synchronous email was sent directly by request thread
        Mail::assertNothingSent();
    }

    /**
     * Test 2 - Bulk 20 Data: Dispatches 20 separate individual jobs instead of 1 monolithic job.
     */
    public function test_bulk_email_dispatches_separate_jobs_per_student(): void
    {
        Queue::fake();

        $ids = [];
        for ($i = 1; $i <= 20; $i++) {
            $record = Pemeriksaan::create([
                'nama' => "Student Bulk {$i}",
                'email' => "student_bulk_{$i}@example.com",
                'nomor_surat' => sprintf("%04d/Un.08/PPKES/09/2026", 172 + $i),
                'status_pengiriman' => 'Belum dikirim'
            ]);
            $ids[] = $record->id;
        }

        $response = $this->post(route('pengiriman.bulkSend'), [
            'pemeriksaan_ids' => $ids
        ]);

        $response->assertStatus(302);

        // Verify exactly 20 separate jobs pushed
        Queue::assertPushed(SendSuratSehatJob::class, 20);

        // Verify all 20 records updated status to 'Dalam antrean'
        $queuedCount = Pemeriksaan::whereIn('id', $ids)->where('status_pengiriman', 'Dalam antrean')->count();
        $this->assertEquals(20, $queuedCount);
    }

    /**
     * Test 3 - Job Lifecycle Success: Job handle updates status to 'Mengirim' then 'Terkirim'.
     */
    public function test_job_handle_updates_status_to_mengirim_and_terkirim(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Student Success',
            'email' => 'student_success@example.com',
            'nomor_surat' => '0200/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Dalam antrean'
        ]);

        $job = new SendSuratSehatJob($pemeriksaan);
        $job->handle();

        $pemeriksaan->refresh();
        $this->assertEquals('Terkirim', $pemeriksaan->status_pengiriman);
        $this->assertNotNull($pemeriksaan->waktu_pengiriman);

        Mail::assertSent(SuratSehatMail::class, function ($mail) use ($pemeriksaan) {
            return $mail->hasTo($pemeriksaan->email);
        });
    }

    /**
     * Test 4 - Job Lifecycle Failure: Job failed callback updates status to 'Gagal'.
     */
    public function test_job_failed_updates_status_to_gagal(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Student Failure',
            'email' => 'student_fail@example.com',
            'nomor_surat' => '0201/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Dalam antrean'
        ]);

        $job = new SendSuratSehatJob($pemeriksaan);
        $job->failed(new \Exception('SMTP Connection Timeout'));

        $pemeriksaan->refresh();
        $this->assertEquals('Gagal', $pemeriksaan->status_pengiriman);
    }

    /**
     * Test 5 - Retry Failed Bulk: Dispatches jobs only for records with 'Gagal' status.
     */
    public function test_retry_failed_bulk_dispatches_failed_jobs_only(): void
    {
        Queue::fake();

        // 2 Failed records
        $p1 = Pemeriksaan::create([
            'nama' => 'Failed 1',
            'email' => 'failed1@example.com',
            'nomor_surat' => '0202/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Gagal'
        ]);
        $p2 = Pemeriksaan::create([
            'nama' => 'Failed 2',
            'email' => 'failed2@example.com',
            'nomor_surat' => '0203/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Gagal'
        ]);

        // 1 Sent record (should NOT be requeued by retryFailed)
        $p3 = Pemeriksaan::create([
            'nama' => 'Sent Already',
            'email' => 'sent@example.com',
            'nomor_surat' => '0204/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Terkirim'
        ]);

        $response = $this->post(route('pengiriman.retryFailed'));
        $response->assertStatus(302);

        Queue::assertPushed(SendSuratSehatJob::class, 2);
        $this->assertEquals('Dalam antrean', $p1->fresh()->status_pengiriman);
        $this->assertEquals('Dalam antrean', $p2->fresh()->status_pengiriman);
        $this->assertEquals('Terkirim', $p3->fresh()->status_pengiriman);
    }

    /**
     * Test 6 - Duplicate Protection: Prevents double dispatch if record is already queued or sending.
     */
    public function test_duplicate_dispatch_protection_prevents_double_requeuing(): void
    {
        Queue::fake();

        $pemeriksaan = Pemeriksaan::create([
            'nama' => 'Student Queued',
            'email' => 'queued@example.com',
            'nomor_surat' => '0205/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Dalam antrean'
        ]);

        // Attempting to dispatch again while status is 'Dalam antrean'
        $response = $this->post(route('pemeriksaan.sendEmail', $pemeriksaan->id));
        $response->assertStatus(302);

        // No new job should be pushed
        Queue::assertNothingPushed();
    }
}
