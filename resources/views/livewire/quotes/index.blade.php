<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Ponude</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Pregled i upravljanje ponudima</p>
        </div>
            <div>
                <a href="{{ route('quotes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700" wire:navigate>
                    <i class="fas fa-plus"></i>
                    Nova ponuda
                </a>
            </div>
        </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    <!-- Statistika ponuda -->
    <div class="mb-6 grid gap-4 md:grid-cols-5">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ukupno</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">Poslane</p>
            <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['sent'] }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-green-700 dark:text-green-400">Prihvaćene</p>
            <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['accepted'] }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-red-700 dark:text-red-400">Odbijene</p>
            <p class="mt-1 text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['rejected'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Istekle</p>
            <p class="mt-1 text-2xl font-bold text-zinc-400 dark:text-zinc-500">{{ $stats['expired'] }}</p>
        </div>
    </div>

    <!-- Export Radnje -->
    <div class="mb-4 flex items-center gap-2">
        <button wire:click="exportExcel" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-excel text-green-600"></i>
            Excel
        </button>
        <button wire:click="exportCsv" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-csv text-orange-600"></i>
            CSV
        </button>
    </div>

    <!-- Filteri za pretragu -->
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <!-- Glavni search -->
        <div class="mb-4">
            <label for="search" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pretraži</label>
            <input type="text" wire:model.live="search" id="search" placeholder="Pretraži po kupcu ili OIB-u" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
        </div>

        <!-- Ostali filteri -->
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label for="dateFrom" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Od datuma</label>
                <input type="date" wire:model.live="dateFrom" id="dateFrom" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
            </div>
            <div>
                <label for="dateTo" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Do datuma</label>
                <input type="date" wire:model.live="dateTo" id="dateTo" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
            </div>

            <div>
                <label for="year" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Godina</label>
                <select wire:model.live="year" id="year" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <option value="">Sve godine</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="month" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Mjesec</label>
                <select wire:model.live="month" id="month" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <option value="">Svi mjeseci</option>
                    <option value="1">Siječanj</option>
                    <option value="2">Veljača</option>
                    <option value="3">Ožujak</option>
                    <option value="4">Travanj</option>
                    <option value="5">Svibanj</option>
                    <option value="6">Lipanj</option>
                    <option value="7">Srpanj</option>
                    <option value="8">Kolovoz</option>
                    <option value="9">Rujan</option>
                    <option value="10">Listopad</option>
                    <option value="11">Studeni</option>
                    <option value="12">Prosinac</option>
                </select>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label for="customer_id" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Kupac</label>
                <select wire:model.live="customer_id" id="customer_id" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <option value="">Svi kupci</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <select wire:model.live="status" id="status" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <option value="">Svi statusi</option>
                    <option value="paid">Plaćeni</option>
                    <option value="unpaid">Neplaćeni</option>
                    <option value="overdue">Dospjeli i neplaćeni</option>
                </select>
            </div>

            <div>
                <label for="paymentMethod" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Način plaćanja</label>
                <select wire:model.live="paymentMethod" id="paymentMethod" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <option value="">Svi načini</option>
                    <option value="gotovina">Gotovina</option>
                    <option value="virman">Virman</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button
                wire:click="resetFilters"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Poništi filtere
            </button>
        </div>
    </div>

    <!-- Tablica���ponuda - Desktop -->
    <div class="hidden xl:block overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Broj ponuda</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tip</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Kupac</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Datum izdavanja</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vrijedi do</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Iznos</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Akcije</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($quotes as $quote)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $quote->id }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $quote->quote_number }}/{{ $quote->quote_year }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            @php
                                $typeMap = [
                                    'R' => 'Ponuda',
                                    'RA' => 'Avansna',
                                    'P' => 'Predponuda',
                                    'regular' => 'Ponuda'
                                ];
                                $typeName = $typeMap[$quote->quote_type] ?? $quote->quote_type;
                            @endphp
                            <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $typeName }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $quote->customer->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $quote->formatDate($quote->issue_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $quote->formatDate($quote->due_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ number_format($quote->total_amount, 2, ',', '.') }} €
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold leading-5
                                @if($quote->isAccepted()) bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                @elseif($quote->isRejected()) bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                @elseif($quote->isExpired()) bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400
                                @elseif($quote->isSent()) bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                @elseif($quote->isConverted()) bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400
                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400
                                @endif">
                                @if($quote->isAccepted())
                                    Prihvaćena
                                @elseif($quote->isRejected())
                                    Odbijena
                                @elseif($quote->isExpired())
                                    Istekla
                                @elseif($quote->isSent())
                                    Poslana
                                @elseif($quote->isConverted())
                                    Pretvorena
                                @else
                                    Nacrt
                                @endif
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('quotes.show', $quote->id) }}" class="mr-2 text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 inline-flex items-center gap-1" wire:navigate>
                                <i class="fas fa-eye"></i>
                                Pregled
                            </a>
                            <button wire:click="sendPdfEmail({{ $quote->id }})" wire:loading.attr="disabled" wire:target="sendPdfEmail({{ $quote->id }})" class="mr-2 text-teal-600 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-300 inline-flex items-center gap-1 disabled:opacity-50" title="Pošalji PDF na email">
                                <i class="fas fa-envelope" wire:loading.remove wire:target="sendPdfEmail({{ $quote->id }})"></i>
                                <i class="fas fa-spinner fa-spin" wire:loading wire:target="sendPdfEmail({{ $quote->id }})"></i>
                                <span class="hidden lg:inline">Mail</span>
                            </button>
                            <button wire:click="delete({{ $quote->id }})" wire:confirm="Jeste li sigurni da želite obrisati ovu ponudu?" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 inline-flex items-center gap-1">
                                <i class="fas fa-trash"></i>
                                Obriši
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Nema pronađenih ponuda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobilni prikaz kartica -->
    <div class="xl:hidden space-y-4">
        @forelse ($quotes as $quote)
            @php
                $typeMap = [
                    'R' => 'Ponuda',
                    'RA' => 'Avansna',
                    'P' => 'Predponuda',
                    'regular' => 'Ponuda'
                ];
                $typeName = $typeMap[$quote->quote_type] ?? $quote->quote_type;
            @endphp
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                #{{ $quote->quote_number }}/{{ $quote->quote_year }}
                            </h3>
                            <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $typeName }}
                            </span>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $quote->customer->name }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold leading-5
                        @if($quote->isAccepted()) bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                        @elseif($quote->isRejected()) bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                        @elseif($quote->isExpired()) bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400
                        @elseif($quote->isSent()) bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                        @elseif($quote->isConverted()) bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400
                        @else bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400
                        @endif">
                        @if($quote->isAccepted())
                            Prihvaćena
                        @elseif($quote->isRejected())
                            Odbijena
                        @elseif($quote->isExpired())
                            Istekla
                        @elseif($quote->isSent())
                            Poslana
                        @elseif($quote->isConverted())
                            Pretvorena
                        @else
                            Nacrt
                        @endif
                    </span>
                </div>
                <div class="space-y-2 text-sm border-t border-zinc-200 dark:border-zinc-700 pt-3">
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Izdano:</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $quote->formatDate($quote->issue_date) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Vrijedi do:</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $quote->formatDate($quote->valid_until) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Iznos:</span>
                        <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ number_format($quote->total_amount, 2, ',', '.') }} €</span>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('quotes.show', $quote->id) }}" wire:navigate
                        class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg border border-blue-600 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30">
                        <i class="fas fa-eye"></i>
                        Pregled
                    </a>
                    <button wire:click="sendPdfEmail({{ $quote->id }})" wire:loading.attr="disabled" wire:target="sendPdfEmail({{ $quote->id }})"
                        class="inline-flex items-center justify-center gap-1 rounded-lg border border-teal-600 bg-teal-50 px-3 py-2 text-sm font-medium text-teal-600 hover:bg-teal-100 dark:border-teal-500 dark:bg-teal-900/20 dark:text-teal-400 dark:hover:bg-teal-900/30 disabled:opacity-50"
                        title="Pošalji PDF na email">
                        <i class="fas fa-envelope" wire:loading.remove wire:target="sendPdfEmail({{ $quote->id }})"></i>
                        <i class="fas fa-spinner fa-spin" wire:loading wire:target="sendPdfEmail({{ $quote->id }})"></i>
                    </button>
                    <button wire:click="delete({{ $quote->id }})"
                        wire:confirm="Jeste li sigurni da želite obrisati ovu ponudu?"
                        class="inline-flex items-center justify-center gap-1 rounded-lg border border-red-600 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <i class="fas fa-file-quote text-4xl text-zinc-300 dark:text-zinc-600 mb-3"></i>
                <p class="text-zinc-500 dark:text-zinc-400">Nema pronađenih ponuda.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $quotes->links() }}
    </div>
</div>
