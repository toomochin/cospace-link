<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation)
    {
    }

    public function envelope(): Envelope
    {
        // ★ 決済方法に応じて件名を切り替える
        $subject = match ($this->reservation->payment_type) {
            'onsite' => '【Co-Space Link】ご予約キャンセルのお知らせ（現地払い）',
            'free' => '【Co-Space Link】ご予約キャンセルのお知らせ',
            default => '【Co-Space Link】ご予約キャンセル・返金手続きのお知らせ',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation_cancelled',
        );
    }
}