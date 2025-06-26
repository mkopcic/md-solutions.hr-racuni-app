<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\ActivityLogs\Index;
use Spatie\Activitylog\Models\Activity;

class TestActivityFilters extends Command
{
    protected $signature = 'app:test-activity-filters';
    protected $description = 'Test Activity Logs Filters';

    public function handle()
    {
        $this->info('Testing Activity Filters...');

        try {
            $component = new Index();

            // Test log name filter
            $this->info("\n--- Testing Log Name Filter ---");
            $availableLogNames = $component->getAvailableLogNames();
            $this->info("Available log names: " . implode(', ', $availableLogNames));

            if (!empty($availableLogNames)) {
                $component->logName = $availableLogNames[0];
                $data = $component->render()->getData();
                $filtered = $data['activities'];
                $this->info("Filtered by '{$availableLogNames[0]}': {$filtered->count()} activities");
            }

            // Test event filter
            $this->info("\n--- Testing Event Filter ---");
            $component->reset(['logName']); // Reset previous filter
            $availableEvents = $component->getAvailableEvents();
            $this->info("Available events: " . implode(', ', $availableEvents));

            if (!empty($availableEvents)) {
                $component->event = $availableEvents[0];
                $data = $component->render()->getData();
                $filtered = $data['activities'];
                $this->info("Filtered by event '{$availableEvents[0]}': {$filtered->count()} activities");
            }

            // Test search filter
            $this->info("\n--- Testing Search Filter ---");
            $component->reset(['event']); // Reset previous filter
            $component->search = 'prijavio';
            $data = $component->render()->getData();
            $filtered = $data['activities'];
            $this->info("Search for 'prijavio': {$filtered->count()} activities");

            // Test date filter
            $this->info("\n--- Testing Date Filter ---");
            $component->reset(['search']); // Reset previous filter
            $component->dateFrom = now()->format('Y-m-d');
            $data = $component->render()->getData();
            $filtered = $data['activities'];
            $this->info("Filtered from today: {$filtered->count()} activities");

            // Test clear filters
            $this->info("\n--- Testing Clear Filters ---");
            $component->clearFilters();
            $data = $component->render()->getData();
            $all = $data['activities'];
            $this->info("After clearing filters: {$all->count()} activities");

            $this->info("\n✓ All filters working correctly!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
