<?php

namespace App\Services;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use Carbon\Carbon;
use Smalot\PdfParser\Parser as SmalotParser;

class PdfScheduleParserService
{
    /**
     * Parse PDF schedule file or text content into structured rows.
     *
     * Returned array items structure:
     * [
     *   'nama_biro' => string,
     *   'program_studi_biro' => string,
     *   'fakultas_biro' => string|null,
     *   'tanggal_jadwal' => 'YYYY-MM-DD'|null|false,
     *   'sesi_jadwal' => string|null,
     *   'waktu_jadwal' => string|null,
     *   'error' => string|null,
     * ]
     */
    public function parsePdf(string $filePath): array
    {
        $text = $this->extractTextFromPdf($filePath);
        return $this->parseTextContent($text);
    }

    /**
     * Extract raw text from PDF using pdftotext CLI (fallback to Smalot PdfParser).
     */
    public function extractTextFromPdf(string $filePath): string
    {
        // Try pdftotext first (fastest and cleanest layout preserving)
        $output = [];
        $returnVar = -1;
        @exec("pdftotext -layout " . escapeshellarg($filePath) . " -", $output, $returnVar);

        if ($returnVar === 0 && !empty($output)) {
            return implode("\n", $output);
        }

        // Fallback to Smalot PdfParser
        if (class_exists(SmalotParser::class)) {
            $parser = new SmalotParser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        }

        throw new \Exception('Sistem memerlukan library PDF Parser (smalot/pdfparser) atau utility pdftotext.');
    }

