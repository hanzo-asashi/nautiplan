<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $activityName,
        public string $status,
        public ?string $notes = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Status Persetujuan Kegiatan: '.$this->activityName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $statusText = match ($this->status) {
            'proposed' => 'Diajukan untuk Ditinjau',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision' => 'Membutuhkan Revisi',
            default => $this->status,
        };

        $html = '<h3>Perubahan Status Persetujuan Kegiatan</h3>';
        $html .= "<p>Kegiatan: <strong>{$this->activityName}</strong></p>";
        $html .= "<p>Status Baru: <strong>{$statusText}</strong></p>";
        if ($this->notes) {
            $html .= "<p>Catatan Reviewer: <em>\"{$this->notes}\"</em></p>";
        }

        return new Content(
            htmlString: $html,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
