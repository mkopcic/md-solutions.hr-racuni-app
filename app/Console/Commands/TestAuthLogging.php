<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAuthLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-auth-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test authentication event logging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Authentication Event Logging...');

        // Get or create a test user
        $user = \App\Models\User::first();
        if (! $user) {
            $user = \App\Models\User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
            ]);
            $this->info('Created test user: '.$user->email);
        }

        // Simulate authentication events
        $this->info('Simulating authentication events...');

        // Simulate login
        event(new \Illuminate\Auth\Events\Login('web', $user, false));
        $this->info('✓ Login event fired');

        // Simulate logout
        event(new \Illuminate\Auth\Events\Logout('web', $user));
        $this->info('✓ Logout event fired');

        // Simulate failed login
        event(new \Illuminate\Auth\Events\Failed('web', null, ['email' => 'wrong@example.com', 'password' => 'wrong']));
        $this->info('✓ Failed login event fired');

        // Check latest authentication logs
        $authLogs = \Spatie\Activitylog\Models\Activity::where('log_name', 'authentication')
            ->latest()
            ->take(5)
            ->get();

        $this->info("\nLatest authentication logs:");
        foreach ($authLogs as $log) {
            $causer = $log->causer ? $log->causer->name : 'System';
            $this->line("- {$log->description} (by: {$causer})");
        }

        $totalAuthLogs = \Spatie\Activitylog\Models\Activity::where('log_name', 'authentication')->count();
        $this->info("\nTotal authentication logs: {$totalAuthLogs}");

        return 0;
    }
}