    /**
     * Parse extracted text content line by line using stateful context tracking.
     */
    public function parseTextContent(string $textContent): array
    {
        $lines = explode("\n", $textContent);

        $results = [];
        $currentFakultas = null;
        $currentProdi = null;

        foreach ($lines as $line) {
            $cleanLine = trim($line);

            if ($cleanLine === '') {
                continue;
            }

            // 1. Ignore headers, footers, page numbers
            if ($this->isHeaderOrFooter($cleanLine)) {
                continue;
            }

            // 2. Check if line declares a FAKULTAS
            if (preg_match('/^(?:\d+\.\s*)?FAKULTAS\s+(.+)$/i', $cleanLine, $fMatches)) {
                $fRemainder = trim($fMatches[1]);
                $matchedFakultas = null;
                $studentPartOnFLine = null;

                $fakultasNames = [
                    'TARBIYAH DAN KEGURUAN',
                    'SYARIAH DAN HUKUM',
                    'USHULUDDIN DAN FILSAFAT',
                    'USHULUDIN DAN FILSAFAT',
                    'DAKWAH DAN KOMUNIKASI',
                    'ADAB DAN HUMANIORA',
                    'EKONOMI DAN BISNIS ISLAM',
                    'SAINS DAN TEKNOLOGI',
                    'ILMU SOSIAL DAN ILMU POLITIK',
                    'ILMU SOSIAL DAN ILMU PEMERINTAHAN',
                    'PSIKOLOGI',
                    'KEDOKTERAN',
                ];

                foreach ($fakultasNames as $fn) {
                    if (preg_match('/^' . preg_quote($fn, '/') . '(?:\s{2,}(.+))?$/i', $fRemainder, $fnMatches)) {
                        $matchedFakultas = 'FAKULTAS ' . $fn;
                        if (!empty($fnMatches[1])) {
                            $studentPartOnFLine = trim($fnMatches[1]);
                        }
                        break;
                    }
                }

                if ($matchedFakultas) {
                    $currentFakultas = $matchedFakultas;
                } else {
                    // Fallback split by 2 or more spaces
                    $parts = preg_split('/\s{2,}/', $fRemainder);
                    $currentFakultas = 'FAKULTAS ' . trim($parts[0]);
                    if (count($parts) > 1) {
                        $studentPartOnFLine = trim(implode(' ', array_slice($parts, 1)));
                    }
                }

                if ($studentPartOnFLine) {
                    $parsedRow = $this->parseStudentLine($studentPartOnFLine, $currentFakultas, $currentProdi);
                    if ($parsedRow) {
                        $results[] = [
                            'nama_biro' => $parsedRow['nama_biro'],
                            'program_studi_biro' => $parsedRow['program_studi_biro'] ?: $currentProdi,
                            'fakultas_biro' => $parsedRow['fakultas_biro'] ?: $currentFakultas,
                            'tanggal_jadwal' => $parsedRow['tanggal_jadwal'],
                            'sesi_jadwal' => $parsedRow['sesi_jadwal'],
                            'waktu_jadwal' => $parsedRow['waktu_jadwal'],
                            'raw_tanggal' => $parsedRow['raw_tanggal'] ?? null,
                        ];
                    }
                }
                continue;
            }

            // 3. Check if line declares a PRODI (e.g. "Prodi. Bimbingan Konseling" or "Prodi Bimbingan Konseling")
            if (preg_match('/^Prodi\.?\s+(.+)$/i', $cleanLine, $pMatches)) {
                $pRemainder = trim($pMatches[1]);
                $studentPartOnPLine = null;

                // Check if line contains double spaces separating prodi name from student data
                $parts = preg_split('/\s{2,}/', $pRemainder);
                $currentProdi = trim($parts[0]);

                if (count($parts) > 1) {
                    $studentPartOnPLine = trim(implode('  ', array_slice($parts, 1)));
                }

                if ($studentPartOnPLine) {
                    $parsedRow = $this->parseStudentLine($studentPartOnPLine, $currentFakultas, $currentProdi);
                    if ($parsedRow) {
                        $results[] = [
                            'nama_biro' => $parsedRow['nama_biro'],
                            'program_studi_biro' => $parsedRow['program_studi_biro'] ?: $currentProdi,
                            'fakultas_biro' => $parsedRow['fakultas_biro'] ?: $currentFakultas,
                            'tanggal_jadwal' => $parsedRow['tanggal_jadwal'],
                            'sesi_jadwal' => $parsedRow['sesi_jadwal'],
                            'waktu_jadwal' => $parsedRow['waktu_jadwal'],
                            'raw_tanggal' => $parsedRow['raw_tanggal'] ?? null,
                        ];
                    }
                }
                continue;
            }

            // 4. Try parsing student row
            // Patterns supported:
            // Single line: 1 | S1 Bimbingan Konseling | AFIFA JAHRA | Sabtu, 15 Agustus 2026 | Sesi 1 | 08.00 s/d 12.30
            // Tabular/multiline PDF row pattern matching:
            $parsedRow = $this->parseStudentLine($cleanLine, $currentFakultas, $currentProdi);

            if ($parsedRow) {
                // If line contains specific prodi inside row, update currentProdi context
                if (!empty($parsedRow['extracted_prodi'])) {
                    $currentProdi = $parsedRow['extracted_prodi'];
                }

                $results[] = [
                    'nama_biro' => $parsedRow['nama_biro'],
                    'program_studi_biro' => $parsedRow['program_studi_biro'] ?: $currentProdi,
                    'fakultas_biro' => $parsedRow['fakultas_biro'] ?: $currentFakultas,
                    'tanggal_jadwal' => $parsedRow['tanggal_jadwal'],
                    'sesi_jadwal' => $parsedRow['sesi_jadwal'],
                    'waktu_jadwal' => $parsedRow['waktu_jadwal'],
                    'raw_tanggal' => $parsedRow['raw_tanggal'] ?? null,
                ];
            }
        }

        return $results;
    }

