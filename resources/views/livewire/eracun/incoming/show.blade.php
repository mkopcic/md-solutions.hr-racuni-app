<div>
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Ulazni Račun: {{ $invoice->invoice_number }}
                <span
                    class="inline-flex rounded-full px-3 py-1 text-sm font-semibold bg-{{ $invoice->status->badge() }}-100 text-{{ $invoice->status->badge() }}-800">
                    {{ $invoice->status->label() }}
                </span>
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">Primljeno:
                {{ $invoice->received_at ? $invoice->received_at->format('d.m.Y H:i') : 'N/A' }}</p>
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            @if ($invoice->ubl_xml)
                <button wire:click="viewXml"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 sm:w-auto dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    <i class="fas fa-code"></i>
                    Prikaži XML
                </button>
            @endif
            <button wire:click="goBack" type="button"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 sm:w-auto">
                <i class="fas fa-arrow-left"></i>
                Natrag
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
            class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Informacije o dobavljaču -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Informacije o Dobavljaču</h2>
                <dl class="grid gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Naziv</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->supplier_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">OIB</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->supplier_oib }}</dd>
                    </div>
                    @if ($invoice->supplier_address)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Adresa</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->supplier_address }}</dd>
                        </div>
                    @endif
                    @if ($invoice->supplier_city)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Grad / Poštanski broj</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->supplier_postal_code }}
                                {{ $invoice->supplier_city }}</dd>
                        </div>
                    @endif
                    @if ($invoice->supplier_iban)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">IBAN</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->supplier_iban }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Detalji računa -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Detalji Računa</h2>
                <dl class="grid gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Broj računa</dt>
                        <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $invoice->invoice_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">FINA ID</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->fina_invoice_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum izdavanja</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                            {{ $invoice->issue_date->format('d.m.Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Datum dospijeća</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                            {{ $invoice->due_date->format('d.m.Y') }}
                            @if ($invoice->isOverdue())
                                <span class="ml-2 text-xs text-red-600"><i class="fas fa-exclamation-triangle"></i>
                                    Kasni {{ abs($invoice->daysUntilDue()) }} dana</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Način plaćanja</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                            {{ strtoupper($invoice->payment_method) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Valuta</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->currency }}</dd>
                    </div>
                </dl>

                @if ($invoice->notes)
                    <div class="mt-4">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Napomena</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $invoice->notes }}</dd>
                    </div>
                @endif

                @if ($invoice->rejection_reason)
                    <div class="mt-4 rounded bg-red-50 p-3 dark:bg-red-900/10">
                        <dt class="text-sm font-medium text-red-600">Razlog odbijanja</dt>
                        <dd class="mt-1 text-sm text-red-800 dark:text-red-400">{{ $invoice->rejection_reason }}</dd>
                    </div>
                @endif
            </div>

            <!-- Stavke računa -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Stavke</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                                    Naziv</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                                    Količina</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                                    Cijena</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                                    PDV %</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                                    Ukupno</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-3 py-3">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $item->name }}</div>
                                        @if ($item->description)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $item->description }}</div>
                                        @endif
                                        @if ($item->kpd_code)
                                            <div class="text-xs text-zinc-400">KPD: {{ $item->kpd_code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm text-zinc-900 dark:text-white">
                                        {{ number_format($item->quantity, 2) }} {{ $item->unit }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm text-zinc-900 dark:text-white">
                                        {{ number_format($item->price, 2, ',', '.') }} €
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ number_format($item->tax_rate, 0) }}%
                                    </td>
                                    <td class="px-3 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ number_format($item->total, 2, ',', '.') }} €
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <td colspan="4"
                                    class="px-3 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">
                                    Osnovica:</td>
                                <td class="px-3 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ number_format($invoice->subtotal, 2, ',', '.') }} €</td>
                            </tr>
                            <tr>
                                <td colspan="4"
                                    class="px-3 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">PDV:
                                </td>
                                <td class="px-3 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ number_format($invoice->tax_total, 2, ',', '.') }} €</td>
                            </tr>
                            <tr>
                                <td colspan="4"
                                    class="px-3 py-3 text-right text-base font-bold text-zinc-900 dark:text-white">
                                    UKUPNO:</td>
                                <td class="px-3 py-3 text-right text-base font-bold text-zinc-900 dark:text-white">
                                    {{ number_format($invoice->total_amount, 2, ',', '.') }} €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar - Akcije i timeline -->
        <div class="space-y-6">
            <!-- Akcije -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Akcije</h2>
                <div class="space-y-3">
                    @if (
                        $invoice->status === \App\Enums\IncomingInvoiceStatus::RECEIVED ||
                            $invoice->status === \App\Enums\IncomingInvoiceStatus::PENDING_REVIEW)
                        <button wire:click="approve" wire:confirm="Želite li odobriti ovaj račun?"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            <i class="fas fa-check"></i>
                            Odobri račun
                        </button>
                        <button onclick="document.getElementById('reject-modal').showModal()"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            <i class="fas fa-times"></i>
                            Odbij račun
                        </button>
                    @endif

                    @if ($invoice->status === \App\Enums\IncomingInvoiceStatus::APPROVED)
                        <button wire:click="markAsPaid" wire:confirm="Želite li označiti račun kao plaćen?"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <i class="fas fa-euro-sign"></i>
                            Označi kao plaćeno
                        </button>
                    @endif

                    @if ($invoice->status === \App\Enums\IncomingInvoiceStatus::PAID)
                        <button wire:click="archive" wire:confirm="Želite li arhivirati ovaj račun?"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <i class="fas fa-archive"></i>
                            Arhiviraj
                        </button>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Povijest</h2>
                <div class="space-y-4">
                    @if ($invoice->received_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <i class="fas fa-download text-sm text-blue-600 dark:text-blue-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Primljeno</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->received_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($invoice->reviewed_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900">
                                    <i class="fas fa-eye text-sm text-amber-600 dark:text-amber-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Pregledano</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->reviewed_at->format('d.m.Y H:i') }}
                                    @if ($invoice->reviewedBy)
                                        <br>{{ $invoice->reviewedBy->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($invoice->approved_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="fas fa-check text-sm text-green-600 dark:text-green-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Odobreno</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->approved_at->format('d.m.Y H:i') }}
                                    @if ($invoice->approvedBy)
                                        <br>{{ $invoice->approvedBy->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($invoice->rejected_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                                    <i class="fas fa-times text-sm text-red-600 dark:text-red-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Odbijeno</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->rejected_at->format('d.m.Y H:i') }}
                                    @if ($invoice->rejectedBy)
                                        <br>{{ $invoice->rejectedBy->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($invoice->paid_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="fas fa-euro-sign text-sm text-green-600 dark:text-green-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Plaćeno</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->paid_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($invoice->archived_at)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <i class="fas fa-archive text-sm text-zinc-600 dark:text-zinc-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Arhivirano</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->archived_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <dialog id="reject-modal" class="rounded-lg p-6 shadow-xl dark:bg-zinc-800">
        <div class="mb-4">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Odbij Račun</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Molimo unesite razlog odbijanja računa</p>
        </div>
        <form wire:submit="reject">
            <div class="mb-4">
                <textarea wire:model="rejectionReason" rows="4"
                    class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                    placeholder="Razlog odbijanja..."></textarea>
                @error('rejectionReason')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('reject-modal').close()"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                    Odustani
                </button>
                <button type="submit"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Potvrdi odbijanje
                </button>
            </div>
        </form>
    </dialog>

    <!-- XML Modal -->
    <div x-data="{ open: false, xml: '', title: '' }"
        x-on:show-xml-modal.window="open = true; xml = $event.detail.xml; title = $event.detail.title" x-show="open"
        x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
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
