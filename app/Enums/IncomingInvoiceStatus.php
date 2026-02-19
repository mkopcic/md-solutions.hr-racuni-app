<?php

namespace App\Enums;

enum IncomingInvoiceStatus: string
{
    case RECEIVED = 'received';             // Primljen od FINA-e
    case PENDING_REVIEW = 'pending_review'; // Čeka tvoju provjeru
    case APPROVED = 'approved';             // Ti si odobrio
    case REJECTED = 'rejected';             // Ti si odbio
    case PAID = 'paid';                     // Ti si označio kao plaćeno
    case ARCHIVED = 'archived';             // Arhiviran

    /**
     * Vraća oznaku u boji za UI
     */
    public function badge(): string
    {
        return match ($this) {
            self::RECEIVED => 'info',
            self::PENDING_REVIEW => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::PAID => 'success',
            self::ARCHIVED => 'secondary',
        };
    }

    /**
     * Vraća label za UI
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Primljeno',
            self::PENDING_REVIEW => 'Čeka pregled',
            self::APPROVED => 'Odobreno',
            self::REJECTED => 'Odbijeno',
            self::PAID => 'Plaćeno',
            self::ARCHIVED => 'Arhivirano',
        };
    }

    /**
     * Sljedeći dozvoljeni statusi
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::RECEIVED => [self::PENDING_REVIEW, self::APPROVED, self::REJECTED],
            self::PENDING_REVIEW => [self::APPROVED, self::REJECTED],
            self::APPROVED => [self::PAID],
            self::REJECTED => [self::ARCHIVED],
            self::PAID => [self::ARCHIVED],
            self::ARCHIVED => [],
        };
    }

    /**
     * Može li prijeći u novi status
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }
}
