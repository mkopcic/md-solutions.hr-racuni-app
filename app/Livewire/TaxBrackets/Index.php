<?php

namespace App\Livewire\TaxBrackets;

use App\Models\TaxBracket;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $isEdit = false;
    public $taxBracketId = null;

    // Form fields
    public $from_amount;
    public $to_amount;
    public $yearly_base;
    public $yearly_tax;
    public $monthly_tax;
    public $city_tax;
    public $quarterly_amount;

    protected $rules = [
        'from_amount' => 'required|numeric|min:0',
        'to_amount' => 'required|numeric|min:0|gt:from_amount',
        'yearly_base' => 'required|numeric|min:0',
        'yearly_tax' => 'required|numeric|min:0',
        'monthly_tax' => 'required|numeric|min:0',
        'city_tax' => 'required|numeric|min:0',
        'quarterly_amount' => 'required|numeric|min:0',
    ];

    public function render()
    {
        $taxBrackets = TaxBracket::orderBy('from_amount')->paginate(10);

        return view('livewire.tax-brackets.index', [
            'taxBrackets' => $taxBrackets
        ])->layout('components.layouts.app', ['title' => 'Porezni razredi']);
    }

    public function openModal($isEdit = false, $id = null)
    {
        $this->reset(['from_amount', 'to_amount', 'yearly_base', 'yearly_tax', 'monthly_tax', 'city_tax', 'quarterly_amount']);
        $this->isEdit = $isEdit;
        $this->taxBracketId = $id;
        $this->showModal = true;

        if ($isEdit && $id) {
            $taxBracket = TaxBracket::findOrFail($id);
            $this->from_amount = $taxBracket->from_amount;
            $this->to_amount = $taxBracket->to_amount;
            $this->yearly_base = $taxBracket->yearly_base;
            $this->yearly_tax = $taxBracket->yearly_tax;
            $this->monthly_tax = $taxBracket->monthly_tax;
            $this->city_tax = $taxBracket->city_tax;
            $this->quarterly_amount = $taxBracket->quarterly_amount;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'from_amount' => $this->from_amount,
            'to_amount' => $this->to_amount,
            'yearly_base' => $this->yearly_base,
            'yearly_tax' => $this->yearly_tax,
            'monthly_tax' => $this->monthly_tax,
            'city_tax' => $this->city_tax,
            'quarterly_amount' => $this->quarterly_amount,
        ];

        if ($this->isEdit) {
            $taxBracket = TaxBracket::findOrFail($this->taxBracketId);
            $taxBracket->update($data);
            session()->flash('message', 'Porezni razred je uspješno ažuriran.');
        } else {
            TaxBracket::create($data);
            session()->flash('message', 'Porezni razred je uspješno kreiran.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $taxBracket = TaxBracket::findOrFail($id);
        $taxBracket->delete();

        session()->flash('message', 'Porezni razred je uspješno obrisan.');
    }
}
