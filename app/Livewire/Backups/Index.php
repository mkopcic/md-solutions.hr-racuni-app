<?php

namespace App\Livewire\Backups;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Index extends Component
{
    public $backups = [];

    public $isRunningBackup = false;

    public $backupInProgress = false;

    protected $listeners = ['backupCompleted' => 'refreshBackups'];

    public function mount()
    {
        $this->loadBackups();
    }

    public function loadBackups()
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $backupName = config('backup.backup.name');

        $files = collect($disk->allFiles($backupName))
            ->filter(fn ($file) => str_ends_with($file, '.zip'))
            ->map(function ($file) use ($disk) {
                return [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => $this->formatBytes($disk->size($file)),
                    'size_bytes' => $disk->size($file),
                    'date' => $disk->lastModified($file),
                    'formatted_date' => date('d.m.Y H:i:s', $disk->lastModified($file)),
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->toArray();

        $this->backups = $files;
    }

    public function runBackup()
    {
        try {
            $this->backupInProgress = true;

            // Run backup in background
            Artisan::call('backup:run');

            $this->backupInProgress = false;

            session()->flash('message', 'Backup je uspješno kreiran!');

            $this->loadBackups();
            $this->dispatch('backupCompleted');
        } catch (\Exception $e) {
            $this->backupInProgress = false;
            session()->flash('error', 'Greška pri kreiranju backupa: '.$e->getMessage());
        }
    }

    public function deleteBackup($path)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

            if ($disk->exists($path)) {
                $disk->delete($path);
                session()->flash('message', 'Backup je uspješno obrisan.');
                $this->loadBackups();
            } else {
                session()->flash('error', 'Backup datoteka ne postoji.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Greška pri brisanju backupa: '.$e->getMessage());
        }
    }

    public function downloadBackup($path)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        if ($disk->exists($path)) {
            return response()->download($disk->path($path));
        }

        session()->flash('error', 'Backup datoteka ne postoji.');
    }

    public function refreshBackups()
    {
        $this->loadBackups();
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    public function render()
    {
        return view('livewire.backups.index')->layout('components.layouts.app', ['title' => 'Backupovi']);
    }
}
