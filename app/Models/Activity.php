<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    /**
     * Zamjenjuje neispravne UTF-8 znakove umjesto da baci exception pri JSON encodiranju.
     * Potrebno jer properties kolumna može sadržavati podatke s neispravnim UTF-8 encodingom
     * (npr. iz legacy unosa s hrvatskim znakovima).
     */
    protected int $jsonEncodeOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;

    /**
     * Dohvaća opis aktivnosti na hrvatskom jeziku
     */
    public function getHrDescription(): string
    {
        $descriptions = [
            'created' => 'kreiran',
            'updated' => 'ažuriran',
            'deleted' => 'obrisan',
            'login' => 'prijava',
            'logout' => 'odjava',
            'viewed' => 'pregledan',
            'downloaded' => 'preuzet',
            'exported' => 'eksportiran',
            'sent' => 'poslan',
            'paid' => 'plaćen',
        ];

        $event = $this->event ?? 'nepoznato';
        $hrEvent = $descriptions[$event] ?? $event;

        $subjectType = $this->subject_type ? class_basename($this->subject_type) : 'Nepoznat objekt';

        $subjectNames = [
            'Invoice' => 'Račun',
            'Customer' => 'Kupac',
            'Business' => 'Obrt',
            'Service' => 'Usluga',
            'KprEntry' => 'KPR Zapis',
            'TaxBracket' => 'Porezni razred',
            'User' => 'Korisnik',
            'InvoiceItem' => 'Stavka računa',
        ];

        $hrSubjectType = $subjectNames[$subjectType] ?? $subjectType;

        if ($this->subject_id) {
            return ucfirst($hrSubjectType)." #{$this->subject_id} je ".$hrEvent;
        }

        return ucfirst($hrSubjectType).' je '.$hrEvent;
    }

    /**
     * Dohvaća naziv modela na hrvatskom
     */
    public function getSubjectTypeHr(): string
    {
        $subjectType = $this->subject_type ? class_basename($this->subject_type) : 'Nepoznat objekt';

        $translations = [
            'Invoice' => 'Račun',
            'Customer' => 'Kupac',
            'Business' => 'Obrt',
            'Service' => 'Usluga',
            'KprEntry' => 'KPR Zapis',
            'TaxBracket' => 'Porezni razred',
            'User' => 'Korisnik',
            'InvoiceItem' => 'Stavka računa',
        ];

        return $translations[$subjectType] ?? $subjectType;
    }

    /**
     * Dohvaća event na hrvatskom
     */
    public function getEventHr(): string
    {
        $translations = [
            'created' => 'Kreiran',
            'updated' => 'Ažuriran',
            'deleted' => 'Obrisan',
            'login' => 'Prijava',
            'logout' => 'Odjava',
            'viewed' => 'Pregledan',
            'downloaded' => 'Preuzet',
            'exported' => 'Eksportiran',
            'sent' => 'Poslan',
            'paid' => 'Plaćen',
        ];

        return $translations[$this->event ?? 'unknown'] ?? ($this->event ?? 'Nepoznato');
    }
}
