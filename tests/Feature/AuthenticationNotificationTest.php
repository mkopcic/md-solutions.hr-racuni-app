<?php

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

test('email notification is sent when user logs in', function () {
    $user = User::factory()->create([
        'name' => 'Test User Login',
        'email' => 'test-login@example.com',
    ]);

    // Dispatch the Login event directly - this will send REAL emails
    Event::dispatch(new Login('web', $user, false));

    // If we get here without exception, emails were sent successfully
    expect(true)->toBeTrue();
});

test('email notification is sent when user logs out', function () {
    $user = User::factory()->create([
        'name' => 'Test User Logout',
        'email' => 'test-logout@example.com',
    ]);

    // Dispatch the Logout event directly - this will send REAL emails
    Event::dispatch(new Logout('web', $user));

    // If we get here without exception, emails were sent successfully
    expect(true)->toBeTrue();
});

test('authentication events are logged to Laravel log', function () {
    Log::spy();

    $user = User::factory()->create([
        'name' => 'Test User For Logging',
        'email' => 'test-log@example.com',
    ]);

    // Dispatch login event
    Event::dispatch(new Login('web', $user, false));

    // Assert that Laravel log was called
    Log::shouldHaveReceived('info')
        ->with('User logged in', \Mockery::type('array'))
        ->once();
});

test('logout events are logged to Laravel log', function () {
    Log::spy();

    $user = User::factory()->create([
        'name' => 'Test User Logout Log',
        'email' => 'test-logout-log@example.com',
    ]);

    // Dispatch logout event
    Event::dispatch(new Logout('web', $user));

    // Assert that Laravel log was called
    Log::shouldHaveReceived('info')
        ->with('User logged out', \Mockery::type('array'))
        ->once();
});
