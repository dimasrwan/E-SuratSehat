<?php

namespace App\Mail;

use App\Models\Pemeriksaan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuratSehatMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pemeriksaan;
    public $pdfContent;

    public function __construct(Pemeriksaan $pemeriksaan, $pdfContent)
    {
        $this->pemeriksaan = $pemeriksaan;
        $this->pdfContent = $pdfContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Surat Keterangan Sehat',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.surat_sehat',
        );
    }

    public function attachments(): array
    {
        $filename = 'Surat-Keterangan-Sehat-' . str_replace(' ', '-', $this->pemeriksaan->nama) . '.pdf';
        
        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                    ->withMime('application/pdf'),
        ];
    }
}
