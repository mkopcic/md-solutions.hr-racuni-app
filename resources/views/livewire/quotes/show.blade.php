<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Ponuda #{{ $quote->full_quote_number ?? $quote->id }}
                @if ($quote->isDraft())
                    <span class="ml-2 rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-300">Nacrt</span>
                @elseif($quote->isSent())
                    <span class="ml-2 rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">Poslana</span>
                @elseif($quote->isAccepted())
                    <span class="ml-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-300">Prihvaćena</span>
                @elseif($quote->isRejected())
                    <span class="ml-2 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800 dark:bg-red-900 dark:text-red-300">Odbijena</span>
                @elseif($quote->isExpired())
                    <span class="ml-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-300">Istekla</span>
                @endif
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                Tip: <strong>{{ $quote->quote_type ?? 'N/A' }}</strong> | Plaćanje: <strong>{{ ucfirst($quote->payment_method ?? 'N/A') }}</strong>
            </p>
        </div>
        
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route('quotes.show.pdf', $quote) }}" target="_blank"
                class="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-green-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-green-700 sm:w-auto">
                <i class="fas fa-eye"></i>
                Prikaži PDF
            </a>

            <button wire:click="sendPdfEmail" wire:loading.attr="disabled"
                class="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 sm:w-auto">
                <i class="fas fa-envelope" wire:loading.remove wire:target="sendPdfEmail"></i>
                <i class="fas fa-spinner fa-spin" wire:loading wire:target="sendPdfEmail"></i>
                <span wire:loading.remove wire:target="sendPdfEmail">Pošalji na mail</span>
                <span wire:loading wire:target="sendPdfEmail">Šaljem...</span>
            </button>

            <a href="{{ route('quotes.index') }}"
                class="inline-flex w-full items-center justify-center gap-1 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-100 sm:w-auto dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                wire:navigate>
                <i class="fas fa-arrow-left"></i>
                Nazad
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-3">
        <!-- Lijevi dio: Podaci o ponudi -->
        <div class="space-y-6 md:col-span-2">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold text-zinc-900 dark:text-white">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    Osnovni podaci
                </h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Kupac</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">{{ $quote->customer->name }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">OIB: {{ $quote->customer->oib }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $quote->customer->address }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $quote->customer->city }}</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum izdavanja</h3>
                            <p class="text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($quote->issue_date)->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum isporuke</h3>
                            <p class="text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($quote->delivery_date)->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Vrijedi do</h3>
                            <p class="text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($quote->valid_until)->format('d.m.Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold text-zinc-900 dark:text-white">
                    <i class="fas fa-list text-blue-600"></i>
                    Stavke ponude
                </h2>

                <!-- Desktop Table -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Naziv</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Količina</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Cijena (€)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Popust (%)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Ukupno (€)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @foreach ($quote->items as $item)
                                <tr>
                                    <td class="whitespace-normal px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                        {{ $item->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $item->quantity }} {{ $item->unit }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ number_format($item->price, 2, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $item->discount > 0 ? number_format($item->discount, 2, ',', '.') . '%' : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">
                                        {{ number_format($item->total, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    UKUPNO</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-base font-bold text-zinc-900 dark:text-white">
                                    {{ number_format($quote->total_amount, 2, ',', '.') }} €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="space-y-3 md:hidden">
                    @foreach ($quote->items as $item)
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="mb-2">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $item->name }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Količina</p>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $item->quantity }} {{ $item->unit }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Cijena</p>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ number_format($item->price, 2, ',', '.') }} €</p>
                                </div>
                                @if($item->discount > 0)
                                    <div>
                                        <p class="text-zinc-500 dark:text-zinc-400">Popust</p>
                                        <p class="font-medium text-amber-600">{{ number_format($item->discount, 2, ',', '.') }}%</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Ukupno</p>
                                    <p class="text-lg font-bold text-zinc-900 dark:text-white">{{ number_format($item->total, 2, ',', '.') }} €</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Mobile Total -->
                    <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <div class="flex items-center justify-between">
                            <p class="text-base font-bold text-zinc-900 dark:text-white">UKUPNO</p>
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($quote->total_amount, 2, ',', '.') }} €</p>
                        </div>
                    </div>
                </div>

                @if ($quote->note)
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Napomena</h3>
                        <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $quote->note }}</p>
                    </div>
                @endif

                @if ($quote->internal_notes)
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Interne napomene</h3>
                        <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $quote->internal_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Desni dio: Status i akcije -->
        <div class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">Status ponude</h2>

                <div class="mb-6">
                    @if ($quote->isDraft())
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                            <i class="fas fa-file-alt mr-2"></i> Nacrt
                        </span>
                    @elseif($quote->isSent())
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            <i class="fas fa-paper-plane mr-2"></i> Poslana
                        </span>
                    @elseif($quote->isAccepted())
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1.5 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                            <i class="fas fa-check-circle mr-2"></i> Prihvaćena
                        </span>
                    @elseif($quote->isRejected())
                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1.5 text-sm font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                            <i class="fas fa-times-circle mr-2"></i> Odbijena
                        </span>
                    @elseif($quote->isExpired())
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-300">
                            <i class="fas fa-clock mr-2"></i> Istekla
                        </span>
                    @endif
                </div>

                <div class="space-y-2">
                    @if ($quote->isDraft())
                        <button wire:click="updateStatus('sent')" wire:confirm="Želite li označiti ponudu kao poslanu?"
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <i class="fas fa-paper-plane"></i> Označi kao poslanu
                        </button>
                    @endif

                    @if ($quote->isSent() && !$quote->isExpired())
                        <button wire:click="updateStatus('accepted')" wire:confirm="Želite li označiti ponudu kao prihvaćenu?"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            <i class="fas fa-check-circle"></i> Označi kao prihvaćenu
                        </button>
                        
                        <button wire:click="updateStatus('rejected')" wire:confirm="Želite li označiti ponudu kao odbijenu?"
                            class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            <i class="fas fa-times-circle"></i> Označi kao odbijenu
                        </button>
                    @endif

                    @if ($quote->isAccepted() && !$quote->isConverted())
                        <button wire:click="convertToInvoice" wire:confirm="Želite li konvertirati ponudu u račun?"
                            class="w-full rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                            <i class="fas fa-file-invoice"></i> Konvertiraj u račun
                        </button>
                    @endif
                </div>

                <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Ukupan iznos</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($quote->total_amount, 2, ',', '.') }} €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Status</span>
                        <span class="font-medium text-zinc-900 dark:text-white">
                            @if ($quote->isDraft()) Nacrt
                            @elseif($quote->isSent()) Poslana
                            @elseif($quote->isAccepted()) Prihvaćena
                            @elseif($quote->isRejected()) Odbijena
                            @elseif($quote->isExpired()) Istekla
                            @endif
                        </span>
                    </div>

                    @if ($quote->isConverted())
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Konvertirana u račun</span>
                            <a href="{{ route('invoices.show', $quote->convertedToInvoice) }}" 
                                class="text-sm font-medium text-blue-600 hover:text-blue-700" wire:navigate>
                                #{{ $quote->convertedToInvoice->full_invoice_number }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
