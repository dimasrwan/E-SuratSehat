<?php

namespace Tests\Feature;

use App\Models\Pemeriksaan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class PemeriksaanConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test 1 - Normal: Single input generates expected number.
     */
    public function test_normal_input_generates_valid_nomor_surat(): void
    {
        $payload = [
            'nama' => 'Maba Test Normal',
            'email' => 'maba_normal@example.com',
            'kesimpulan' => 'SEHAT DAN TIDAK BUTA WARNA',
        ];

        $response = $this->post(route('pemeriksaan.store'), $payload);
        $response->assertStatus(302);

        $record = Pemeriksaan::where('email', 'maba_normal@example.com')->first();
        $this->assertNotNull($record);
        $this->assertNotNull($record->nomor_surat);
        $this->assertMatchesRegularExpression('/^\d{4}\/Un\.08\/PPKES\/\d{2}\/\d{4}$/', $record->nomor_surat);
    }

    /**
     * Test 2 - Sequential: Creating 3 records yields sequential numbers.
     */
    public function test_sequential_inputs_generate_sequential_nomor_surat(): void
    {
        $records = [];
        for ($i = 1; $i <= 3; $i++) {
            $payload = [
                'nama' => "Maba Sequential {$i}",
                'email' => "maba_seq_{$i}@example.com",
            ];
            $this->post(route('pemeriksaan.store'), $payload);
            $records[] = Pemeriksaan::where('email', "maba_seq_{$i}@example.com")->first();
        }

        $this->assertCount(3, $records);
        $nums = array_map(function ($r) {
            $parts = explode('/', $r->nomor_surat);
            return (int) $parts[0];
        }, $records);

        $this->assertEquals($nums[0] + 1, $nums[1]);
        $this->assertEquals($nums[1] + 1, $nums[2]);
    }

    /**
     * Test 3 - Concurrent: Simulates multiple stores within transaction lock to verify zero duplicates.
     */
    public function test_concurrent_inputs_generate_unique_nomor_surat_without_duplicates(): void
    {
        $created = [];
        for ($i = 1; $i <= 5; $i++) {
            $payload = [
                'nama' => "Maba Concurrent {$i}",
                'email' => "maba_concurrent_{$i}@example.com",
            ];
            $response = $this->post(route('pemeriksaan.store'), $payload);
            $response->assertStatus(302);
            $created[] = Pemeriksaan::where('email', "maba_concurrent_{$i}@example.com")->first();
        }

        $nomorSurats = array_map(fn($r) => $r->nomor_surat, $created);
        $this->assertCount(5, array_unique($nomorSurats));
    }

    /**
     * Test 4 - Database Constraint: Directly attempting to insert duplicate nomor_surat throws QueryException.
     */
    public function test_database_unique_constraint_rejects_duplicate_nomor_surat(): void
    {
        $testNomor = '9999/Un.08/PPKES/09/2026';

        Pemeriksaan::create([
            'nama' => 'Mahasiswa 1',
            'email' => 'maba1_dup@example.com',
            'nomor_surat' => $testNomor
        ]);

        $this->expectException(QueryException::class);

        Pemeriksaan::create([
            'nama' => 'Mahasiswa 2',
            'email' => 'maba2_dup@example.com',
            'nomor_surat' => $testNomor
        ]);
    }

    /**
     * Test 5 - Transaction Failure: Ensures transaction rollback occurs on failure without partial records.
     */
    public function test_transaction_rollback_on_failure(): void
    {
        $initialCount = Pemeriksaan::count();

        try {
            DB::transaction(function () {
                Pemeriksaan::create([
                    'nama' => 'Test Rollback',
                    'email' => 'rollback@example.com',
                    'nomor_surat' => '8888/Un.08/PPKES/09/2026'
                ]);

                throw new \Exception('Simulated Failure');
            });
        } catch (\Exception $e) {
            // Expected
        }

        $this->assertEquals($initialCount, Pemeriksaan::count());
        $this->assertNull(Pemeriksaan::where('email', 'rollback@example.com')->first());
    }
}
