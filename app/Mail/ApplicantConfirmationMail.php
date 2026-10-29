<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when an applicant is pushed to the examinee list: their admission test date, time and venue.
 */
class ApplicantConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{applicant_name: string, date: string, time: string, venue: string} $data
     */
    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Admission Test Schedule - System Notification');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.applicant-confirmation');
    }
}