    /**
     * Filter out header titles, column headers, page numbers.
     */
    protected function isHeaderOrFooter(string $line): bool
    {
        $lower = mb_strtolower($line, 'UTF-8');

        if (str_contains($lower, 'daftar jadwal pemeriksaan kesehatan') ||
            str_contains($lower, 'mahasiswa baru tahun ajaran') ||
            str_contains($lower, 'klinik uin ar-raniry') ||
            str_contains($lower, 'nama peserta hari / tanggal sesi waktu') ||
            str_contains($lower, 'nama peserta') && str_contains($lower, 'sesi') ||
            preg_match('/^halaman\s+\d+(\s+dari\s+\d+)?$/i', $line) ||
            preg_match('/^\d+\s*\/\s*\d+$/', $line) ||
            preg_match('/^page\s+\d+/i', $line)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Parse student data row.
     */
    protected function parseStudentLine(string $line, ?string $currentFakultas, ?string $currentProdi): ?array
    {
        // If line contains pipe '|', use pipe splitting
        if (str_contains($line, '|')) {
            $parts = array_map('trim', explode('|', $line));
            // Minimum 4 parts required (e.g. index|prodi|nama|date|sesi|waktu or index|nama|date|sesi|waktu)
            if (count($parts) >= 4) {
                $num = $parts[0];
                $part1 = $parts[1] ?? '';
                $part2 = $parts[2] ?? '';
                $part3 = $parts[3] ?? '';
                $part4 = $parts[4] ?? '';
                $part5 = $parts[5] ?? '';

                // Case: 1 | S1 Bimbingan Konseling | AFIFA JAHRA | Sabtu, 15 Agustus 2026 | Sesi 1 | 08.00 s/d 12.30
                if (count($parts) >= 6) {
                    $extractedProdi = preg_replace('/^(S1|D3|S2|S3)\s+/i', '', $part1);
                    $nama = $part2;
                    $rawDate = $part3;
                    $sesi = $part4;
                    $waktu = $part5;
                } else {
                    // Case: 1 | AFIFA JAHRA | Sabtu, 15 Agustus 2026 | Sesi 1 | 08.00 s/d 12.30
                    $extractedProdi = null;
                    $nama = $part1;
                    $rawDate = $part2;
                    $sesi = $part3;
                    $waktu = $part4;
                }

                $parsedDate = $this->parseIndonesianDate($rawDate);
                if ($parsedDate) {
                    return [
                        'nama_biro' => $nama,
                        'program_studi_biro' => $extractedProdi ?: $currentProdi,
                        'extracted_prodi' => $extractedProdi,
                        'fakultas_biro' => $currentFakultas,
                        'tanggal_jadwal' => $parsedDate,
                        'sesi_jadwal' => $sesi,
                        'waktu_jadwal' => $waktu,
                        'raw_tanggal' => $rawDate,
                    ];
                }
            }
        }

        // Pattern 2: Space separated line matching
        $normalLine = preg_replace('/\s+/', ' ', trim(str_replace('|', ' ', $line)));
        $dateRegex = '(?:(?:Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu),\s*)?(\d{1,2}\s+(?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})';
        $sesiRegex = '(Sesi\s+\d+)';
        $waktuRegex = '(\d{2}[\.:]\d{2}\s*(?:s\/d|-|sampai)\s*\d{2}[\.:]\d{2})';

        // Pattern 2A: Context-based match using $currentProdi if available
        if ($currentProdi) {
            $escapedProdi = preg_quote($currentProdi, '/');
            $contextPattern = '/^(\d+)\s+(?:(?:S1|D3|S2|S3)\s+)?(?:' . $escapedProdi . '\s+)?(.+?)\s+' . $dateRegex . '\s+' . $sesiRegex . '\s+' . $waktuRegex . '$/i';
            if (preg_match($contextPattern, $normalLine, $m)) {
                $num = $m[1];
                $nama = trim($m[2]);
                $rawDate = trim($m[3]);
                $sesi = trim($m[4]);
                $waktu = trim($m[5]);

                return [
                    'nama_biro' => $nama,
                    'program_studi_biro' => $currentProdi,
                    'extracted_prodi' => null,
                    'fakultas_biro' => $currentFakultas,
                    'tanggal_jadwal' => $this->parseIndonesianDate($rawDate),
                    'sesi_jadwal' => $sesi,
                    'waktu_jadwal' => $waktu,
                    'raw_tanggal' => $rawDate,
                ];
            }
        }

        $fullPattern = '/^(\d+)\s+(?:((?:S1|D3|S2|S3)\s+[^0-9]+?)\s+)?(.+?)\s+' . $dateRegex . '\s+' . $sesiRegex . '\s+' . $waktuRegex . '$/i';

        if (preg_match($fullPattern, $normalLine, $matches)) {
            $num = $matches[1];
            $rawExtractedProdi = !empty($matches[2]) ? trim($matches[2]) : null;
            $extractedProdi = $rawExtractedProdi ? preg_replace('/^(S1|D3|S2|S3)\s+/i', '', $rawExtractedProdi) : null;
            $nama = trim($matches[3]);
            $rawDate = trim($matches[4]);
            $sesi = trim($matches[5]);
            $waktu = trim($matches[6]);

            $parsedDate = $this->parseIndonesianDate($rawDate);

            return [
                'nama_biro' => $nama,
                'program_studi_biro' => $extractedProdi ?: $currentProdi,
                'extracted_prodi' => $extractedProdi,
                'fakultas_biro' => $currentFakultas,
                'tanggal_jadwal' => $parsedDate,
                'sesi_jadwal' => $sesi,
                'waktu_jadwal' => $waktu,
                'raw_tanggal' => $rawDate,
            ];
        }

        // Pattern 2: Multi-line / Loose format parsing
        // If line contains date + sesi + waktu
        if (preg_match('/' . $dateRegex . '.*?' . $sesiRegex . '.*?' . $waktuRegex . '/i', $normalLine, $m)) {
            $rawDate = trim($m[1]);
            $sesi = trim($m[2]);
            $waktu = trim($m[3]);

            // Extract name before date
            $beforeDate = trim(substr($normalLine, 0, strpos($normalLine, $m[0])));
            // Remove leading index number if present
            $nama = preg_replace('/^\d+\s+/', '', $beforeDate);
            $nama = preg_replace('/^S\d\s+.*?\s+/i', '', $nama); // remove S1 ... prefix

            if (!empty($nama)) {
                return [
                    'nama_biro' => $nama,
                    'program_studi_biro' => $currentProdi,
                    'extracted_prodi' => null,
                    'fakultas_biro' => $currentFakultas,
                    'tanggal_jadwal' => $this->parseIndonesianDate($rawDate),
                    'sesi_jadwal' => $sesi,
                    'waktu_jadwal' => $waktu,
                    'raw_tanggal' => $rawDate,
                ];
            }
        }

        return null;
    }

    /**
     * Parse Indonesian Date format string (e.g. "15 Agustus 2026") into "YYYY-MM-DD".
     */
    public function parseIndonesianDate(string $rawDate): ?string
    {
        $bulanMap = [
            'januari' => '01',
            'februari' => '02',
            'maret' => '03',
            'april' => '04',
            'mei' => '05',
            'juni' => '06',
            'juli' => '07',
            'agustus' => '08',
            'september' => '09',
            'oktober' => '10',
            'november' => '11',
            'desember' => '12',
        ];

        $clean = mb_strtolower(trim($rawDate), 'UTF-8');
        // Remove day names if any
        $clean = preg_replace('/^(senin|selasa|rabu|kamis|jumat|sabtu|minggu)[,\s]*/i', '', $clean);
        $clean = trim($clean);

        if (preg_match('/^(\d{1,2})\s+([a-z]+)\s+(\d{4})$/i', $clean, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $monthStr = mb_strtolower($m[2], 'UTF-8');
            $year = $m[3];

            if (isset($bulanMap[$monthStr])) {
                return "{$year}-{$bulanMap[$monthStr]}-{$day}";
            }
        }

        // Try standard Carbon parse
        try {
            return Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
