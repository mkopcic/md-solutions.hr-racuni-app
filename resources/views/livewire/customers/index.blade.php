<div x-data="{ customerDialog: false }">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Kupci</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje kupcima i klijentima</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="create" @click="customerDialog = true"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700">
                <i class="fas fa-user-plus"></i>
                Dodaj kupca
            </button>

            <!-- Test gumb -->
            <button @click="customerDialog = true"
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-green-700">
                <i class="fas fa-bug"></i>
                Test Dialog
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
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-zinc-500"></i>
            </div>
            <input type="text" wire:model.live="search" placeholder="Pretraži kupce..."
                class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 pl-10 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
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
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
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
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Nema pronađenih kupaca.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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

    <script>
        // Auto-open dialog ako je create=1 u URL-u
        @if (request()->get('create') == '1')
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    console.log('Auto-opening dialog from URL parameter');
                    // Set customerDialog to true using Alpine
                    const rootElement = document.querySelector('[x-data]');
                    if (rootElement && rootElement.__x) {
                        rootElement.__x.$data.customerDialog = true;
                    }
                }, 500);
            });
        @endif

        // Listen for successful form save to close dialog
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-customer-dialog', () => {
                console.log('Livewire signaled to close dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerDialog = false;
                }
            });
        });
    </script>
</div>
