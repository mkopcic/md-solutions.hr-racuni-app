<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                <i class="fas fa-book"></i>
                Knjiga prometa
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">Evidencija prometa i praćenje prihoda</p>
        </div>
        <button wire:click="generateKprEntries" class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
            <i class="fas fa-sync-alt"></i>
            Generiraj iz računa
        </button>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
            <div class="grid w-full max-w-xl grid-cols-2 gap-4">
                <div>
                    <label for="month" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Mjesec</label>
                    <select wire:model.live="month" id="month" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @foreach($months as $key => $monthName)
                            <option value="{{ $key }}">{{ $monthName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="year" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Godina</label>
                    <select wire:model.live="year" id="year" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid w-full grid-cols-2 gap-4 md:max-w-xs">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                        <i class="fas fa-calendar"></i>
                        Mjesečni prihod
                    </h3>
                    <p class="text-xl font-bold text-blue-900 dark:text-blue-200">{{ number_format($totalMonthlyAmount, 2, ',', '.') }} €</p>
                </div>
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">
                        <i class="fas fa-chart-line"></i>
                        Godišnji prihod
                    </h3>
                    <p class="text-xl font-bold text-green-900 dark:text-green-200">{{ number_format($totalYearlyAmount, 2, ',', '.') }} €</p>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
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
</div>
