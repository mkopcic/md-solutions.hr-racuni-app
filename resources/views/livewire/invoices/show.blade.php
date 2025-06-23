<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Račun #{{ $invoice->id }}
                @if($invoice->isPaid())
                    <span class="ml-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">Plaćeno</span>
                @elseif($invoice->isOverdue())
                    <span class="ml-2 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">Dospjelo</span>
                @else
                    <span class="ml-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">Neplaćeno</span>
                @endif
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                Pregled računa i upravljanje
            </p>
        </div>
        <div class="flex gap-2">
            <button wire:click="generatePdf" class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Preuzmi PDF
            </button>
            <a href="{{ route('invoices.index') }}" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Nazad na račune
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-3">
        <!-- Lijevi dio: Podaci o računu -->
        <div class="space-y-6 md:col-span-2">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">Osnovni podaci</h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Kupac</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">{{ $invoice->customer->name }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">OIB: {{ $invoice->customer->oib }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->customer->address }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->customer->city }}</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum izdavanja</h3>
                            <p class="text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum isporuke</h3>
                            <p class="text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($invoice->delivery_date)->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum dospijeća</h3>
                            <p class="text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">Stavke računa</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Naziv</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Količina</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Cijena (€)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Popust (%)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ukupno (€)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="whitespace-normal px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $item->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-zinc-700 dark:text-zinc-300">{{ $item->quantity }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-300">{{ number_format($item->price, 2, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $item->discount > 0 ? number_format($item->discount, 2, ',', '.') . '%' : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">{{ number_format($item->total, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">UKUPNO</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-base font-bold text-zinc-900 dark:text-white">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($invoice->note)
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Napomena</h3>
                        <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $invoice->note }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Desni dio: Status i akcije -->
        <div class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">Status</h2>

                <div class="mb-6">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium
                        @if($invoice->status === 'paid')
                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                        @elseif($invoice->status === 'partial')
                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                        @else
                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                        @endif">
                        @if($invoice->status === 'paid')
                            Plaćeno
                        @elseif($invoice->status === 'partial')
                            Djelomično plaćeno
                        @else
                            Neplaćeno
                        @endif
                    </span>

                    @if($invoice->status === 'paid' || $invoice->status === 'partial')
                        <div class="mt-2">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Plaćeno: {{ number_format(($invoice->paid_cash + $invoice->paid_transfer), 2, ',', '.') }} € od
                                {{ number_format($invoice->total_amount, 2, ',', '.') }} €
                            </p>
                            <div class="mt-1 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div class="h-2 rounded-full bg-green-500" style="width: {{ min(100, (($invoice->paid_cash + $invoice->paid_transfer) / $invoice->total_amount) * 100) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($invoice->status !== 'paid')
                    <div x-data="{ showPaymentForm: false, paymentType: 'cash', paymentAmount: '' }">
                        <button @click="showPaymentForm = !showPaymentForm" type="button" class="mb-4 flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700">
                            Evidentiraj plaćanje
                        </button>

                        <div x-show="showPaymentForm" class="mt-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Evidencija plaćanja</h3>

                            <div class="mb-3">
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Način plaćanja</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" x-model="paymentType" value="cash" class="h-4 w-4 text-blue-600">
                                        <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Gotovina</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" x-model="paymentType" value="transfer" class="h-4 w-4 text-blue-600">
                                        <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Transakcija</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Iznos (€)</label>
                                <input type="number" step="0.01" x-model="paymentAmount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                <small class="mt-1 text-xs text-zinc-500">Preostalo: {{ number_format($invoice->total_amount - ($invoice->paid_cash + $invoice->paid_transfer), 2, ',', '.') }} €</small>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button @click="showPaymentForm = false" type="button" class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                    Odustani
                                </button>
                                <button @click="$wire.markAsPaid(paymentType, paymentAmount); showPaymentForm = false; paymentAmount = ''" type="button" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">
                                    Evidentiraj
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Ukupan iznos</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Plaćeno gotovinom</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->paid_cash, 2, ',', '.') }} €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Plaćeno transakcijom</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->paid_transfer, 2, ',', '.') }} €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Preostali iznos</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->getRemainingAmount(), 2, ',', '.') }} €</span>
                    </div>
                </div>

                <!-- KPR Zapis -->
                <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-zinc-900 dark:text-white">Knjiga prometa (KPR)</h3>

                        @if($invoice->kprEntry)
                            <div class="flex items-center">
                                <span class="mr-2 text-sm text-green-600">✓ Evidentirano u KPR</span>
                                <a href="{{ route('kpr.index') }}" class="rounded-lg bg-zinc-100 px-3 py-1 text-sm text-zinc-800 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700" wire:navigate>
                                    Pregledaj KPR
                                </a>
                            </div>
                        @else
                            <button wire:click="createKprEntry" class="rounded-lg bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700">
                                Evidentiraj u KPR
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
