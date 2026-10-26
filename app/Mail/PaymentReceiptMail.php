<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $borrowerName,
        public string $loanReference,
        public string $amount,
        public string $paymentDate,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment received | Loan '.$this->loanReference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-receipt',
        );
    }
}
