<?php

declare(strict_types=1);

use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoicesShow;
use App\Mail\InvoicePdfMail;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'iban' => 'HR1210010051863000160', // Valid Croatian IBAN
    ]);
    $this->customer = Customer::factory()->create();
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'total_amount' => 1000.00,
    ]);
});

test('sendPdfEmail sends email to authenticated user from Show component', function () {
    Mail::fake();

    $this->actingAs($this->user);

    Livewire::test(InvoicesShow::class, ['invoice' => $this->invoice])
        ->call('sendPdfEmail')
        ->assertHasNoErrors();

    Mail::assertQueued(InvoicePdfMail::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
});

test('sendPdfEmail includes invoice and business data', function () {
    Mail::fake();

    $this->actingAs($this->user);

    Livewire::test(InvoicesShow::class, ['invoice' => $this->invoice])
        ->call('sendPdfEmail');

    Mail::assertQueued(InvoicePdfMail::class, function ($mail) {
        return $mail->invoice->id === $this->invoice->id
            && $mail->business->id === $this->business->id;
    });
});

test('sendPdfEmail sends email to authenticated user from Index component', function () {
    Mail::fake();

    $this->actingAs($this->user);

    // Create additional invoices with valid customer
    Invoice::factory()->count(5)->create(['customer_id' => $this->customer->id]);

    Livewire::test(InvoicesIndex::class)
        ->call('sendPdfEmail', $this->invoice->id)
        ->assertHasNoErrors();

    Mail::assertQueued(InvoicePdfMail::class, function ($mail) {
        return $mail->hasTo($this->user->email)
            && $mail->invoice->id === $this->invoice->id;
    });
});

test('sendPdfEmail fails when user is not authenticated', function () {
    Mail::fake();

    Livewire::test(InvoicesShow::class, ['invoice' => $this->invoice])
        ->call('sendPdfEmail');

    Mail::assertNothingQueued();
});

test('sendPdfEmail fails when invoice is not found in Index component', function () {
    Mail::fake();

    $this->actingAs($this->user);

    // Create additional invoices with valid customer
    Invoice::factory()->count(5)->create(['customer_id' => $this->customer->id]);

    Livewire::test(InvoicesIndex::class)
        ->call('sendPdfEmail', 99999);

    Mail::assertNothingQueued();
});

test('sendPdfEmail creates valid PDF attachment', function () {
    Mail::fake();

    $this->actingAs($this->user);

    Livewire::test(InvoicesShow::class, ['invoice' => $this->invoice])
        ->call('sendPdfEmail');

    Mail::assertQueued(InvoicePdfMail::class, function ($mail) {
        $attachments = $mail->attachments();

        expect($attachments)->toHaveCount(1);
        expect($attachments[0])->toBeInstanceOf(\Illuminate\Mail\Mailables\Attachment::class);

        return true;
    });
});

test('sendPdfEmail includes correct invoice details in email', function () {
    Mail::fake();

    $this->actingAs($this->user);

    Livewire::test(InvoicesShow::class, ['invoice' => $this->invoice])
        ->call('sendPdfEmail');

    Mail::assertQueued(InvoicePdfMail::class, function ($mail) {
        return $mail->invoice->customer_id === $this->customer->id
            && $mail->invoice->total_amount == 1000.00;
    });
});
