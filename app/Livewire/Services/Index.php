<?php

namespace App\Livewire\Services;

use App\Exports\ServicesExport;
use App\Models\Service;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app', ['title' => 'Usluge'])]
class Index extends Component
{
    use WithPagination;

    public $name;

    public $description;

    public $price;

    public $unit = 'kom';

    public $active = true;

    public $editingServiceId;

    public $search = '';

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'description' => 'nullable|max:1000',
        'price' => 'required|numeric|min:0',
        'unit' => 'required|max:20',
        'active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Naziv usluge je obavezan',
        'name.min' => 'Naziv mora imati barem 2 znaka',
        'price.required' => 'Cijena je obavezna',
        'price.numeric' => 'Cijena mora biti broj',
        'price.min' => 'Cijena ne može biti negativna',
        'unit.required' => 'Jedinica mjere je obavezna',
    ];

    public function render()
    {
        $services = Service::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.services.index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->editingServiceId = null;
        $this->dispatch('open-service-dialog');
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $this->editingServiceId = $id;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->price = $service->price;
        $this->unit = $service->unit;
        $this->active = $service->active;

        $this->dispatch('open-service-dialog');
    }

    public function save()
    {
        $this->validate();

        Service::updateOrCreate(['id' => $this->editingServiceId], [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'unit' => $this->unit,
            'active' => $this->active,
        ]);

        session()->flash('message', $this->editingServiceId ? 'Usluga uspješno ažurirana.' : 'Usluga uspješno dodana.');

        $this->reset(['editingServiceId', 'name', 'description', 'price', 'unit', 'active']);
        $this->active = true;
        $this->unit = 'kom';
        $this->dispatch('close-service-dialog');
    }

    public function delete($id)
    {
        $service = Service::find($id);

        // Provjeri postoje li stavke računa koje koriste ovu uslugu
        if ($service->invoiceItems()->count() > 0) {
            session()->flash('error', 'Nije moguće obrisati uslugu jer je korištena u računima.');

            return;
        }

        $service->delete();
        session()->flash('message', 'Usluga uspješno obrisana.');
    }

    public function toggleActive($id)
    {
        $service = Service::find($id);
        $service->active = ! $service->active;
        $service->save();

        session()->flash('message', $service->active ? 'Usluga aktivirana.' : 'Usluga deaktivirana.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->description = '';
        $this->price = '';
        $this->unit = 'kom';
        $this->active = true;
        $this->editingServiceId = null;
    }

    public function closeDialog()
    {
        $this->resetInputFields();
        $this->dispatch('close-service-dialog');
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new ServicesExport($this->search),
            'usluge_'.now()->format('Y-m-d_His').'.xlsx'
        );
    }

    public function exportCsv(): BinaryFileResponse
    {
        return Excel::download(
            new ServicesExport($this->search),
            'usluge_'.now()->format('Y-m-d_His').'.csv'
        );
    }
}
