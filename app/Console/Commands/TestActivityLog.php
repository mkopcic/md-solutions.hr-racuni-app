<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-activity-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test activity logging functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Activity Logging...');

        // Test Customer update
        $customer = \App\Models\Customer::first();
        if ($customer) {
            $oldName = $customer->name;
            $customer->name = 'Ažurirani Kupac - '.now()->format('H:i:s');
            $customer->save();
            $this->info("Customer updated: {$oldName} -> {$customer->name}");
        }

        // Check latest activity logs
        $activities = \Spatie\Activitylog\Models\Activity::latest()->take(5)->get();
        $this->info("\nLatest 5 activities:");

        foreach ($activities as $activity) {
            $causer = $activity->causer ? $activity->causer->name : 'System';
            $this->line("- Event: {$activity->event} | Subject: {$activity->subject_type} | Log: {$activity->log_name} | Causer: {$causer}");
        }

        $totalCount = \Spatie\Activitylog\Models\Activity::count();
        $this->info("\nTotal activity logs: {$totalCount}");

        return 0;
    }
}
