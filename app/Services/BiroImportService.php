<?php

namespace App\Services;

use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\MabaData;
use App\Models\TahunMaba;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BiroImportService
{
    /**
     * Map header aliases for normalization.
     */
    protected array $headerAliases = [
        'nama' => ['nama', 'nama maba', 'nama mahasiswa', 'nama lengkap'],
        'program_studi' => ['program studi', 'prodi', 'jurusan'],
        'tanggal_jadwal' => ['tanggal', 'tanggal jadwal', 'tgl', 'tgl jadwal'],
        'sesi_jadwal' => ['sesi', 'sesi jadwal'],
        'waktu_jadwal' => ['waktu', 'waktu jadwal', 'jam'],
    ];

    /**
     * Store temporary file, parse rows, classify duplicates, and create ImportBatch + Rows preview.
     */
    public function createBatchAndParsePreview(UploadedFile $file, int $tahunMabaId, User $user): ImportBatch
    {
        $tahunMaba = TahunMaba::findOrFail($tahunMabaId);

        // Store file in private storage
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = $file->store('imports');
        $fullPath = Storage::path($storedFilename);
        // Determine file type and extract raw rows array
        $extension = strtolower($file->getClientOriginalExtension());
        $rawRows = [];

        // Pre-fetch Master ProgramStudi map for fast matching & validation
        // Map normalized name -> ProgramStudi model (with fakultas)
        $masterProdis = \App\Models\ProgramStudi::with('fakultas')->get();
        $masterProdiMap = [];
        foreach ($masterProdis as $p) {
            $norm = $this->normalizeString($p->nama);
            $normClean = preg_replace('/^(s1|d3|s2|s3)\s+/', '', $norm);
            $normClean = preg_replace('/\s*\(.*?\)\s*/', ' ', $normClean);
            $normClean = str_replace(['`', "'", '’', '-'], ['', '', '', ' '], $normClean);
            $normClean = preg_replace('/\s+dan\s+hukum$/i', '', $normClean);
            $normClean = str_replace(' dan ', ' ', $normClean);
            $normClean = preg_replace('/\s+/', ' ', trim($normClean));

            $masterProdiMap[$norm] = $p;
            $masterProdiMap[$normClean] = $p;
        }

        if ($extension === 'pdf') {
            $pdfParser = new PdfScheduleParserService();
            $parsedPdfRows = $pdfParser->parsePdf($fullPath);

            if (empty($parsedPdfRows)) {
                throw new \Exception('Tidak ada data peserta yang berhasil dibaca dari file PDF ini.');
            }

            foreach ($parsedPdfRows as $idx => $pr) {
                $rawRows[] = [
                    'nama' => $pr['nama_biro'],
                    'program_studi' => $pr['program_studi_biro'],
                    'tanggal_jadwal' => $pr['tanggal_jadwal'] ?? $pr['raw_tanggal'],
                    'sesi_jadwal' => $pr['sesi_jadwal'],
                    'waktu_jadwal' => $pr['waktu_jadwal'],
                    'fakultas' => $pr['fakultas_biro'],
                ];
            }
        } else {
            // Load Spreadsheet (XLSX, XLS, CSV)
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $spreadsheetRows = $worksheet->toArray(null, true, true, true);

            if (empty($spreadsheetRows)) {
                throw new \Exception('File kosong atau tidak berisi data.');
            }

            // Parse Header (Row 1)
            $headerRow = array_shift($spreadsheetRows);
            $headerMap = $this->parseHeaders($headerRow);

            if (!isset($headerMap['nama'])) {
                throw new \Exception('Kolom "Nama Peserta" (atau "Nama" / "Nama Maba") belum ditemukan pada file.');
            }

            if (!isset($headerMap['program_studi'])) {
                throw new \Exception('Kolom "Program Studi" (atau "Prodi" / "Jurusan") belum ditemukan pada file.');
            }

            foreach ($spreadsheetRows as $sRow) {
                $rawNama = isset($headerMap['nama']) && isset($sRow[$headerMap['nama']]) ? $sRow[$headerMap['nama']] : null;
                $rawProdi = isset($headerMap['program_studi']) && isset($sRow[$headerMap['program_studi']]) ? $sRow[$headerMap['program_studi']] : null;
                $rawTanggal = isset($headerMap['tanggal_jadwal']) && isset($sRow[$headerMap['tanggal_jadwal']]) ? $sRow[$headerMap['tanggal_jadwal']] : null;
                $rawSesi = isset($headerMap['sesi_jadwal']) && isset($sRow[$headerMap['sesi_jadwal']]) ? $sRow[$headerMap['sesi_jadwal']] : null;
                $rawWaktu = isset($headerMap['waktu_jadwal']) && isset($sRow[$headerMap['waktu_jadwal']]) ? $sRow[$headerMap['waktu_jadwal']] : null;

                if ($this->isEmptyRow($rawNama, $rawProdi, $rawTanggal, $rawSesi, $rawWaktu)) {
                    continue;
                }

                $rawRows[] = [
                    'nama' => $rawNama,
                    'program_studi' => $rawProdi,
                    'tanggal_jadwal' => $rawTanggal,
                    'sesi_jadwal' => $rawSesi,
                    'waktu_jadwal' => $rawWaktu,
                    'fakultas' => null,
                ];
            }
        }

        // Pre-fetch existing MabaData for strict year isolation
        $existingMabas = MabaData::where('tahun_maba_id', $tahunMaba->id)->get();

        // Build lookup maps for fast matching
        $exactMap = [];
        $possibleMap = [];

        foreach ($existingMabas as $maba) {
            $nClean = $this->normalizeString($maba->nama_biro);
            $pClean = $this->normalizeString($maba->program_studi_biro);
            $tStr = $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('Y-m-d') : '';
            $sStr = $this->normalizeString($maba->sesi_jadwal);
            $wStr = $this->normalizeString($maba->waktu_jadwal);

            $exactKey = "{$nClean}|{$pClean}|{$tStr}|{$sStr}|{$wStr}";
            $possibleKey = "{$nClean}|{$pClean}";

            $exactMap[$exactKey] = $maba;
            if (!isset($possibleMap[$possibleKey])) {
                $possibleMap[$possibleKey] = $maba;
            }
        }

        // Create Batch Record
        $batch = ImportBatch::create([
            'tahun_maba_id' => $tahunMaba->id,
            'user_id' => $user->id,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'status' => 'PREVIEW',
        ]);

        $totalRows = 0;
        $newRows = 0;
        $possibleDuplicateRows = 0;
        $exactDuplicateRows = 0;
        $errorRows = 0;

        $batchRowsToInsert = [];

        $rowNumber = 1;
        foreach ($rawRows as $item) {
            $rowNumber++;
            $totalRows++;

            $rawNama = $item['nama'];
            $rawProdi = $item['program_studi'];
            $rawTanggal = $item['tanggal_jadwal'];
            $rawSesi = $item['sesi_jadwal'];
            $rawWaktu = $item['waktu_jadwal'];

            $namaBiro = $this->normalizeStringDisplay($rawNama);
            $prodiBiro = $this->normalizeStringDisplay($rawProdi);
            $tanggalJadwal = $this->parseDateValue($rawTanggal);
            $sesiJadwal = $this->normalizeStringDisplay($rawSesi);
            $waktuJadwal = $this->normalizeStringDisplay($rawWaktu);

            // Validation against Master ProgramStudi
            $errors = [];
            if (empty($namaBiro)) {
                $errors[] = 'Nama Maba wajib diisi.';
            }

            if (empty($prodiBiro)) {
                $errors[] = 'Program Studi wajib diisi.';
            } else {
                // Match against master ProgramStudi (case insensitive & normalized)
                $normProdi = $this->normalizeString($prodiBiro);
                $normProdiClean = preg_replace('/^(s1|d3|s2|s3)\s+/', '', $normProdi);
                $normProdiClean = preg_replace('/\s*\(.*?\)\s*/', ' ', $normProdiClean);
                $normProdiClean = str_replace(['`', "'", '’', '-'], ['', '', '', ' '], $normProdiClean);
                $normProdiClean = preg_replace('/\s+dan\s+hukum$/i', '', $normProdiClean);
                $normProdiClean = str_replace(' dan ', ' ', $normProdiClean);
                $normProdiClean = preg_replace('/\s+/', ' ', trim($normProdiClean));

                $matchedProdi = $masterProdiMap[$normProdi] ?? ($masterProdiMap[$normProdiClean] ?? null);

                if (!empty($masterProdiMap) && !$matchedProdi) {
                    $errors[] = "Program Studi '{$prodiBiro}' tidak ditemukan di Master Program Studi.";
                }
            }

            if (!empty($rawTanggal) && $tanggalJadwal === false) {
                $errors[] = "Format tanggal '{$rawTanggal}' tidak valid.";
                $tanggalJadwal = null;
            }

            if (!empty($errors)) {
                $errorRows++;
                $batchRowsToInsert[] = [
                    'import_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'classification' => 'ERROR',
                    'nama_biro' => $namaBiro,
                    'program_studi_biro' => $prodiBiro,
                    'tanggal_jadwal' => $tanggalJadwal,
                    'sesi_jadwal' => $sesiJadwal,
                    'waktu_jadwal' => $waktuJadwal,
                    'existing_maba_data_id' => null,
                    'error_messages' => implode(' ', $errors),
                    'selected_action' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                continue;
            }

            // Duplicate Classification
            $nClean = $this->normalizeString($namaBiro);
            $pClean = $this->normalizeString($prodiBiro);
            $tStr = $tanggalJadwal ?? '';
            $sStr = $this->normalizeString($sesiJadwal);
            $wStr = $this->normalizeString($waktuJadwal);

            $exactKey = "{$nClean}|{$pClean}|{$tStr}|{$sStr}|{$wStr}";
            $possibleKey = "{$nClean}|{$pClean}";

            if (isset($exactMap[$exactKey])) {
                $exactDuplicateRows++;
                $batchRowsToInsert[] = [
                    'import_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'classification' => 'EXACT_DUPLICATE',
                    'nama_biro' => $namaBiro,
                    'program_studi_biro' => $prodiBiro,
                    'tanggal_jadwal' => $tanggalJadwal,
                    'sesi_jadwal' => $sesiJadwal,
                    'waktu_jadwal' => $waktuJadwal,
                    'existing_maba_data_id' => $exactMap[$exactKey]->id,
                    'error_messages' => null,
                    'selected_action' => 'SKIP',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif (isset($possibleMap[$possibleKey])) {
                $possibleDuplicateRows++;
                $batchRowsToInsert[] = [
                    'import_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'classification' => 'POSSIBLE_DUPLICATE',
                    'nama_biro' => $namaBiro,
                    'program_studi_biro' => $prodiBiro,
                    'tanggal_jadwal' => $tanggalJadwal,
                    'sesi_jadwal' => $sesiJadwal,
                    'waktu_jadwal' => $waktuJadwal,
                    'existing_maba_data_id' => $possibleMap[$possibleKey]->id,
                    'error_messages' => null,
                    'selected_action' => 'UPDATE_JADWAL', // Default action for possible duplicate
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $newRows++;
                $batchRowsToInsert[] = [
                    'import_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'classification' => 'NEW',
                    'nama_biro' => $namaBiro,
                    'program_studi_biro' => $prodiBiro,
                    'tanggal_jadwal' => $tanggalJadwal,
                    'sesi_jadwal' => $sesiJadwal,
                    'waktu_jadwal' => $waktuJadwal,
                    'existing_maba_data_id' => null,
                    'error_messages' => null,
                    'selected_action' => 'CREATE_NEW',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert Batch Rows in Chunks (500 per chunk)
        foreach (array_chunk($batchRowsToInsert, 500) as $chunk) {
            ImportBatchRow::insert($chunk);
        }

        // Update Batch Counters
        $batch->update([
            'total_rows' => $totalRows,
            'new_rows' => $newRows,
            'possible_duplicate_rows' => $possibleDuplicateRows,
            'exact_duplicate_rows' => $exactDuplicateRows,
            'error_rows' => $errorRows,
        ]);

        return $batch->fresh();
    }

    /**
     * Execute Batch import under DB transaction after Admin confirmation.
     */
    public function confirmBatch(ImportBatch $batch, array $actions = []): ImportBatch
    {
        if ($batch->status !== 'PREVIEW') {
            throw new \Exception('Batch ini tidak dalam status PREVIEW dan tidak dapat dikonfirmasi.');
        }

        $batch->update(['status' => 'PROCESSING', 'started_at' => now()]);

        DB::transaction(function () use ($batch, $actions) {
            $insertedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            // Update user selected actions for possible duplicates if submitted
            if (!empty($actions)) {
                foreach ($actions as $rowId => $action) {
                    if (in_array($action, ['CREATE_NEW', 'UPDATE_JADWAL', 'SKIP'])) {
                        ImportBatchRow::where('id', $rowId)
                            ->where('import_batch_id', $batch->id)
                            ->update(['selected_action' => $action]);
                    }
                }
            }

            $rows = ImportBatchRow::where('import_batch_id', $batch->id)->get();

            foreach ($rows as $row) {
                if ($row->classification === 'ERROR') {
                    $skippedCount++;
                    continue;
                }

                if ($row->classification === 'EXACT_DUPLICATE') {
                    $skippedCount++;
                    continue;
                }

                $action = $row->selected_action;

                if ($row->classification === 'POSSIBLE_DUPLICATE') {
                    if ($action === 'UPDATE_JADWAL' && $row->existing_maba_data_id) {
                        $maba = MabaData::find($row->existing_maba_data_id);
                        if ($maba) {
                            // BIODATA PROTECTION RULE:
                            // ONLY update Biro source schedule fields!
                            // NEVER overwrite personal biodata or status or verified_at/verified_by!
                            $maba->update([
                                'tanggal_jadwal' => $row->tanggal_jadwal,
                                'sesi_jadwal' => $row->sesi_jadwal,
                                'waktu_jadwal' => $row->waktu_jadwal,
                            ]);
                            $updatedCount++;
                            continue;
                        }
                    } elseif ($action === 'SKIP') {
                        $skippedCount++;
                        continue;
                    }
                }

                // If action is CREATE_NEW (or default for NEW)
                // CONCURRENCY RE-CHECK: verify no exact match was created concurrently
                $nClean = $this->normalizeString($row->nama_biro);
                $pClean = $this->normalizeString($row->program_studi_biro);
                $tStr = $row->tanggal_jadwal ? $row->tanggal_jadwal->format('Y-m-d') : null;

                $existingQuery = MabaData::where('tahun_maba_id', $batch->tahun_maba_id)
                    ->whereRaw('LOWER(TRIM(nama_biro)) = ?', [$nClean])
                    ->whereRaw('LOWER(TRIM(program_studi_biro)) = ?', [$pClean]);

                if ($tStr) {
                    $existingQuery->where('tanggal_jadwal', $tStr);
                }

                if ($row->sesi_jadwal) {
                    $existingQuery->whereRaw('LOWER(TRIM(sesi_jadwal)) = ?', [$this->normalizeString($row->sesi_jadwal)]);
                }

                $recheckMatch = $existingQuery->first();

                if ($recheckMatch && $row->classification === 'NEW') {
                    // Exact match found on concurrency re-check => skip
                    $skippedCount++;
                    continue;
                }

                MabaData::create([
                    'tahun_maba_id' => $batch->tahun_maba_id,
                    'nama_biro' => $row->nama_biro,
                    'program_studi_biro' => $row->program_studi_biro,
                    'tanggal_jadwal' => $row->tanggal_jadwal,
                    'sesi_jadwal' => $row->sesi_jadwal,
                    'waktu_jadwal' => $row->waktu_jadwal,
                    'status_biodata' => 'BELUM_MENGISI',
                ]);
                $insertedCount++;
            }

            $batch->update([
                'status' => 'COMPLETED',
                'inserted_rows' => $insertedCount,
                'updated_rows' => $updatedCount,
                'skipped_rows' => $skippedCount,
                'completed_at' => now(),
            ]);
        });

        // Clean up stored temporary file
        $this->cleanupBatchFile($batch);

        return $batch->fresh();
    }

    /**
     * Cancel batch import in PREVIEW state.
     */
    public function cancelBatch(ImportBatch $batch): ImportBatch
    {
        if ($batch->status === 'PREVIEW') {
            $batch->update(['status' => 'CANCELLED']);
            $this->cleanupBatchFile($batch);
        }
        return $batch->fresh();
    }

    /**
     * Helper to delete temporary import file.
     */
    protected function cleanupBatchFile(ImportBatch $batch): void
    {
        if ($batch->stored_filename && Storage::exists($batch->stored_filename)) {
            Storage::delete($batch->stored_filename);
        }
    }

    /**
     * Match row 1 header names against known aliases.
     */
    protected function parseHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colKey => $colValue) {
            if ($colValue === null) continue;

            $normalizedCol = $this->normalizeString((string)$colValue);

            foreach ($this->headerAliases as $targetKey => $aliases) {
                if (in_array($normalizedCol, $aliases)) {
                    $map[$targetKey] = $colKey;
                    break;
                }
            }
        }
        return $map;
    }

    /**
     * Normalize string for matching (lowercase, trim, collapse spaces).
     */
    protected function normalizeString(?string $str): string
    {
        if ($str === null) return '';
        $clean = preg_replace('/\s+/', ' ', trim($str));
        return mb_strtolower($clean, 'UTF-8');
    }

    /**
     * Normalize string for display/DB storage.
     */
    protected function normalizeStringDisplay(?string $str): ?string
    {
        if ($str === null) return null;
        $clean = preg_replace('/\s+/', ' ', trim((string)$str));
        return $clean !== '' ? $clean : null;
    }

    /**
     * Check if a row is completely empty.
     */
    protected function isEmptyRow(...$values): bool
    {
        foreach ($values as $val) {
            if ($val !== null && trim((string)$val) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Parse date input string or Excel numeric timestamp into YYYY-MM-DD format string.
     */
    protected function parseDateValue($rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        // If numeric value (Excel timestamp)
        if (is_numeric($rawDate)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$rawDate);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                return false;
            }
        }

        $str = trim((string)$rawDate);

        // Try YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $str)->format('Y-m-d');
            } catch (\Throwable $e) {
                return false;
            }
        }

        // Try DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $matches)) {
            try {
                return Carbon::createFromFormat('d/m/Y', "{$matches[1]}/{$matches[2]}/{$matches[3]}")->format('Y-m-d');
            } catch (\Throwable $e) {
                return false;
            }
        }

        // Fallback Carbon parse
        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
