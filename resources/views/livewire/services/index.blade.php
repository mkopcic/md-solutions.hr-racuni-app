<div x-data="{ serviceDialog: false }">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Usluge</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje predlošcima usluga za račune</p>
        </div>
        <button wire:click="create" @click="serviceDialog = true"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-plus"></i>
            Dodaj uslugu
        </button>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 flex items-center gap-2 rounded-lg bg-green-100 p-4 text-green-700 dark:bg-green-900/30 dark:text-green-300">
            <i class="fas fa-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 flex items-center gap-2 rounded-lg bg-red-100 p-4 text-red-700 dark:bg-red-900/30 dark:text-red-300">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ukupno</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-green-700 dark:text-green-400">Aktivne</p>
            <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Neaktivne</p>
            <p class="mt-1 text-2xl font-bold text-zinc-500 dark:text-zinc-400">{{ $stats['inactive'] }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">Prosj. cijena</p>
            <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-400">{{ number_format($stats['avg_price'], 0, ',', '.') }} €</p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-zinc-400"></i>
            </div>
            <input type="text" wire:model.live="search" placeholder="Pretraži usluge po nazivu ili opisu..."
                class="w-full rounded-lg border border-zinc-300 bg-white py-2.5 pl-10 pr-4 text-sm text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400" />
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <button wire:click="exportExcel" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <i class="fas fa-file-excel text-green-600"></i>
                Excel
            </button>
            <button wire:click="exportCsv" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <i class="fas fa-file-csv text-orange-600"></i>
                CSV
            </button>
        </div>
    </div>

    <!-- Card Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($services as $service)
            <div wire:key="service-{{ $service->id }}" class="group flex flex-col rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Card header -->
                <div class="flex items-start justify-between p-4 pb-3">
                    <div class="min-w-0 flex-1 pr-2">
                        <h3 class="text-sm font-semibold leading-snug text-zinc-900 dark:text-white" title="{{ $service->name }}">
                            {{ Str::limit($service->name, 55) }}
                        </h3>
                    </div>
                    @if($service->active)
                        <span class="shrink-0 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-400">
                            Aktivna
                        </span>
                    @else
                        <span class="shrink-0 inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            Neaktivna
                        </span>
                    @endif
                </div>

                <!-- Description -->
                <div class="flex-1 px-4 pb-3">
                    @if($service->description)
                        <p class="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400" title="{{ $service->description }}">
                            {{ Str::limit($service->description, 90) }}
                        </p>
                    @else
                        <p class="text-xs italic text-zinc-300 dark:text-zinc-600">Bez opisa</p>
                    @endif
                </div>

                <!-- Price -->
                <div class="mx-4 mb-4 flex items-baseline justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-800">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Cijena / {{ $service->unit }}</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-white">
                        {{ number_format($service->price, 2, ',', '.') }} €
                    </span>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                    <button wire:click="toggleActive({{ $service->id }})"
                        title="{{ $service->active ? 'Deaktiviraj' : 'Aktiviraj' }}"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium transition-colors
                            {{ $service->active
                                ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                                : 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20' }}">
                        <i class="fas fa-{{ $service->active ? 'toggle-on' : 'toggle-off' }}"></i>
                        {{ $service->active ? 'Deaktiviraj' : 'Aktiviraj' }}
                    </button>
                    <div class="flex items-center gap-1">
                        <button wire:click="edit({{ $service->id }})" @click="serviceDialog = true"
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                            <i class="fas fa-edit"></i>
                            Uredi
                        </button>
                        <button wire:click="delete({{ $service->id }})"
                            wire:confirm="Jeste li sigurni da želite obrisati ovu uslugu?"
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <i class="fas fa-trash"></i>
                            Obriši
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <i class="fas fa-concierge-bell mb-3 text-4xl text-zinc-300 dark:text-zinc-600"></i>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nema pronađenih usluga.</p>
                @if($search)
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Pokušajte s drugim pojmom pretrage.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
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
