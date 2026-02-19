<div>
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">e-Račun Logovi</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Centralni log svih e-Račun transakcija</p>
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route('eracun.outgoing.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 sm:w-auto dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                <i class="fas fa-paper-plane"></i>
                Izlazni
            </a>
            <a href="{{ route('eracun.incoming.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 sm:w-auto dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                <i class="fas fa-inbox"></i>
                Ulazni
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
    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ukupno Logova</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Izlazni</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['outgoing'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ulazni</p>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $stats['incoming'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Greške</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Čeka Retry</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending_retry'] }}</p>
        </div>
    </div>

    <!-- Filteri -->
    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4">
            <label for="search" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pretraži</label>
            <input type="text" wire:model.live="search" id="search" placeholder="Pretraži po Message ID, FINA ID, kupcu ili dobavljaču" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400">
        </div>

        <div class="grid gap-4 md:grid-cols-5">
            <div>
                <label for="direction" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Smjer</label>
                <select wire:model.live="direction" id="direction" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                    <option value="">Svi smjerovi</option>
                    <option value="outgoing">Izlazni</option>
                    <option value="incoming">Ulazni</option>
                </select>
            </div>

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

    <!-- Tablica -->
    <div class="hidden overflow-hidden rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Smjer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Račun</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Partner</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">FINA Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Retry</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Datum</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Akcije</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                #{{ $log->id }}
                                @if($log->fina_invoice_id)
                                    <div class="text-xs">{{ substr($log->fina_invoice_id, 0, 15) }}...</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($log->direction === 'outgoing')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                        <i class="fas fa-paper-plane"></i> Izlazni
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        <i class="fas fa-inbox"></i> Ulazni
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @if($log->invoice)
                                    <a href="{{ route('invoices.show', $log->invoice) }}" class="font-medium text-blue-600 hover:text-blue-700" wire:navigate>
                                        #{{ $log->invoice->full_invoice_number ?? $log->invoice_id }}
                                    </a>
                                @elseif($log->incomingInvoice)
                                    <a href="{{ route('eracun.incoming.show', $log->incomingInvoice) }}" class="font-medium text-blue-600 hover:text-blue-700" wire:navigate>
                                        {{ $log->incomingInvoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-zinc-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($log->invoice && $log->invoice->customer)
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ $log->invoice->customer->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $log->invoice->customer->oib }}</div>
                                @elseif($log->incomingInvoice)
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ $log->incomingInvoice->supplier_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $log->incomingInvoice->supplier_oib }}</div>
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->status->badge() }}-100 text-{{ $log->status->badge() }}-800">
                                    {{ $log->status->label() }}
                                </span>
                                @if($log->error_message)
                                    <div class="mt-1 text-xs text-red-600" title="{{ $log->error_message }}">
                                        <i class="fas fa-exclamation-circle"></i> {{ Str::limit($log->error_message, 30) }}
                                    </div>
                                @endif
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
                                {{ $log->retry_count }}/3
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $log->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    @if($log->ubl_xml)
                                        <button wire:click="viewXml({{ $log->id }}, 'ubl')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400" title="UBL XML">
                                            <i class="fas fa-file-code"></i>
                                        </button>
                                    @endif
                                    @if($log->request_xml)
                                        <button wire:click="viewXml({{ $log->id }}, 'request')" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400" title="Request XML">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                    @endif
                                    @if($log->response_xml)
                                        <button wire:click="viewXml({{ $log->id }}, 'response')" class="text-green-600 hover:text-green-900 dark:text-green-400" title="Response XML">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                    @endif
                                    <button wire:click="deleteLog({{ $log->id }})" wire:confirm="Želite li obrisati ovaj log?" class="text-red-600 hover:text-red-900 dark:text-red-400" title="Obriši">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                Nema logova
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="space-y-4 md:hidden">
        @forelse ($logs as $log)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Header -->
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">#{{ $log->id }}</span>
                        @if($log->direction === 'outgoing')
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                <i class="fas fa-paper-plane"></i> Izlazni
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                <i class="fas fa-inbox"></i> Ulazni
                            </span>
                        @endif
                    </div>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->status->badge() }}-100 text-{{ $log->status->badge() }}-800">
                        {{ $log->status->label() }}
                    </span>
                </div>

                <!-- FINA ID -->
                @if($log->fina_invoice_id)
                    <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">FINA: {{ substr($log->fina_invoice_id, 0, 20) }}...</p>
                @endif

                <!-- Invoice & Partner -->
                <div class="mb-3 space-y-2">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Račun</p>
                        @if($log->invoice)
                            <a href="{{ route('invoices.show', $log->invoice) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400" wire:navigate>
                                #{{ $log->invoice->full_invoice_number ?? $log->invoice_id }}
                            </a>
                        @elseif($log->incomingInvoice)
                            <a href="{{ route('eracun.incoming.show', $log->incomingInvoice) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400" wire:navigate>
                                {{ $log->incomingInvoice->invoice_number }}
                            </a>
                        @else
                            <span class="text-sm text-zinc-400">N/A</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Partner</p>
                        @if($log->invoice && $log->invoice->customer)
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $log->invoice->customer->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log->invoice->customer->oib }}</p>
                        @elseif($log->incomingInvoice)
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $log->incomingInvoice->supplier_name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log->incomingInvoice->supplier_oib }}</p>
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </div>
                </div>

                <!-- Status Grid -->
                <div class="mb-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">FINA Status</p>
                        @if($log->fina_status)
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $log->fina_status->badge() }}-100 text-{{ $log->fina_status->badge() }}-800">
                                {{ $log->fina_status->label() }}
                            </span>
                        @else
                            <span class="text-xs text-zinc-400">-</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Retry</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $log->retry_count }}/3</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Datum</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $log->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>

                <!-- Error Message -->
                @if($log->error_message)
                    <div class="mb-3 rounded bg-red-50 p-2 dark:bg-red-900/10">
                        <p class="text-xs text-red-600 dark:text-red-400">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ Str::limit($log->error_message, 100) }}
                        </p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                    @if($log->ubl_xml)
                        <button wire:click="viewXml({{ $log->id }}, 'ubl')" class="inline-flex items-center gap-1 rounded bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400">
                            <i class="fas fa-file-code"></i>
                            UBL XML
                        </button>
                    @endif
                    @if($log->request_xml)
                        <button wire:click="viewXml({{ $log->id }}, 'request')" class="inline-flex items-center gap-1 rounded bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                            <i class="fas fa-arrow-up"></i>
                            Request
                        </button>
                    @endif
                    @if($log->response_xml)
                        <button wire:click="viewXml({{ $log->id }}, 'response')" class="inline-flex items-center gap-1 rounded bg-green-100 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400">
                            <i class="fas fa-arrow-down"></i>
                            Response
                        </button>
                    @endif
                    <button wire:click="deleteLog({{ $log->id }})" wire:confirm="Želite li obrisati ovaj log?" class="inline-flex items-center gap-1 rounded bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400">
                        <i class="fas fa-trash"></i>
                        Obriši
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Nema logova</p>
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
