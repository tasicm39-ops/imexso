<?php

namespace App\Mail;

use App\Models\Car;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Offer $offer,
        public Car $car,
        public User $sender,
        public string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        $companyName = $this->sender->company_name ?? $this->sender->name;

        return new Envelope(
            from: new Address(
                config('mail.from.address', 'noreply@imexso.com'),
                $companyName,
            ),
            replyTo: [
                new Address($this->sender->email, $companyName),
            ],
            subject: 'Offre véhicule - '.$this->car->make.' '.$this->car->model,
        );
    }

    public function content(): Content
    {
        $logoUrl = null;
        if ($this->sender->logo && Storage::disk('public')->exists($this->sender->logo)) {
            $logoUrl = Storage::disk('public')->url($this->sender->logo);
        }

        return new Content(
            view: 'emails.offer',
            with: [
                'offer' => $this->offer,
                'car' => $this->car,
                'sender' => $this->sender,
                'logoUrl' => $logoUrl,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorage($this->pdfPath)
                ->as('Devis.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
