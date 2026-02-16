<?php

namespace App\Livewire\Invoices;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
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

    // Nova polja za brojanje i tip
    public $invoice_type = 'R';

    public $invoice_number = 1;

    public $invoice_year;

    public $payment_method = 'virman';

    // Stavke računa
    public $items = [];

    public $totalAmount = 0;

    public $subtotal = 0;

    public $taxTotal = 0;

    // Za odabir kupca
    public $customers = [];

    // Za odabir servisa
    public $services = [];

    // Za odabir poreznih stopa (PDV stope)
    public $taxRates = [0, 5, 13, 25];

    // Za validaciju
    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'issue_date' => 'required|date',
        'delivery_date' => 'required|date',
        'due_date' => 'required|date',
        'note' => 'nullable|string',
        'advance_note' => 'nullable|string',
        'invoice_type' => 'required|in:R,RA,P',
        'invoice_number' => 'required|integer|min:1',
        'payment_method' => 'required|in:gotovina,virman,kartica',
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string',
        'items.*.unit' => 'required|in:kom,sat,dan',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.discount' => 'nullable|numeric|min:0',
        'items.*.tax_rate' => 'required|numeric|min:0',
        'items.*.total' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->issue_date = Carbon::now()->format('Y-m-d');
        $this->delivery_date = Carbon::now()->format('Y-m-d');
        $this->due_date = Carbon::now()->addDays(15)->format('Y-m-d');
        $this->invoice_year = Carbon::now()->year;

        // Auto-generate next invoice number
        $lastInvoice = Invoice::whereNotNull('invoice_number')
            ->orderByRaw('CAST(invoice_number AS UNSIGNED) DESC')
            ->first();

        $this->invoice_number = $lastInvoice ? (int) $lastInvoice->invoice_number + 1 : 1;

        $this->customers = Customer::orderBy('name')->get(['id', 'name', 'oib']);
        $this->services = Service::orderBy('name')->get(['id', 'name', 'price']);
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.invoices.create', [
            'customers' => $this->customers,
            'services' => $this->services,
            'taxRates' => $this->taxRates,
        ])->layout('components.layouts.app', ['title' => 'Novi račun']);
    }

    public function addItem()
    {
        $defaultTaxRate = 25.00;

        $this->items[] = [
            'service_id' => null,
            'name' => '',
            'unit' => 'kom',
            'quantity' => 1,
            'price' => 0,
            'discount' => 0,
            'tax_rate' => $defaultTaxRate,
            'tax_amount' => 0,
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
            $this->items[$index]['service_id'] = $service->id;
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
        $taxRate = floatval($item['tax_rate'] ?: 0);

        // Calculate base total
        $subtotal = $quantity * $price;

        // Apply discount
        if ($discount > 0) {
            $subtotal = $subtotal - ($subtotal * $discount / 100);
        }

        // Calculate tax
        $taxAmount = $subtotal * ($taxRate / 100);

        // Total with tax
        $total = $subtotal + $taxAmount;

        $this->items[$index]['tax_amount'] = round($taxAmount, 2);
        $this->items[$index]['total'] = round($total, 2);

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->subtotal = 0;
        $this->taxTotal = 0;
        $this->totalAmount = 0;

        foreach ($this->items as $item) {
            $itemSubtotal = floatval($item['total'] ?: 0) - floatval($item['tax_amount'] ?: 0);
            $this->subtotal += $itemSubtotal;
            $this->taxTotal += floatval($item['tax_amount'] ?: 0);
            $this->totalAmount += floatval($item['total'] ?: 0);
        }

        $this->subtotal = round($this->subtotal, 2);
        $this->taxTotal = round($this->taxTotal, 2);
        $this->totalAmount = round($this->totalAmount, 2);
    }

    public function save()
    {
        $this->validate();

        $invoice = Invoice::create([
            'customer_id' => $this->customer_id,
            'invoice_number' => $this->invoice_number,
            'invoice_year' => $this->invoice_year,
            'invoice_type' => $this->invoice_type,
            'issue_date' => $this->issue_date,
            'delivery_date' => $this->delivery_date,
            'due_date' => $this->due_date,
            'note' => $this->note,
            'advance_note' => $this->advance_note,
            'payment_method' => $this->payment_method,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->taxTotal,
            'total_amount' => $this->totalAmount,
            'paid_cash' => 0,
            'paid_transfer' => 0,
        ]);

        foreach ($this->items as $item) {
            InvoiceItem::create([
                'service_id' => $item['service_id'] ?? null,
                'invoice_id' => $invoice->id,
                'name' => $item['name'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $item['discount'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'total' => $item['total'],
            ]);
        }

        // Ako želimo da se novi unos odmah evidentira u knjizi prometa, tu bi dodali logiku za KPR

        session()->flash('message', 'Račun uspješno kreiran.');

        return redirect()->route('invoices.show', $invoice);
    }

    public function updatedInvoiceType()
    {
        // Invoice number doesn't change with type
    }
}
