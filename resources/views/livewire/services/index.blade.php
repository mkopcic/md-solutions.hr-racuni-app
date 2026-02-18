<div x-data="{ serviceDialog: false }">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Usluge</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje predlošcima usluga za račune</p>
        </div>
        <button wire:click="create" @click="serviceDialog = true"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700">
            <i class="fas fa-plus"></i>
            Dodaj uslugu
        </button>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-green-100 p-4 text-green-700 dark:bg-green-900 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-red-100 p-4 text-red-700 dark:bg-red-900 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-zinc-500"></i>
            </div>
            <input type="text" wire:model.live="search" placeholder="Pretraži usluge..."
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
                        Opis</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Cijena</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Jedinica</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Status</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Akcije</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($services as $service)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            <div class="max-w-xs" title="{{ $service->name }}">
                                {{ Str::limit($service->name, 40) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            <div class="max-w-md" title="{{ $service->description }}">
                                {{ Str::limit($service->description, 60) ?: '-' }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ number_format($service->price, 2, ',', '.') }} €
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $service->unit }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if($service->active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="fas fa-check-circle"></i>
                                    Aktivna
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    <i class="fas fa-times-circle"></i>
                                    Neaktivna
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <button wire:click="toggleActive({{ $service->id }})"
                                class="mr-2 text-amber-600 hover:text-amber-900 dark:hover:text-amber-400 inline-flex items-center gap-1"
                                title="{{ $service->active ? 'Deaktiviraj' : 'Aktiviraj' }}">
                                <i class="fas fa-{{$service->active ? 'toggle-on' : 'toggle-off'}}"></i>
                            </button>
                            <button wire:click="edit({{ $service->id }})" @click="serviceDialog = true"
                                class="mr-2 text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 inline-flex items-center gap-1">
                                <i class="fas fa-edit"></i>
                                Uredi
                            </button>
                            <button wire:click="delete({{ $service->id }})"
                                wire:confirm="Jeste li sigurni da želite obrisati ovu uslugu?"
                                class="text-red-600 hover:text-red-900 dark:hover:text-red-400 inline-flex items-center gap-1">
                                <i class="fas fa-trash"></i>
                                Obriši
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Nema pronađenih usluga.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $services->links() }}
    </div>

    <!-- Service Dialog -->
    <div x-show="serviceDialog" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4" style="display: none;"
        @keydown.escape="serviceDialog = false" @click.self="serviceDialog = false">

        <div class="bg-white dark:bg-zinc-800 rounded-lg p-8 shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                    @if ($editingServiceId)
                        <i class="fas fa-edit"></i>
                        Uredi uslugu
                    @else
                        <i class="fas fa-plus"></i>
                        Dodaj novu uslugu
                    @endif
                </h3>
                <button @click="serviceDialog = false"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit="save">
                <div class="mb-4">
                    <label for="name" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-tag"></i>
                        Naziv usluge
                    </label>
                    <input type="text" wire:model="name" id="name"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Naziv usluge">
                    @error('name')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-align-left"></i>
                        Opis
                    </label>
                    <textarea wire:model="description" id="description" rows="3"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Opis usluge (opcionalno)"></textarea>
                    @error('description')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-euro-sign"></i>
                            Cijena
                        </label>
                        <input type="number" step="0.01" wire:model="price" id="price"
                            class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            placeholder="0.00">
                        @error('price')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-ruler"></i>
                            Jedinica
                        </label>
                        <input type="text" wire:model="unit" id="unit"
                            class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            placeholder="kom, sat, m²...">
                        @error('unit')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="active" id="active"
                            class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-check-circle"></i>
                            Aktivna usluga
                        </span>
                    </label>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeDialog" @click="serviceDialog = false"
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
        // Listen for successful form save to close dialog
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-service-dialog', () => {
                console.log('Livewire signaled to close dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.serviceDialog = false;
                }
            });
        });
    </script>
</div>
