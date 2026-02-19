<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Račun #{{ $invoice->full_invoice_number ?? $invoice->id }}
                @if ($invoice->isPaid())
                    <span
                        class="ml-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">Plaćeno</span>
                @elseif($invoice->isOverdue())
                    <span class="ml-2 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">Dospjelo</span>
                @else
                    <span
                        class="ml-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">Neplaćeno</span>
                @endif
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                Tip: <strong>{{ $invoice->invoice_type ?? 'N/A' }}</strong> | Plaćanje: <strong>{{ ucfirst($invoice->payment_method ?? 'N/A') }}</strong>
            </p>

            <!-- e-Račun Status Badge -->
            <div class="mt-2">
                @if ($invoice->latestEracunLog)
                    @php
                        $eLog = $invoice->latestEracunLog;
                        $eStatus = $eLog->status;
                    @endphp

                    @if (in_array($eStatus, [\App\Enums\EracunStatus::SENT, \App\Enums\EracunStatus::ACCEPTED]))
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-green-100 px-3 py-1.5 text-sm font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            <i class="fas fa-paper-plane"></i>
                            FINA e-Račun
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                        </span>
                    @elseif (in_array($eStatus, [\App\Enums\EracunStatus::PENDING, \App\Enums\EracunStatus::SENDING]))
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                            <i class="fas fa-paper-plane"></i>
                            e-Račun
                            <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                            <span class="text-xs">({{ $eStatus->label() }})</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-3 py-1.5 text-sm font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            <i class="fas fa-paper-plane"></i>
                            e-Račun
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                            <span class="text-xs">({{ $eStatus->label() }})</span>
                        </span>
                    @endif
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <i class="fas fa-file-invoice"></i>
                        Obični račun
                    </span>
                @endif
            </div>
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route('invoices.show.pdf', $invoice) }}" target="_blank"
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

            @php
                $totalPaid = $invoice->paid_cash + $invoice->paid_transfer;
                $isMathPaid = $totalPaid >= $invoice->total_amount;
            @endphp

            @if ($isMathPaid && !$invoice->isPaid())
                <button wire:click="syncStatus" wire:confirm="Želite li označiti ovaj račun kao plaćen?"
                    class="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700 sm:w-auto">
                    <i class="fas fa-check-circle"></i>
                    Označi kao plaćeno
                </button>
            @endif

            @if (!$invoice->isPaid())
                <button onclick="document.getElementById('payment-dialog').showModal()"
                    class="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-orange-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-orange-700 sm:w-auto">
                    <i class="fas fa-euro-sign"></i>
                    Evidentiraj plaćanje
                </button>
            @endif
            <a href="{{ route('invoices.index') }}"
                class="inline-flex w-full items-center justify-center gap-1 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-100 sm:w-auto dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                wire:navigate>
                <i class="fas fa-arrow-left"></i>
                Nazad na račune
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
        <!-- Lijevi dio: Podaci o računu -->
        <div class="space-y-6 md:col-span-2">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold text-zinc-900 dark:text-white">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    Osnovni podaci
                </h2>

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
                            <p class="text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum isporuke</h3>
                            <p class="text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($invoice->delivery_date)->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum dospijeća</h3>
                            <p class="text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold text-zinc-900 dark:text-white">
                    <i class="fas fa-list text-blue-600"></i>
                    Stavke računa
                </h2>

                <!-- Desktop Table -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Naziv</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Količina</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Cijena (€)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Popust (%)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Ukupno (€)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="whitespace-normal px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                        {{ $item->name }}</td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-center text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $item->quantity }}</td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ number_format($item->price, 2, ',', '.') }}</td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $item->discount > 0 ? number_format($item->discount, 2, ',', '.') . '%' : '-' }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right font-medium text-zinc-900 dark:text-white">
                                        {{ number_format($item->total, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td colspan="4"
                                    class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    UKUPNO</td>
                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right text-base font-bold text-zinc-900 dark:text-white">
                                    {{ number_format($invoice->total_amount, 2, ',', '.') }} €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="space-y-3 md:hidden">
                    @foreach ($invoice->items as $item)
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="mb-2">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $item->name }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Količina</p>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $item->quantity }}</p>
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
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                        </div>
                    </div>
                </div>

                @if ($invoice->note)
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
                    <span
                        class="inline-flex rounded-full px-3 py-1 text-sm font-medium
                        @if ($invoice->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                        @elseif($invoice->status === 'partial')
                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                        @else
                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                        @if ($invoice->status === 'paid')
                            Plaćeno
                        @elseif($invoice->status === 'partial')
                            Djelomično plaćeno
                        @else
                            Neplaćeno
                        @endif
                    </span>

                    @if ($invoice->status === 'paid' || $invoice->status === 'partial')
                        <div class="mt-2">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Plaćeno:
                                {{ number_format($invoice->paid_cash + $invoice->paid_transfer, 2, ',', '.') }} € od
                                {{ number_format($invoice->total_amount, 2, ',', '.') }} €
                            </p>
                            <div class="mt-1 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div class="h-2 rounded-full bg-green-500"
                                    style="width: {{ min(100, (($invoice->paid_cash + $invoice->paid_transfer) / $invoice->total_amount) * 100) }}%">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($invoice->status !== 'paid')
                    <button onclick="document.getElementById('payment-dialog').showModal()" type="button"
                        class="mb-4 flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fas fa-euro-sign"></i>
                        Evidentiraj plaćanje
                    </button>
                @endif

                <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Ukupan iznos</span>
                        <span
                            class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->total_amount, 2, ',', '.') }}
                            €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Plaćeno gotovinom</span>
                        <span
                            class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->paid_cash, 2, ',', '.') }}
                            €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Plaćeno transakcijom</span>
                        <span
                            class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->paid_transfer, 2, ',', '.') }}
                            €</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Preostali iznos</span>
                        <span
                            class="font-medium text-zinc-900 dark:text-white">{{ number_format($invoice->getRemainingAmount(), 2, ',', '.') }}
                            €</span>
                    </div>
                </div>

                <!-- KPR Zapis -->
                <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 font-medium text-zinc-900 dark:text-white">
                            <i class="fas fa-book text-blue-600"></i>
                            Knjiga prometa (KPR)
                        </h3>

                        @if ($invoice->kprEntry)
                            <div class="flex items-center">
                                <span class="mr-2 text-sm text-green-600"><i class="fas fa-check-circle"></i> Evidentirano</span>
                                <a href="{{ route('kpr.index') }}"
                                    class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-3 py-1 text-sm text-zinc-800 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700"
                                    wire:navigate>
                                    <i class="fas fa-eye"></i>
                                    Pregledaj KPR
                                </a>
                            </div>
                        @else
                            <button wire:click="createKprEntry"
                                class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700">
                                <i class="fas fa-plus-circle"></i>
                                Evidentiraj u KPR
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- e-Račun Status -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">e-Račun Status</h2>

                @if ($invoice->latestEracunLog)
                    @php
                        $log = $invoice->latestEracunLog;
                    @endphp
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Status slanja:</span>
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->status->badge() }}-100 text-{{ $log->status->badge() }}-800">
                                {{ $log->status->label() }}
                            </span>
                        </div>

                        @if ($log->fina_status)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">FINA Status:</span>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->fina_status->badge() }}-100 text-{{ $log->fina_status->badge() }}-800">
                                    {{ $log->fina_status->label() }}
                                </span>
                            </div>
                        @endif

                        @if ($log->sent_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Poslano:</span>
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $log->sent_at->format('d.m.Y H:i') }}</span>
                            </div>
                        @endif

                        @if ($log->error_message)
                            <div class="rounded bg-red-50 p-3 dark:bg-red-900/10">
                                <p class="text-xs text-red-600 dark:text-red-400">{{ $log->error_message }}</p>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-col gap-2">
                            @if ($log->fina_invoice_id)
                                <button wire:click="checkEracunStatus" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                    <i class="fas fa-sync"></i> Provjeri status
                                </button>
                            @endif

                            @if ($log->ubl_xml)
                                <button wire:click="viewEracunXml('ubl')" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                    <i class="fas fa-code"></i> Prikaži XML
                                </button>
                            @endif

                            <a href="{{ route('eracun.outgoing.index') }}" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                                <i class="fas fa-list"></i> Svi e-Računi
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">Račun još nije poslan na FINA e-Račun sustav</p>
                        <button wire:click="sendToEracun" wire:confirm="Želite li poslati ovaj račun na FINA e-Račun sustav?" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <i class="fas fa-paper-plane"></i> Pošalji e-Račun
                        </button>
                        <a href="{{ route('eracun.outgoing.index') }}" class="mt-2 block text-sm text-blue-600 hover:text-blue-700" wire:navigate>
                            Pregled svih poslanih e-računa
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Dialog -->
    <dialog id="payment-dialog"
        class="rounded-lg border-0 bg-white p-6 shadow-xl dark:bg-zinc-900 dark:text-white backdrop:bg-black backdrop:bg-opacity-50">
        <div class="min-w-96">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Evidentiraj plaćanje</h3>
                <button onclick="document.getElementById('payment-dialog').close()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="payment-form">
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Način
                        plaćanja</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="paymentType" value="cash" checked
                                class="h-4 w-4 text-blue-600">
                            <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Gotovina</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="paymentType" value="transfer" class="h-4 w-4 text-blue-600">
                            <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Transakcija</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Iznos (€)</label>
                    <input type="number" step="0.01" name="paymentAmount"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        required>
                    <small class="mt-1 text-xs text-zinc-500">Preostalo:
                        {{ number_format($invoice->total_amount - ($invoice->paid_cash + $invoice->paid_transfer), 2, ',', '.') }}
                        €</small>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('payment-dialog').close()"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="fas fa-times"></i>
                        Odustani
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                        <i class="fas fa-check"></i>
                        Evidentiraj
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const paymentType = formData.get('paymentType');
            const paymentAmount = parseFloat(formData.get('paymentAmount'));

            if (paymentAmount > 0) {
                @this.markAsPaid(paymentType, paymentAmount);
                document.getElementById('payment-dialog').close();
                this.reset();
            }
        });
    </script>

    <!-- XML Modal -->
    <div x-data="{ open: false, xml: '', title: '' }"
         x-on:show-xml-modal.window="open = true; xml = $event.detail.xml; title = $event.detail.title"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="open = false"></div>
            <div class="relative z-50 w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white" x-text="title"></h3>
                    <button @click="open = false" class="text-zinc-400 hover:text-zinc-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="max-h-96 overflow-auto rounded bg-zinc-50 p-4 dark:bg-zinc-900">
                    <pre class="text-xs text-zinc-900 dark:text-white"><code x-text="xml"></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
