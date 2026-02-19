<?php

namespace App\Livewire\Eracun\Incoming;

use App\Models\IncomingInvoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Detalji Ulaznog Računa'])]
class Show extends Component
{
    public IncomingInvoice $invoice;

    public $rejectionReason = '';

    public function mount($id)
    {
        $this->invoice = IncomingInvoice::with(['items', 'eracunLog'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.eracun.incoming.show');
    }

    public function goBack()
    {
        return $this->redirect(route('eracun.incoming.index'), navigate: false);
    }

    public function approve()
    {
        try {
            if ($this->invoice->approve(auth()->id())) {
                session()->flash('message', 'Račun je uspješno odobren.');
                $this->invoice->refresh();
            } else {
                session()->flash('error', 'Nije moguće odobriti račun u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:3|max:500',
        ], [
            'rejectionReason.required' => 'Razlog odbijanja je obavezan.',
            'rejectionReason.min' => 'Razlog mora imati najmanje 3 znaka.',
            'rejectionReason.max' => 'Razlog može imati najviše 500 znakova.',
        ]);

        try {
            if ($this->invoice->reject(auth()->id(), $this->rejectionReason)) {
                session()->flash('message', 'Račun je odbijen.');
                $this->invoice->refresh();
                $this->rejectionReason = '';
            } else {
                session()->flash('error', 'Nije moguće odbiti račun u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function markAsPaid()
    {
        try {
            if ($this->invoice->markAsPaid()) {
                session()->flash('message', 'Račun je označen kao plaćen.');
                $this->invoice->refresh();
            } else {
                session()->flash('error', 'Nije moguće označiti račun kao plaćen u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function archive()
    {
        try {
            if ($this->invoice->archive()) {
                session()->flash('message', 'Račun je arhiviran.');
                $this->invoice->refresh();
            } else {
                session()->flash('error', 'Nije moguće arhivirati račun u trenutnom statusu.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška: '.$e->getMessage());
        }
    }

    public function viewXml()
    {
        $this->dispatch('show-xml-modal', xml: $this->invoice->ubl_xml, title: 'UBL XML - '.$this->invoice->invoice_number);
    }
}
