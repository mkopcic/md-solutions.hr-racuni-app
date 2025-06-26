<?php

// Test script za activity logging
require_once 'bootstrap/app.php';

$app = new \Illuminate\Foundation\Application(
    dirname(__DIR__)
);

// Test Customer update
$customer = \App\Models\Customer::first();
if ($customer) {
    $customer->name = 'Ažurirani Kupac - ' . now();
    $customer->save();
    echo "Customer updated\n";
}

// Check activity logs
$activities = \Spatie\Activitylog\Models\Activity::latest()->take(5)->get();
echo "Latest activities:\n";
foreach ($activities as $activity) {
    echo "- Event: {$activity->event}, Subject: {$activity->subject_type}, Log: {$activity->log_name}\n";
}

echo "Total activities: " . \Spatie\Activitylog\Models\Activity::count() . "\n";
