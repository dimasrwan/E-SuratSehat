<?php

namespace App\Jobs;

use App\Mail\SuratSehatMail;
use App\Models\Pemeriksaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSuratSehatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pemeriksaan;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [10, 30, 60];

    public function __construct(Pemeriksaan $pemeriksaan)
    {
        $this->pemeriksaan = $pemeriksaan;
    }

    public function handle(): void
    {
        if (!$this->pemeriksaan->email) {
            return;
        }

        // Update status to 'Mengirim'
        $this->pemeriksaan->update([
            'status_pengiriman' => 'Mengirim'
        ]);

        // Generate PDF content in-memory
        $pdf = Pdf::loadView('pemeriksaan.pdf', ['pemeriksaan' => $this->pemeriksaan])
            ->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        // Send Email
        Mail::to($this->pemeriksaan->email)->send(new SuratSehatMail($this->pemeriksaan, $pdfContent));

        // Update Status to 'Terkirim' on success
        $this->pemeriksaan->update([
            'status_pengiriman' => 'Terkirim',
            'waktu_pengiriman' => now()
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->pemeriksaan->update([
            'status_pengiriman' => 'Gagal'
        ]);

        \Log::error('Failed to send email to ' . $this->pemeriksaan->email . ': ' . $exception->getMessage());
    }
}
