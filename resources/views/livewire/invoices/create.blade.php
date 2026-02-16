<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Novi račun</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Kreiranje novog računa</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">Osnovni podaci</h3>

            <div class="grid gap-6 md:grid-cols-3">
                <div>
                    <label for="invoice_type" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Vrsta računa <span class="text-red-500">*</span></label>
                    <select wire:model.live="invoice_type" id="invoice_type" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="R">Račun (R)</option>
                        <option value="RA">Račun avansni (RA)</option>
                        <option value="P">Predračun (P)</option>
                    </select>
                    @error('invoice_type') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="invoice_number" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Broj računa <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="invoice_number" id="invoice_number" readonly class="w-full rounded-lg border border-zinc-300 bg-zinc-100 p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('invoice_number') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    <p class="mt-1 text-xs text-zinc-500">Format: {{ $invoice_number }}-1-1</p>
                </div>

                <div>
                    <label for="payment_method" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Način plaćanja <span class="text-red-500">*</span></label>
                    <select wire:model="payment_method" id="payment_method" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="virman">Virman</option>
                        <option value="gotovina">Gotovina</option>
                        <option value="kartica">Kartica</option>
                    </select>
                    @error('payment_method') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <label for="customer_id" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Kupac <span class="text-red-500">*</span></label>
                    <select wire:model="customer_id" id="customer_id" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="">Odaberi kupca</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->oib }})</option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="issue_date" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Datum izdavanja <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="issue_date" id="issue_date" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('issue_date') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="delivery_date" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Datum isporuke <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="delivery_date" id="delivery_date" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('delivery_date') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="due_date" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Datum dospijeća <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="due_date" id="due_date" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('due_date') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <label for="note" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Napomena</label>
                    <textarea wire:model="note" id="note" rows="3" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="Napomena koja će biti prikazana na računu"></textarea>
                </div>

                <div>
                    <label for="advance_note" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Napomena za avansno plaćanje</label>
                    <textarea wire:model="advance_note" id="advance_note" rows="3" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="Ako je račun plaćen avansom, dodajte bilješku ovdje"></textarea>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Stavke računa</h3>
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1 text-sm font-medium text-white hover:bg-blue-700">
                    <i class="fas fa-plus"></i>
                    Dodaj stavku
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th scope="col" class="w-4/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Opis</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Jed.mj.</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Količina</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Cijena (€)</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Popust (%)</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">PDV %</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">PDV iznos</th>
                            <th scope="col" class="w-1/12 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ukupno (€)</th>
                            <th scope="col" class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @foreach ($items as $index => $item)
                            <tr>
                <td class="px-4 py-2">
                    <div class="space-y-2">
                        <input type="text" wire:model="items.{{ $index }}.name" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="Opis stavke">

                        <select wire:change="selectService({{ $index }}, $event.target.value)" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-sm text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            <option value="">-- Ili odaberi uslugu --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} ({{ number_format($service->price, 2, ',', '.') }} €)</option>
                            @endforeach
                        </select>
                    </div>
                    @error('items.'.$index.'.name') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </td>
                                <td class="px-4 py-2">
                                    <select wire:model="items.{{ $index }}.unit" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                        <option value="kom">kom</option>
                                        <option value="sat">sat</option>
                                        <option value="dan">dan</option>
                                    </select>
                                    @error('items.'.$index.'.unit') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" wire:model="items.{{ $index }}.quantity" wire:change="updateItemTotal({{ $index }})" min="0.01" step="0.01" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                    @error('items.'.$index.'.quantity') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" wire:model="items.{{ $index }}.price" wire:change="updateItemTotal({{ $index }})" min="0" step="0.01" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                    @error('items.'.$index.'.price') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" wire:model="items.{{ $index }}.discount" wire:change="updateItemTotal({{ $index }})" min="0" step="0.01" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                    @error('items.'.$index.'.discount') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <select wire:model="items.{{ $index }}.tax_rate" wire:change="updateItemTotal({{ $index }})" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                        @foreach($taxRates as $rate)
                                            <option value="{{ $rate }}">{{ number_format($rate, 0) }}%</option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.tax_rate') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" wire:model="items.{{ $index }}.tax_amount" readonly class="w-full rounded-lg border border-zinc-300 bg-zinc-100 p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" wire:model="items.{{ $index }}.total" readonly class="w-full rounded-lg border border-zinc-300 bg-zinc-100 p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-2">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="rounded-md bg-red-100 p-1 text-red-600 hover:bg-red-200 dark:bg-red-800/20 dark:text-red-400">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                        <!-- Totals -->
                        <tr class="bg-zinc-50 dark:bg-zinc-800">
                            <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">OSNOVICA (bez PDV-a):</td>
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">{{ number_format($subtotal, 2, ',', '.') }} €</td>
                            <td></td>
                        </tr>
                        <tr class="bg-zinc-50 dark:bg-zinc-800">
                            <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">PDV UKUPNO:</td>
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-white">{{ number_format($taxTotal, 2, ',', '.') }} €</td>
                            <td></td>
                        </tr>
                        <tr class="bg-blue-50 dark:bg-blue-900/20">
                            <td colspan="5" class="px-4 py-3 text-right text-base font-bold text-zinc-900 dark:text-white">UKUPNO ZA NAPLATU:</td>
                            <td colspan="3" class="px-4 py-3 text-right text-base font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalAmount, 2, ',', '.') }} €</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                @error('items') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('invoices.index') }}" class="rounded-lg border border-zinc-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
                Odustani
            </a>

            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                Spremi račun
            </button>
        </div>
    </form>
</div>
