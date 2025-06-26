<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\ActivityLogs\Index;
use Spatie\Activitylog\Models\Activity;

class TestActivityModal extends Command
{
    protected $signature = 'app:test-activity-modal';
    protected $description = 'Test Activity Details Modal';

    public function handle()
    {
        $this->info('Testing Activity Modal...');

        try {
            $component = new Index();

            // Get first activity with properties
            $activity = Activity::with(['subject', 'causer'])
                ->whereNotNull('properties')
                ->first();

            if (!$activity) {
                $activity = Activity::with(['subject', 'causer'])->first();
            }

            if (!$activity) {
                $this->error('No activities found');
                return Command::FAILURE;
            }

            $this->info("Testing with activity ID: {$activity->id}");
            $this->info("Activity description: {$activity->description}");
            $this->info("Activity log name: {$activity->log_name}");

            // Test modal opening
            $component->showDetails($activity->id);

            if ($component->showModal) {
                $this->info("✓ Modal opened successfully");

                if ($component->selectedActivity) {
                    $this->info("✓ Selected activity loaded");
                    $this->info("Selected activity ID: {$component->selectedActivity->id}");
                    $this->info("Has properties: " . ($component->selectedActivity->properties->isNotEmpty() ? 'Yes' : 'No'));
                    $this->info("Has causer: " . ($component->selectedActivity->causer ? 'Yes' : 'No'));
                    $this->info("Has subject: " . ($component->selectedActivity->subject ? 'Yes' : 'No'));
                } else {
                    $this->error("Selected activity not loaded");
                }
            } else {
                $this->error("Modal not opened");
            }

            // Test modal closing
            $component->closeModal();

            if (!$component->showModal) {
                $this->info("✓ Modal closed successfully");
            } else {
                $this->error("Modal not closed");
            }

            // Test toggle functionality - opening same activity again should close it
            $this->info("\n--- Testing Toggle Functionality ---");
            $component->showDetails($activity->id);
            $this->info("Opened activity again");

            // Now click same activity again - should close
            $component->showDetails($activity->id);

            if (!$component->showModal) {
                $this->info("✓ Toggle works - modal closed when same activity clicked");
            } else {
                $this->error("Toggle failed - modal should be closed");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
