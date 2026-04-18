<x-layouts.app :title="__('Dashboard')">
    @php
        $invoiceCount     = \App\Models\Invoice::count();
        $paidCount        = \App\Models\Invoice::whereNotNull('payment_date')->count();
        $unpaidCount      = \App\Models\Invoice::whereNull('payment_date')->count();
        $totalRevenue     = \App\Models\Invoice::sum('total_amount');
        $paidRevenue      = \App\Models\Invoice::whereNotNull('payment_date')->sum('total_amount');
        $customerCount    = \App\Models\Customer::count();
        $business         = \App\Models\Business::first();
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Dashboard</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Pregled prihoda i računa</p>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- Stat kartice --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-900/20">
                <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">Ukupno računa</p>
                <p class="mt-1 text-3xl font-bold text-blue-800 dark:text-blue-300">{{ $invoiceCount }}</p>
                <div class="mt-2 text-sm">
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1 text-blue-600 hover:underline dark:text-blue-400" wire:navigate>
                        <i class="fas fa-list text-xs"></i> Prikaži sve
                    </a>
                </div>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-5 dark:border-green-900 dark:bg-green-900/20">
                <p class="text-xs font-medium uppercase tracking-wide text-green-700 dark:text-green-400">Plaćeni prihod</p>
                <p class="mt-1 text-3xl font-bold text-green-800 dark:text-green-300">{{ number_format($paidRevenue, 2, ',', '.') }} €</p>
                <div class="mt-2 text-sm">
                    <a href="{{ route('kpr.index') }}" class="inline-flex items-center gap-1 text-green-600 hover:underline dark:text-green-400" wire:navigate>
                        <i class="fas fa-book text-xs"></i> Knjiga prometa
                    </a>
                </div>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-900/20">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-400">Neplaćeni računi</p>
                <p class="mt-1 text-3xl font-bold text-amber-800 dark:text-amber-300">{{ $unpaidCount }}</p>
                <div class="mt-2 text-sm">
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1 text-amber-600 hover:underline dark:text-amber-400" wire:navigate>
                        <i class="fas fa-exclamation-circle text-xs"></i> Pregledaj
                    </a>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ukupno kupaca</p>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $customerCount }}</p>
                <div class="mt-2 text-sm">
                    <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1 text-blue-500 hover:underline dark:text-blue-400" wire:navigate>
                        <i class="fas fa-users text-xs"></i> Prikaži sve
                    </a>
                </div>
            </div>
        </div>

        {{-- Zadnji računi --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-white">Zadnji računi</h2>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse(\App\Models\Invoice::with('customer')->latest()->take(5)->get() as $invoice)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $invoice->customer->name }}</p>
                            <p class="text-sm text-zinc-500">{{ $invoice->formatDate($invoice->issue_date) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold {{ $invoice->isPaid() ? 'text-green-600' : 'text-amber-600' }}">
                                {{ number_format($invoice->total_amount, 2, ',', '.') }} €
                            </span>
                            @if($invoice->isPaid())
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-400">Plaćen</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Čeka</span>
                            @endif
                            <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-3 py-1 text-sm font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                                <i class="fas fa-eye text-xs"></i> Pregled
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-zinc-500">
                        Nema računa za prikaz.
                        <a href="{{ route('invoices.create') }}" class="ml-2 inline-flex items-center gap-1 text-blue-500 hover:underline" wire:navigate>
                            <i class="fas fa-plus text-xs"></i> Kreiraj novi račun
                        </a>
                    </div>
                @endforelse
            </div>
            @if($invoiceCount > 5)
                <div class="border-t border-zinc-100 px-6 py-3 text-right dark:border-zinc-800">
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-500 hover:underline" wire:navigate>
                        <i class="fas fa-list text-xs"></i> Prikaži sve račune
                    </a>
                </div>
            @endif
        </div>

        {{-- Brze akcije + Podaci o obrtu --}}
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">Brze akcije</h2>
                <div class="grid gap-2">
                    <a href="{{ route('invoices.create') }}" class="flex w-full items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700" wire:navigate>
                        <i class="fas fa-file-invoice"></i>
                        Novi račun
                    </a>
                    <a href="{{ route('quotes.index') }}" class="flex w-full items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700" wire:navigate>
                        <i class="fas fa-file-alt"></i>
                        Nova ponuda
                    </a>
                    <a href="{{ route('customers.index') }}" class="flex w-full items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700" wire:navigate>
                        <i class="fas fa-user-plus"></i>
                        Novi kupac
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold text-zinc-900 dark:text-white">Podaci o obrtu</h2>
                    <a href="{{ route('business.settings') }}" class="text-sm text-blue-500 hover:underline" wire:navigate>Uredi</a>
                </div>
                @if($business)
                    <div class="space-y-2 text-sm">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-building mt-0.5 w-4 text-zinc-400"></i>
                            <div>
                                <span class="block font-medium text-zinc-900 dark:text-white">{{ $business->name }}</span>
                                <span class="text-zinc-500">{{ $business->address }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-id-card w-4 text-zinc-400"></i>
                            <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $business->oib }}</span>
                        </div>
                        @if($business->email)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope w-4 text-zinc-400"></i>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $business->email }}</span>
                            </div>
                        @endif
                        @if($business->iban)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-university w-4 text-zinc-400"></i>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $business->iban }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-center dark:border-amber-900 dark:bg-amber-900/20">
                        <p class="text-sm text-amber-700 dark:text-amber-400">Podaci o obrtu nisu uneseni.</p>
                        <a href="{{ route('business.settings') }}" class="mt-2 inline-flex items-center gap-1 text-sm text-blue-500 hover:underline" wire:navigate>
                            <i class="fas fa-plus text-xs"></i> Unesi podatke
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layouts.app>
