<?php

namespace App\Livewire\Business;

use App\Models\Business;
use Livewire\Component;

class BusinessSettings extends Component
{
    public $name;

    public $address;

    public $oib;

    public $iban;

    public $email;

    public $phone;

    public $location;

    public $months_active;

    public $business;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'address' => 'required|max:255',
        'oib' => 'required|size:11',
        'iban' => 'required|max:34',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|max:20',
        'location' => 'nullable|max:100',
        'months_active' => 'required|integer|min:1|max:12',
    ];

    public function mount()
    {
        $this->business = Business::first();

        if ($this->business) {
            $this->name = $this->business->name;
            $this->address = $this->business->address;
            $this->oib = $this->business->oib;
            $this->iban = $this->business->iban;
            $this->email = $this->business->email;
            $this->phone = $this->business->phone;
            $this->location = $this->business->location;
            $this->months_active = $this->business->months_active;
        } else {
            // Postavi defaultne vrijednosti
            $this->months_active = 12;
        }
    }

    public function render()
    {
        return view('livewire.business.business-settings')
            ->layout('components.layouts.app', ['title' => 'Podaci o obrtu']);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'oib' => $this->oib,
            'iban' => $this->iban,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'months_active' => $this->months_active,
        ];

        if ($this->business) {
            $this->business->update($data);
        } else {
            $this->business = Business::create($data);
        }

        session()->flash('message', 'Podaci o obrtu uspješno spremljeni.');
    }
}
