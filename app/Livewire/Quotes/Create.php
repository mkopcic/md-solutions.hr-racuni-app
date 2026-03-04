<?php

namespace App\Livewire\Quotes;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Service;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    // Osnovni podaci ponude
    public $customer_id = '';

    public $issue_date;

    public $delivery_date;

    public $valid_until;

    public $note = '';

    public $internal_notes = '';

    // Nova polja za brojanje i tip
    public $quote_type = 'R';

    public $quote_number = 1;

    public $quote_year;

    public $payment_method = 'virman';

    // Stavke ponude
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
        'delivery_date' => 'nullable|date',
        'valid_until' => 'required|date|after:issue_date',
        'note' => 'nullable|string',
        'internal_notes' => 'nullable|string',
        'quote_type' => 'required|in:R,RA,P',
        'quote_number' => 'required|integer|min:1',
        'payment_method' => 'required|in:gotovina,virman,kartica',
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string',
        'items.*.unit' => 'required|in:kom,sat,dan,m2,kg',
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
        $this->valid_until = Carbon::now()->addDays(30)->format('Y-m-d');
        $this->quote_year = Carbon::now()->year;

        // Auto-generate next quote number
        $lastQuote = Quote::whereNotNull('quote_number')
            ->orderByRaw('CAST(quote_number AS UNSIGNED) DESC')
            ->first();

        $this->quote_number = $lastQuote ? (int) $lastQuote->quote_number + 1 : 1;

        $this->customers = Customer::orderBy('name')->get(['id', 'name', 'oib']);
        $this->services = Service::orderBy('name')->get(['id', 'name', 'price']);
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.quotes.create', [
            'customers' => $this->customers,
            'services' => $this->services,
            'taxRates' => $this->taxRates,
        ])->layout('components.layouts.app', ['title' => 'Nova ponuda']);
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

    public function save($status = 'draft')
    {
        $this->validate();

        $quote = Quote::create([
            'customer_id' => $this->customer_id,
            'quote_number' => $this->quote_number,
            'quote_year' => $this->quote_year,
            'quote_type' => $this->quote_type,
            'issue_date' => $this->issue_date,
            'delivery_date' => $this->delivery_date,
            'valid_until' => $this->valid_until,
            'note' => $this->note,
            'internal_notes' => $this->internal_notes,
            'payment_method' => $this->payment_method,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->taxTotal,
            'total_amount' => $this->totalAmount,
            'status' => $status,
        ]);

        foreach ($this->items as $item) {
            QuoteItem::create([
                'service_id' => $item['service_id'] ?? null,
                'quote_id' => $quote->id,
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

        session()->flash('message', 'Ponuda uspješno kreirana.');

        return redirect()->route('quotes.show', $quote);
    }

    public function saveAsDraft()
    {
        return $this->save('draft');
    }

    public function saveAndSend()
    {
        return $this->save('sent');
    }
}
