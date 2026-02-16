<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $name;

    public $address;

    public $city;

    public $oib;

    public $editingCustomerId;

    public $search = '';

    public function mount()
    {
        // Ako je create=1 u URL-u, otvori dialog odmah
        if (request()->get('create') == '1') {
            $this->create();
        }
    }

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'address' => 'required|max:255',
        'city' => 'required|max:100',
        'oib' => 'required|size:11',
    ];

    protected $messages = [
        'name.required' => 'Naziv je obavezan',
        'name.min' => 'Naziv mora imati barem 2 znaka',
        'address.required' => 'Adresa je obavezna',
        'city.required' => 'Grad je obavezan',
        'oib.required' => 'OIB je obavezan',
        'oib.size' => 'OIB mora imati 11 znamenki',
    ];

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('oib', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.customers.index', [
            'customers' => $customers,
        ])->layout('components.layouts.app', ['title' => 'Kupci']);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->editingCustomerId = null;
        $this->dispatch('open-customer-dialog');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $this->editingCustomerId = $id;
        $this->name = $customer->name;
        $this->address = $customer->address;
        $this->city = $customer->city;
        $this->oib = $customer->oib;

        $this->dispatch('open-customer-dialog');
    }

    public function save()
    {
        $this->validate();

        Customer::updateOrCreate(['id' => $this->editingCustomerId], [
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'oib' => $this->oib,
        ]);

        session()->flash('message', $this->editingCustomerId ? 'Kupac uspješno ažuriran.' : 'Kupac uspješno dodan.');

        $this->reset(['editingCustomerId', 'name', 'address', 'city', 'oib']);
        $this->dispatch('close-customer-dialog');
    }

    public function delete($id)
    {
        $customer = Customer::find($id);

        // Provjeri postoje li računi za ovog kupca
        if ($customer->invoices()->count() > 0) {
            session()->flash('error', 'Nije moguće obrisati kupca jer ima povezane račune.');

            return;
        }

        $customer->delete();
        session()->flash('message', 'Kupac uspješno obrisan.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->address = '';
        $this->city = '';
        $this->oib = '';
        $this->editingCustomerId = null;
    }

    public function closeDialog()
    {
        $this->resetInputFields();
        $this->dispatch('close-customer-dialog');
    }
}
