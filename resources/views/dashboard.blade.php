<x-layouts.app :title="__('Dashboard')">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Dashboard</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Pregled prihoda i računa</p>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500">Ukupno računa</h3>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ \App\Models\Invoice::count() }}</p>
                <div class="mt-1 flex items-center text-sm">
                    <a href="{{ route('invoices.index') }}" class="text-blue-500 hover:underline" wire:navigate>Prikaži sve</a>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500">Ukupno kupaca</h3>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ \App\Models\Customer::count() }}</p>
                <div class="mt-1 flex items-center text-sm">
                    <a href="{{ route('customers.index') }}" class="text-blue-500 hover:underline" wire:navigate>Prikaži sve</a>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-sm font-medium text-zinc-500">Ukupni prihod</h3>
                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format(\App\Models\Invoice::sum('total_amount'), 2, ',', '.') }} €</p>
                <div class="mt-1 flex items-center text-sm">
                    <a href="{{ route('kpr.index') }}" class="text-blue-500 hover:underline" wire:navigate>Knjiga prometa</a>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-white">Zadnji računi</h2>
            </div>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse(\App\Models\Invoice::with('customer')->latest()->take(5)->get() as $invoice)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $invoice->customer->name }}</p>
                            <p class="text-sm text-zinc-500">Datum: {{ $invoice->formatDate($invoice->issue_date) }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-medium {{ $invoice->isPaid() ? 'text-green-600' : 'text-amber-600' }}">
                                {{ number_format($invoice->total_amount, 2, ',', '.') }} €
                            </span>
                            <a href="{{ route('invoices.show', $invoice) }}" class="rounded-lg bg-zinc-100 px-3 py-1 text-sm font-medium text-zinc-900 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700" wire:navigate>
                                Pregled
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-zinc-500">
                        Nema računa za prikaz.
                        <a href="{{ route('invoices.create') }}" class="ml-2 text-blue-500 hover:underline" wire:navigate>Kreiraj novi račun</a>
                    </div>
                @endforelse
            </div>
            @if(\App\Models\Invoice::count() > 5)
                <div class="border-t border-zinc-200 px-6 py-3 text-right dark:border-zinc-700">
                    <a href="{{ route('invoices.index') }}" class="text-sm text-blue-500 hover:underline" wire:navigate>Prikaži sve račune</a>
                </div>
            @endif
        </div>

        <div class="flex gap-4">
            <div class="w-1/2 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">Brze akcije</h2>
                <div class="grid gap-2">
                    <a href="{{ route('invoices.create') }}" class="flex w-full items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Novi račun
                    </a>
                    <a href="{{ route('customers.index') }}" class="flex w-full items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-center text-sm font-medium text-zinc-800 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        Novi kupac
                    </a>
                </div>
            </div>
            <div class="w-1/2 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">Podaci o obrtu</h2>
                @php
                    $business = \App\Models\Business::first();
                @endphp
                @if($business)
                    <div class="grid gap-1 text-sm">
                        <p><span class="text-zinc-500">Naziv:</span> {{ $business->name }}</p>
                        <p><span class="text-zinc-500">OIB:</span> {{ $business->oib }}</p>
                        <p><span class="text-zinc-500">Email:</span> {{ $business->email }}</p>
                        <div class="mt-2">
                            <a href="{{ route('business.settings') }}" class="text-blue-500 hover:underline" wire:navigate>Uredi podatke</a>
                        </div>
                    </div>
                @else
                    <div class="text-center text-zinc-500">
                        Podaci o obrtu nisu uneseni.
                        <a href="{{ route('business.settings') }}" class="ml-2 text-blue-500 hover:underline" wire:navigate>Unesi podatke</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
