<?php

namespace App\Enums;

enum EracunStatus: string
{
    case PENDING = 'pending';       // Kreiran, čeka slanje
    case SENDING = 'sending';       // U procesu slanja
    case SENT = 'sent';             // Uspješno poslan FINA-i
    case ACCEPTED = 'accepted';     // FINA prihvatila
    case REJECTED = 'rejected';     // FINA odbila (greška u XML-u)
    case FAILED = 'failed';         // Tehnička greška kod slanja

    /**
     * Vraća oznaku u boji za UI
     */
    public function badge(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::SENDING => 'info',
            self::SENT => 'success',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::FAILED => 'danger',
        };
    }

    /**
     * Vraća label za UI
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Čeka slanje',
            self::SENDING => 'Šalje se',
            self::SENT => 'Poslan',
            self::ACCEPTED => 'Prihvaćen',
            self::REJECTED => 'Odbijen',
            self::FAILED => 'Greška',
        };
    }

    /**
     * Da li je status finalan (ne može se više mijenjati)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::ACCEPTED, self::REJECTED, self::FAILED]);
    }
}
