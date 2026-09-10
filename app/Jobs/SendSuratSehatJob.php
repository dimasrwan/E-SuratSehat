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
use Exception;

class SendSuratSehatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pemeriksaan;

    public function __construct(Pemeriksaan $pemeriksaan)
    {
        $this->pemeriksaan = $pemeriksaan;
    }

    public function handle(): void
    {
        try {
            if (!$this->pemeriksaan->email) {
                return;
            }

            // Generate PDF content
            $pdf = Pdf::loadView('pemeriksaan.pdf', ['pemeriksaan' => $this->pemeriksaan])->setPaper('a4', 'portrait');
            $pdfContent = $pdf->output();

            // Send Email
            Mail::to($this->pemeriksaan->email)->send(new SuratSehatMail($this->pemeriksaan, $pdfContent));

            // Update Status
            $this->pemeriksaan->update([
                'status_pengiriman' => 'Terkirim',
                'waktu_pengiriman' => now()
            ]);

        } catch (Exception $e) {
            // Update Status on Failure
            $this->pemeriksaan->update([
                'status_pengiriman' => 'Gagal'
            ]);
            
            \Log::error('Failed to send email to ' . $this->pemeriksaan->email . ': ' . $e->getMessage());
        }
    }
}
