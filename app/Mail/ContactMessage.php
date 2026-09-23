<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Contact Azohub] ' . $this->data['subject'],
            // Envelope::normalizeAddresses() ne lit que les VALEURS d'un tableau
            // (elle ignore les clés) : passer un tableau associatif [email => nom]
            // faisait donc passer le nom lui-même comme adresse, d'où l'échec RFC 2822.
            // Il faut un objet Address explicite.
            replyTo: [new Address($this->data['email'], $this->data['name'])],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact',
            with: $this->data,
        );
    }
}
