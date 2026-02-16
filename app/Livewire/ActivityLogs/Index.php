<?php

namespace App\Livewire\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $logName = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $event = '';

    public $perPage = 25;

    public $showModal = false;

    public $selectedActivity = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'logName' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'event' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLogName()
    {
        $this->resetPage();
    }

    public function updatingEvent()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'logName', 'dateFrom', 'dateTo', 'event']);
        $this->resetPage();
    }

    public function showDetails($activityId)
    {
        // Toggle functionality - if same activity is already selected, close it
        if ($this->selectedActivity && $this->selectedActivity->id == $activityId) {
            $this->closeModal();

            return;
        }

        $this->selectedActivity = Activity::with(['subject', 'causer'])->find($activityId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedActivity = null;
    }

    public function getAvailableLogNames()
    {
        return Activity::distinct()
            ->pluck('log_name')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getAvailableEvents()
    {
        return Activity::distinct()
            ->pluck('event')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    public function render()
    {
        $query = Activity::with(['subject', 'causer'])
            ->latest();

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('subject_type', 'like', '%'.$this->search.'%')
                    ->orWhereHas('causer', function ($causerQuery) {
                        $causerQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->logName) {
            $query->where('log_name', $this->logName);
        }

        if ($this->event) {
            $query->where('event', $this->event);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $activities = $query->paginate($this->perPage);

        return view('livewire.activity-logs.index', [
            'activities' => $activities,
            'availableLogNames' => $this->getAvailableLogNames(),
            'availableEvents' => $this->getAvailableEvents(),
        ])->layout('components.layouts.app', ['title' => 'Activity Logs']);
    }
}
