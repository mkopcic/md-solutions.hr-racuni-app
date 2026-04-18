<div>
<div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                <i class="fas fa-book"></i>
                Knjiga prometa
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">Evidencija prometa i praćenje prihoda</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <button wire:click="generateKprEntries" class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                <i class="fas fa-sync-alt"></i>
                Generiraj nove
            </button>
            <button wire:click="regenerateKprEntries"
                wire:confirm="Jeste li sigurni? Ovo će obrisati sve postojeće KPR unose i ponovno ih generirati s ispravnim mjesecima."
                class="rounded-lg bg-amber-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-4 focus:ring-amber-300 dark:focus:ring-amber-800">
                <i class="fas fa-redo"></i>
                Regeneriraj sve
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300">
            <i class="fas fa-check-circle shrink-0"></i>
            {{ session('message') }}
        </div>
    @endif

    <!-- Export Radnje -->
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <button wire:click="exportExcel"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-excel text-green-600"></i>
            Excel
        </button>
        <button wire:click="exportCsv"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-csv text-orange-500"></i>
            CSV
        </button>
        <div class="mx-2 h-6 w-px bg-zinc-300 dark:bg-zinc-600"></div>
        <button wire:click="exportYearExcel"
            class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:border-green-900 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30">
            <i class="fas fa-file-excel text-green-600"></i>
            Excel — {{ $year }}
        </button>
        <button wire:click="exportYearCsv"
            class="inline-flex items-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-medium text-orange-700 hover:bg-orange-100 dark:border-orange-900 dark:bg-orange-900/20 dark:text-orange-400 dark:hover:bg-orange-900/30">
            <i class="fas fa-file-csv text-orange-500"></i>
            CSV — {{ $year }}
        </button>
    </div>

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
            <div class="grid w-full max-w-xl grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Mjesec</flux:label>
                    <flux:select wire:model.live="month" id="month">
                        @foreach($months as $key => $monthName)
                            <option value="{{ $key }}">{{ $monthName }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Godina</flux:label>
                    <flux:select wire:model.live="year" id="year">
                        @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="grid w-full grid-cols-2 gap-4 md:max-w-xs">
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
                    <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">Mjesečni prihod</p>
                    <p class="mt-1 text-xl font-bold text-blue-800 dark:text-blue-300">{{ number_format($totalMonthlyAmount, 2, ',', '.') }} €</p>
                </div>
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
                    <p class="text-xs font-medium uppercase tracking-wide text-green-700 dark:text-green-400">Godišnji prihod</p>
                    <p class="mt-1 text-xl font-bold text-green-800 dark:text-green-300">{{ number_format($totalYearlyAmount, 2, ',', '.') }} €</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop tablica -->
    <div class="hidden overflow-hidden rounded-xl border border-zinc-200 bg-white md:block dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-calendar"></i>
                            Datum
                        </th>
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-file-invoice"></i>
                            Račun
                        </th>
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-user"></i>
                            Kupac
                        </th>
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-euro-sign"></i>
                            Iznos (€)
                        </th>
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-cogs"></i>
                            Akcije
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                {{ $entry->invoice->issue_date->format('d.m.Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                <a href="{{ route('invoices.show', $entry->invoice_id) }}" class="text-blue-600 hover:underline dark:text-blue-400 inline-flex items-center gap-1">
                                    <i class="fas fa-eye"></i>
                                    Račun #{{ $entry->invoice_id }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                {{ $entry->invoice->customer->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-zinc-900 dark:text-white">
                                {{ number_format($entry->amount, 2, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <button wire:click="deleteEntry({{ $entry->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 inline-flex items-center gap-1" onclick="return confirm('Jeste li sigurni da želite obrisati ovaj unos?')">
                                    <i class="fas fa-trash"></i>
                                    Obriši
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Nema pronađenih unosa za odabrani period.</p>
                                    <p class="text-xs mt-1">Evidentirajte račune kako bi se prikazali u KPR.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            {{ $entries->links() }}
        </div>
    </div>

    <!-- Mobilni prikaz -->
    <div class="overflow-hidden rounded-xl border border-zinc-200 md:hidden dark:border-zinc-700">
        <div class="space-y-0 divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse ($entries as $entry)
                <div class="bg-white p-4 dark:bg-zinc-900">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="flex-1">
                            <a href="{{ route('invoices.show', $entry->invoice_id) }}" class="text-blue-600 hover:underline dark:text-blue-400 inline-flex items-center gap-1">
                                <i class="fas fa-file-invoice"></i>
                                <span class="font-semibold">Račun #{{ $entry->invoice_id }}</span>
                            </a>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $entry->invoice->customer->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-zinc-900 dark:text-white">{{ number_format($entry->amount, 2, ',', '.') }} €</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm border-t border-zinc-200 dark:border-zinc-700 pt-3">
                        <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $entry->invoice->issue_date->format('d.m.Y') }}</span>
                        </div>
                        <button wire:click="deleteEntry({{ $entry->id }})" onclick="return confirm('Jeste li sigurni da želite obrisati ovaj unos?')"
                            class="inline-flex items-center gap-1 rounded-lg border border-red-600 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30">
                            <i class="fas fa-trash"></i>
                            Obriši
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 text-center dark:bg-zinc-900">
                    <i class="fas fa-inbox text-4xl text-zinc-300 dark:text-zinc-600 mb-3"></i>
                    <p class="text-zinc-500 dark:text-zinc-400">Nema pronađenih unosa za odabrani period.</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Evidentirajte račune kako bi se prikazali u KPR.</p>
                </div>
            @endforelse
        </div>

        <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            {{ $entries->links() }}
        </div>
    </div>
        </div>
    </div>
</div>
