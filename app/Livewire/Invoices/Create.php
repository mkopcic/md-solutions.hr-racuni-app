<?php

namespace App\Livewire\Invoices;

use App\Models\Customer;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    // Osnovni podaci računa
    public $customer_id = '';
    public $issue_date;
    public $delivery_date;
    public $due_date;
    public $note = '';
    public $advance_note = '';

    // Stavke računa
    public $items = [];
    public $totalAmount = 0;

    // Za odabir kupca
    public $customers = [];

    // Za odabir servisa
    public $services = [];

    // Za validaciju
    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'issue_date' => 'required|date',
        'delivery_date' => 'required|date',
        'due_date' => 'required|date',
        'note' => 'nullable|string',
        'advance_note' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.discount' => 'nullable|numeric|min:0',
        'items.*.total' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->issue_date = Carbon::now()->format('Y-m-d');
        $this->delivery_date = Carbon::now()->format('Y-m-d');
        $this->due_date = Carbon::now()->addDays(15)->format('Y-m-d');

        $this->addItem();
        $this->customers = Customer::orderBy('name')->get(['id', 'name', 'oib']);
        $this->services = Service::orderBy('name')->get(['id', 'name', 'price']);
    }

    public function render()
    {
        return view('livewire.invoices.create', [
            'customers' => $this->customers,
            'services' => $this->services,
        ])->layout('components.layouts.app', ['title' => 'Novi račun']);
    }

    public function addItem()
    {
        $this->items[] = [
            'name' => '',
            'quantity' => 1,
            'price' => 0,
            'discount' => 0,
            'total' => 0,
        ];
    }

    public function removeItem($index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }

        $this->calculateTotal();
    }

    public function selectService($index, $serviceId)
    {
        $service = Service::find($serviceId);
        if ($service) {
            $this->items[$index]['name'] = $service->name;
            $this->items[$index]['price'] = $service->price;
            $this->updateItemTotal($index);
        }
    }

    public function updateItemTotal($index)
    {
        $item = $this->items[$index];
        $quantity = floatval($item['quantity'] ?: 0);
        $price = floatval($item['price'] ?: 0);
        $discount = floatval($item['discount'] ?: 0);

        $total = $quantity * $price;

        if ($discount > 0) {
            $total = $total - ($total * $discount / 100);
        }

        $this->items[$index]['total'] = round($total, 2);

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->totalAmount = 0;

        foreach ($this->items as $item) {
            $this->totalAmount += floatval($item['total'] ?: 0);
        }
    }

    public function save()
    {
        $this->validate();

        $invoice = Invoice::create([
            'customer_id' => $this->customer_id,
            'issue_date' => $this->issue_date,
            'delivery_date' => $this->delivery_date,
            'due_date' => $this->due_date,
            'note' => $this->note,
            'advance_note' => $this->advance_note,
            'total_amount' => $this->totalAmount,
            'paid_cash' => 0,
            'paid_transfer' => 0,
        ]);

        foreach ($this->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $item['discount'],
                'total' => $item['total'],
            ]);
        }

        // Ako želimo da se novi unos odmah evidentira u knjizi prometa, tu bi dodali logiku za KPR

        session()->flash('message', 'Račun uspješno kreiran.');

        return redirect()->route('invoices.show', $invoice);
    }
}
