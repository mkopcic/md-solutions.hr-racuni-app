<?php

namespace App\Enums;

enum FinaStatus: string
{
    case RECEIVED = 'RECEIVED';                           // Kupac primio
    case RECEIVING_CONFIRMED = 'RECEIVING_CONFIRMED';     // Kupac potvrdio primitak
    case APPROVED = 'APPROVED';                           // Kupac odobrio
    case REJECTED = 'REJECTED';                           // Kupac odbio
    case PAYMENT_RECEIVED = 'PAYMENT_RECEIVED';           // Kupac označio kao plaćeno

    /**
     * Vraća oznaku u boji za UI
     */
    public function badge(): string
    {
        return match ($this) {
            self::RECEIVED => 'info',
            self::RECEIVING_CONFIRMED => 'primary',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::PAYMENT_RECEIVED => 'success',
        };
    }

    /**
     * Vraća label za UI
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Primljeno',
            self::RECEIVING_CONFIRMED => 'Potvrđen primitak',
            self::APPROVED => 'Odobreno',
            self::REJECTED => 'Odbijeno',
            self::PAYMENT_RECEIVED => 'Plaćeno',
        };
    }
}
