<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('backups page requires authentication', function () {
    get(route('backups.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can access backups page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('backups.index'))
        ->assertOk()
        ->assertSee('Backupovi');
});

test('backups page displays list of backups', function () {
    $user = User::factory()->create();

    // Create a test backup file
    $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
    $backupName = config('backup.backup.name');
    $testFile = $backupName.'/test-backup.zip';
    $disk->put($testFile, 'test content');

    actingAs($user)
        ->get(route('backups.index'))
        ->assertOk()
        ->assertSee('test-backup.zip');

    // Cleanup
    $disk->delete($testFile);
});

test('user can create new backup', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test('backups.index')
        ->call('runBackup')
        ->assertHasNoErrors()
        ->assertDispatched('backupCompleted');
});

test('user can delete backup', function () {
    $user = User::factory()->create();

    // Create a test backup file
    $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
    $backupName = config('backup.backup.name');
    $testFile = $backupName.'/test-backup-delete.zip';
    $disk->put($testFile, 'test content');

    actingAs($user);

    Livewire::test('backups.index')
        ->call('deleteBackup', $testFile)
        ->assertHasNoErrors();

    expect($disk->exists($testFile))->toBeFalse();
});

test('backups page shows empty state when no backups exist', function () {
    $user = User::factory()->create();

    // Clean any existing backups
    $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
    $backupName = config('backup.backup.name');
    $files = $disk->allFiles($backupName);
    foreach ($files as $file) {
        $disk->delete($file);
    }

    actingAs($user)
        ->get(route('backups.index'))
        ->assertOk()
        ->assertSee('Nema backupova');
});
