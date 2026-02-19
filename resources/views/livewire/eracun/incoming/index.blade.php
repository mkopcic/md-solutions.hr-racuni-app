<div>
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Ulazni e-Računi</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Računi primljeni od dobavljača</p>
        </div>
        <div class="flex w-full justify-center gap-2 sm:w-auto">
            <a href="{{ route('eracun.logs.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 sm:w-auto dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                <i class="fas fa-list"></i>
                Logovi
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistika -->
    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-7">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ukupno</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Primljeno</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['received'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Čeka pregled</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending_review'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Odobreno</p>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Odbijeno</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Plaćeno</p>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $stats['paid'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Za uplatu</p>
            <p class="mt-2 text-lg font-bold text-amber-600">{{ number_format($stats['unpaidAmount'], 2, ',', '.') }} €</p>
        </div>
    </div>

    <!-- Filteri -->
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4">
            <label for="search" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pretraži</label>
            <input type="text" wire:model.live="search" id="search" placeholder="Pretraži po dobavljaču, OIB-u ili broju računa" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400">
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <select wire:model.live="status" id="status" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                    <option value="">Svi statusi</option>
                    @foreach(\App\Enums\IncomingInvoiceStatus::cases() as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="dateFrom" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Od datuma</label>
                <input type="date" wire:model.live="dateFrom" id="dateFrom" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
            </div>

            <div>
                <label for="dateTo" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Do datuma</label>
                <input type="date" wire:model.live="dateTo" id="dateTo" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="resetFilters" type="button" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <i class="fas fa-times"></i>
                Poništi filtere
            </button>
        </div>
    </div>

    <!-- Tablica -->
    <div class="hidden overflow-hidden rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Broj računa</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Dobavljač</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Datum izdavanja</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Dospijeće</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Iznos</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Akcije</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($invoices as $invoice)
                    <tr class="@if($invoice->isOverdue()) bg-red-50 dark:bg-red-900/10 @endif">
                        <td class="whitespace-nowrap px-6 py-4">
                            <a href="{{ route('eracun.incoming.show', $invoice) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400" wire:navigate>
                                {{ $invoice->invoice_number }}
                            </a>
                            @if($invoice->isOverdue())
                                <span class="ml-2 text-xs text-red-600"><i class="fas fa-exclamation-triangle"></i> Kasni</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-zinc-900 dark:text-white">{{ $invoice->supplier_name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->supplier_oib }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $invoice->issue_date->format('d.m.Y') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $invoice->due_date->format('d.m.Y') }}
                            @php
                                $daysUntil = $invoice->daysUntilDue();
                            @endphp
                            @if($daysUntil < 0)
                                <span class="text-xs text-red-600">({{ abs($daysUntil) }} dana kašnjenja)</span>
                            @elseif($daysUntil <= 7)
                                <span class="text-xs text-amber-600">({{ $daysUntil }} dana)</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ number_format($invoice->total_amount, 2, ',', '.') }} €
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $invoice->status->badge() }}-100 text-{{ $invoice->status->badge() }}-800">
                                {{ $invoice->status->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('eracun.incoming.show', $invoice) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Detalji" wire:navigate>
                                    <i class="fas fa-eye fa-lg"></i>
                                </a>

                                @if($invoice->status === \App\Enums\IncomingInvoiceStatus::PENDING_REVIEW || $invoice->status === \App\Enums\IncomingInvoiceStatus::RECEIVED)
                                    <button wire:click="quickApprove({{ $invoice->id }})" wire:confirm="Želite li odobriti ovaj račun?" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Odobri">
                                        <i class="fas fa-check fa-lg"></i>
                                    </button>
                                    <button wire:click="quickReject({{ $invoice->id }})" wire:confirm="Želite li odbiti ovaj račun?" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Odbij">
                                        <i class="fas fa-times fa-lg"></i>
                                    </button>
                                @endif

                                @if($invoice->status === \App\Enums\IncomingInvoiceStatus::APPROVED)
                                    <button wire:click="markAsPaid({{ $invoice->id }})" wire:confirm="Želite li označiti ovaj račun kao plaćen?" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors" title="Označi kao plaćeno">
                                        <i class="fas fa-euro-sign fa-lg"></i>
                                    </button>
                                @endif

                                @if($invoice->ubl_xml)
                                    <button wire:click="viewXml({{ $invoice->id }})" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 transition-colors" title="Prikaži XML">
                                        <i class="fas fa-code fa-lg"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Nema primljenih e-računa
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="space-y-4 md:hidden">
        @forelse ($invoices as $invoice)
            <div class="rounded-lg border @if($invoice->isOverdue()) border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/10 @else border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 @endif p-4">
                <!-- Header -->
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex-1">
                        <a href="{{ route('eracun.incoming.show', $invoice) }}" class="text-base font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400" wire:navigate>
                            {{ $invoice->invoice_number }}
                        </a>
                        @if($invoice->isOverdue())
                            <span class="ml-2 inline-flex items-center gap-1 text-xs font-medium text-red-600">
                                <i class="fas fa-exclamation-triangle"></i>
                                Kasni
                            </span>
                        @endif
                    </div>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $invoice->status->badge() }}-100 text-{{ $invoice->status->badge() }}-800">
                        {{ $invoice->status->label() }}
                    </span>
                </div>

                <!-- Supplier -->
                <div class="mb-3">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $invoice->supplier_name }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->supplier_oib }}</p>
                </div>

                <!-- Info Grid -->
                <div class="mb-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Iznos</p>
                        <p class="font-semibold text-zinc-900 dark:text-white">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                    </div>
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Datum izdavanja</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $invoice->issue_date->format('d.m.Y') }}</p>
                    </div>
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Dospijeće</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $invoice->due_date->format('d.m.Y') }}</p>
                        @php
                            $daysUntil = $invoice->daysUntilDue();
                        @endphp
                        @if($daysUntil < 0)
                            <p class="text-xs text-red-600">({{ abs($daysUntil) }} dana kašnjenja)</p>
                        @elseif($daysUntil <= 7)
                            <p class="text-xs text-amber-600">({{ $daysUntil }} dana)</p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                    <a href="{{ route('eracun.incoming.show', $invoice) }}" class="inline-flex items-center gap-1 rounded bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400" wire:navigate>
                        <i class="fas fa-eye"></i>
                        Detalji
                    </a>

                    @if($invoice->status === \App\Enums\IncomingInvoiceStatus::PENDING_REVIEW || $invoice->status === \App\Enums\IncomingInvoiceStatus::RECEIVED)
                        <button wire:click="quickApprove({{ $invoice->id }})" wire:confirm="Želite li odobriti ovaj račun?" class="inline-flex items-center gap-1 rounded bg-green-100 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400">
                            <i class="fas fa-check"></i>
                            Odobri
                        </button>
                        <button wire:click="quickReject({{ $invoice->id }})" wire:confirm="Želite li odbiti ovaj račun?" class="inline-flex items-center gap-1 rounded bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400">
                            <i class="fas fa-times"></i>
                            Odbij
                        </button>
                    @endif

                    @if($invoice->status === \App\Enums\IncomingInvoiceStatus::APPROVED)
                        <button wire:click="markAsPaid({{ $invoice->id }})" wire:confirm="Želite li označiti ovaj račun kao plaćen?" class="inline-flex items-center gap-1 rounded bg-green-100 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400">
                            <i class="fas fa-euro-sign"></i>
                            Označi plaćeno
                        </button>
                    @endif

                    @if($invoice->ubl_xml)
                        <button wire:click="viewXml({{ $invoice->id }})" class="inline-flex items-center gap-1.5 rounded bg-purple-100 px-3 py-1.5 text-xs font-medium text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:hover:bg-purple-900/50 transition-colors">
                            <i class="fas fa-code"></i>
                            Prikaži XML
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Nema primljenih e-računa</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $invoices->links() }}
    </div>

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
