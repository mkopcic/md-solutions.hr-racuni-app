<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Izlazni e-Računi</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Pregled poslanih računa na FINA e-Račun sustav</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('eracun.logs.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                <i class="fas fa-list"></i>
                Logovi
            </a>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" wire:navigate>
                <i class="fas fa-arrow-left"></i>
                Natrag na račune
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
    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ukupno</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Čeka slanje</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Poslano</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['sent'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Prihvaćeno</p>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $stats['accepted'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Odbijeno</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Greške</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
        </div>
    </div>

    <!-- Filteri -->
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4">
            <label for="search" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pretraži</label>
            <input type="text" wire:model.live="search" id="search" placeholder="Pretraži po kupcu ili OIB-u" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400">
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <select wire:model.live="status" id="status" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                    <option value="">Svi statusi</option>
                    @foreach(\App\Enums\EracunStatus::cases() as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="finaStatus" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">FINA Status</label>
                <select wire:model.live="finaStatus" id="finaStatus" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                    <option value="">Svi FINA statusi</option>
                    @foreach(\App\Enums\FinaStatus::cases() as $case)
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

    <!-- Desktop Table -->
    <div class="hidden overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 md:block">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Račun</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Kupac</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Iznos</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">FINA Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Datum</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Akcije</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4">
                            <a href="{{ route('invoices.show', $log->invoice) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400" wire:navigate>
                                #{{ $log->invoice->full_invoice_number ?? $log->invoice_id }}
                            </a>
                            @if($log->fina_invoice_id)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">FINA: {{ $log->fina_invoice_id }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-zinc-900 dark:text-white">{{ $log->invoice->customer->name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log->invoice->customer->oib }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ number_format($log->invoice->total_amount, 2, ',', '.') }} €
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->status->badge() }}-100 text-{{ $log->status->badge() }}-800">
                                {{ $log->status->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($log->fina_status)
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->fina_status->badge() }}-100 text-{{ $log->fina_status->badge() }}-800">
                                    {{ $log->fina_status->label() }}
                                </span>
                            @else
                                <span class="text-xs text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                @if($log->status === \App\Enums\EracunStatus::FAILED && $log->retry_count < 3)
                                    <button wire:click="retryInvoice({{ $log->id }})" wire:confirm="Želite li ponovno pokušati poslati ovaj račun?" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 transition-colors" title="Pošalji ponovno">
                                        <i class="fas fa-redo fa-lg"></i>
                                    </button>
                                @endif

                                @if($log->fina_invoice_id)
                                    <button wire:click="checkStatus({{ $log->id }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Provjeri status">
                                        <i class="fas fa-sync fa-lg"></i>
                                    </button>
                                @endif

                                @if($log->ubl_xml)
                                    <button wire:click="viewXml({{ $log->id }}, 'ubl')" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 transition-colors" title="Prikaži UBL XML">
                                        <i class="fas fa-code fa-lg"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Nema poslanih e-računa
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="space-y-4 md:hidden">
        @forelse ($logs as $log)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Header -->
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <a href="{{ route('invoices.show', $log->invoice) }}" class="text-base font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400" wire:navigate>
                            #{{ $log->invoice->full_invoice_number ?? $log->invoice_id }}
                        </a>
                        @if($log->fina_invoice_id)
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">FINA: {{ $log->fina_invoice_id }}</p>
                        @endif
                    </div>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->status->badge() }}-100 text-{{ $log->status->badge() }}-800">
                        {{ $log->status->label() }}
                    </span>
                </div>

                <!-- Customer -->
                <div class="mb-3">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $log->invoice->customer->name }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log->invoice->customer->oib }}</p>
                </div>

                <!-- Info Grid -->
                <div class="mb-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Iznos</p>
                        <p class="font-semibold text-zinc-900 dark:text-white">{{ number_format($log->invoice->total_amount, 2, ',', '.') }} €</p>
                    </div>
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Datum</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $log->created_at->format('d.m.Y') }}</p>
                        <p class="text-xs text-zinc-500">{{ $log->created_at->format('H:i') }}</p>
                    </div>
                </div>

                <!-- FINA Status -->
                @if($log->fina_status)
                    <div class="mb-3">
                        <p class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">FINA Status</p>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->fina_status->badge() }}-100 text-{{ $log->fina_status->badge() }}-800">
                            {{ $log->fina_status->label() }}
                        </span>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                    @if($log->status === \App\Enums\EracunStatus::FAILED && $log->retry_count < 3)
                        <button wire:click="retryInvoice({{ $log->id }})" wire:confirm="Želite li ponovno pokušati poslati ovaj račun?" class="inline-flex items-center gap-1.5 rounded bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50 transition-colors">
                            <i class="fas fa-redo"></i>
                            Pošalji ponovno
                        </button>
                    @endif

                    @if($log->fina_invoice_id)
                        <button wire:click="checkStatus({{ $log->id }})" class="inline-flex items-center gap-1.5 rounded bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors">
                            <i class="fas fa-sync"></i>
                            Provjeri status
                        </button>
                    @endif

                    @if($log->ubl_xml)
                        <button wire:click="viewXml({{ $log->id }}, 'ubl')" class="inline-flex items-center gap-1.5 rounded bg-purple-100 px-3 py-1.5 text-xs font-medium text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:hover:bg-purple-900/50 transition-colors">
                            <i class="fas fa-code"></i>
                            Prikaži XML
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Nema poslanih e-računa</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $logs->links() }}
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
