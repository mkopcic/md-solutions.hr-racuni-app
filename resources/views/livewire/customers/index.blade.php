<div x-data="{ customerDialog: false, customerViewDialog: false }">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Kupci</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje kupcima i klijentima</p>
        </div>
        <button wire:click="create" @click="customerDialog = true"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700">
            <i class="fas fa-user-plus"></i>
            Dodaj kupca
        </button>
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

    <!-- Export Radnje -->
    <div class="mb-4 flex items-center gap-2">
        <button wire:click="exportExcel"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-excel"></i>
            Izvezi Excel
        </button>
        <button wire:click="exportCsv"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-csv"></i>
            Izvezi CSV
        </button>
    </div>

    <div class="mb-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-zinc-500"></i>
            </div>
            <input type="text" wire:model.live="search" placeholder="Pretraži kupce..."
                class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 pl-10 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
        </div>
    </div>

    <!-- Desktop tablica - skrivena na mobilnim uređajima -->
    <div class="hidden lg:block overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Naziv</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        OIB</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Adresa</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Grad</th>
                    <th scope="col"
                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Broj računa</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Akcije</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $customer->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->oib }}
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->address }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->city }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                            <span
                                class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                <i class="fas fa-file-invoice mr-1"></i>
                                {{ $customer->invoices_count }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <button wire:click="view({{ $customer->id }})" @click="customerViewDialog = true"
                                class="mr-2 text-green-600 hover:text-green-900 dark:hover:text-green-400 inline-flex items-center gap-1">
                                <i class="fas fa-eye"></i>
                                Pregled
                            </button>
                            <button wire:click="edit({{ $customer->id }})" @click="customerDialog = true"
                                class="mr-2 text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 inline-flex items-center gap-1">
                                <i class="fas fa-edit"></i>
                                Uredi
                            </button>
                            <button wire:click="delete({{ $customer->id }})"
                                wire:confirm="Jeste li sigurni da želite obrisati ovog kupca?"
                                class="text-red-600 hover:text-red-900 dark:hover:text-red-400 inline-flex items-center gap-1">
                                <i class="fas fa-trash"></i>
                                Obriši
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Nema pronađenih kupaca.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobilni prikaz kartica - vidljiv samo na manjim ekranima -->
    <div class="lg:hidden space-y-4">
        @forelse ($customers as $customer)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $customer->name }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">OIB: {{ $customer->oib }}</p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                        <i class="fas fa-file-invoice mr-1"></i>
                        {{ $customer->invoices_count }}
                    </span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-zinc-400 mt-0.5"></i>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $customer->address }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-city text-zinc-400"></i>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $customer->city }}</span>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button wire:click="view({{ $customer->id }})" @click="customerViewDialog = true"
                        class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg border border-green-600 bg-green-50 px-3 py-2 text-sm font-medium text-green-600 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30">
                        <i class="fas fa-eye"></i>
                        Pregled
                    </button>
                    <button wire:click="edit({{ $customer->id }})" @click="customerDialog = true"
                        class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg border border-blue-600 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30">
                        <i class="fas fa-edit"></i>
                        Uredi
                    </button>
                    <button wire:click="delete({{ $customer->id }})"
                        wire:confirm="Jeste li sigurni da želite obrisati ovog kupca?"
                        class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg border border-red-600 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30">
                        <i class="fas fa-trash"></i>
                        Obriši
                    </button>
                </div>
            </div>
        @empty
            <div
                class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <i class="fas fa-users text-4xl text-zinc-300 dark:text-zinc-600 mb-3"></i>
                <p class="text-zinc-500 dark:text-zinc-400">Nema pronađenih kupaca.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>

    <!-- Customer Dialog -->
    <div x-show="customerDialog" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4" style="display: none;"
        @keydown.escape="customerDialog = false" @click.self="customerDialog = false">

        <div class="bg-white dark:bg-zinc-800 rounded-lg p-8 shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                    @if ($editingCustomerId)
                        <i class="fas fa-edit"></i>
                        Uredi kupca
                    @else
                        <i class="fas fa-plus"></i>
                        Dodaj novog kupca
                    @endif
                </h3>
                <button @click="customerDialog = false"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit="save">
                <div class="mb-4">
                    <label for="name" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-user"></i>
                        Naziv
                    </label>
                    <input type="text" wire:model="name" id="name"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Naziv kupca">
                    @error('name')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="oib" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-id-card"></i>
                        OIB
                    </label>
                    <input type="text" wire:model="oib" id="oib"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="OIB kupca">
                    @error('oib')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="address" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresa
                    </label>
                    <input type="text" wire:model="address" id="address"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Adresa kupca">
                    @error('address')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="city" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-city"></i>
                        Grad
                    </label>
                    <input type="text" wire:model="city" id="city"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Grad">
                    @error('city')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeDialog" @click="customerDialog = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="fas fa-times"></i>
                        Odustani
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fas fa-save"></i>
                        Spremi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer View Dialog (Read-only) -->
    <div x-show="customerViewDialog" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4" style="display: none;"
        @keydown.escape="customerViewDialog = false" @click.self="customerViewDialog = false">

        <div class="bg-white dark:bg-zinc-800 rounded-lg p-8 shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                    <i class="fas fa-eye"></i>
                    Pregled kupca
                </h3>
                <button @click="customerViewDialog = false"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-user"></i>
                        Naziv
                    </label>
                    <p class="text-base font-medium text-zinc-900 dark:text-white">{{ $name }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-id-card"></i>
                        OIB
                    </label>
                    <p class="text-base font-medium text-zinc-900 dark:text-white">{{ $oib }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresa
                    </label>
                    <p class="text-base text-zinc-700 dark:text-zinc-300">{{ $address }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-city"></i>
                        Grad
                    </label>
                    <p class="text-base text-zinc-700 dark:text-zinc-300">{{ $city }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-file-invoice"></i>
                        Broj računa
                    </label>
                    <p class="text-base font-semibold text-blue-600 dark:text-blue-400">
                        {{ $viewingCustomer?->invoices_count ?? 0 }}</p>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <button type="button" @click="customerViewDialog = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="fas fa-times"></i>
                        Zatvori
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Listen for successful form save to close dialog
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-customer-dialog', () => {
                console.log('Livewire signaled to close edit dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerDialog = false;
                }
            });

            Livewire.on('open-customer-dialog', () => {
                console.log('Livewire signaled to open edit dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerDialog = true;
                }
            });

            Livewire.on('close-customer-view-dialog', () => {
                console.log('Livewire signaled to close view dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerViewDialog = false;
                }
            });

            Livewire.on('open-customer-view-dialog', () => {
                console.log('Livewire signaled to open view dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerViewDialog = true;
                }
            });
        });
    </script>
</div>
