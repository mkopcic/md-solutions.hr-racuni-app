<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\ActivityLogs\Index;
use Spatie\Activitylog\Models\Activity;

class TestActivityDashboard extends Command
{
    protected $signature = 'app:test-activity-dashboard';
    protected $description = 'Test Activity Logs Dashboard';

    public function handle()
    {
        $this->info('Testing Activity Logs Dashboard...');

        // Test basic data retrieval
        $activityCount = Activity::count();
        $this->info("Total activities: {$activityCount}");

        // Test Livewire component
        try {
            $component = new Index();
            $data = $component->render()->getData();

            $this->info("✓ Livewire component initialized successfully");
            $this->info("Available log names: " . count($component->getAvailableLogNames()));
            $this->info("Available events: " . count($component->getAvailableEvents()));

            // Test pagination
            $activities = $data['activities'];
            $this->info("Paginated activities count: " . $activities->count());
            $this->info("Total activities in pagination: " . $activities->total());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
