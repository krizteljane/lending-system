<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $borrowerName,
        public string $loanReference,
        public string $dueDate,
        public string $amountDue,
        public bool $dueToday,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->dueToday ? 'Payment due today' : 'Upcoming loan payment')." - {$this->loanReference}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-due-reminder');
    }
}
