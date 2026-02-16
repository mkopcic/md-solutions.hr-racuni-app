<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Porezni razredi</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje paušalnim poreznim razredima</p>
        </div>
        <button wire:click="openModal" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
            Novi porezni razred
        </button>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Raspon prihoda (€)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Godišnja osnovica (€)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Godišnji porez (€)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Mjesečni porez (€)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Prirez (€)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Kvartalni iznos (€)
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Akcije
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($taxBrackets as $taxBracket)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4">
                            {{ number_format($taxBracket->from_amount, 2, ',', '.') }} - {{ number_format($taxBracket->to_amount, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            {{ number_format($taxBracket->yearly_base, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            {{ number_format($taxBracket->yearly_tax, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            {{ number_format($taxBracket->monthly_tax, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            {{ number_format($taxBracket->city_tax, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            {{ number_format($taxBracket->quarterly_amount, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <button wire:click="openModal(true, {{ $taxBracket->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                Uredi
                            </button>
                            <button wire:click="delete({{ $taxBracket->id }})" class="ml-4 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Jeste li sigurni da želite obrisati ovaj porezni razred?')">
                                Obriši
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm italic text-zinc-500 dark:text-zinc-400">
                            Nema definiranih poreznih razreda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $taxBrackets->links() }}
    </div>

    <!-- Tax Bracket Dialog -->
    <dialog id="tax-dialog" class="rounded-lg border-0 bg-white p-6 shadow-xl dark:bg-zinc-900 backdrop:bg-black backdrop:bg-opacity-50">
        <div class="min-w-96">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    @if($isEdit)
                        <i class="fas fa-edit"></i>
                        Uredi porezni razred
                    @else
                        <i class="fas fa-plus"></i>
                        Dodaj novi porezni razred
                    @endif
                </h3>
                <button onclick="document.getElementById('tax-dialog').close()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="from_amount" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-coins"></i>
                            Od iznosa (€)
                        </label>
                        <input type="number" step="0.01" wire:model="from_amount" id="from_amount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('from_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="to_amount" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-coins"></i>
                            Do iznosa (€)
                        </label>
                        <input type="number" step="0.01" wire:model="to_amount" id="to_amount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('to_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="yearly_base" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-calendar-alt"></i>
                            Godišnja osnovica (€)
                        </label>
                        <input type="number" step="0.01" wire:model="yearly_base" id="yearly_base" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('yearly_base') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="yearly_tax" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-percentage"></i>
                            Godišnji porez (€)
                        </label>
                        <input type="number" step="0.01" wire:model="yearly_tax" id="yearly_tax" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('yearly_tax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="monthly_tax" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-calendar"></i>
                            Mjesečni porez (€)
                        </label>
                        <input type="number" step="0.01" wire:model="monthly_tax" id="monthly_tax" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('monthly_tax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="city_tax" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            <i class="fas fa-city"></i>
                            Prirez (€)
                        </label>
                        <input type="number" step="0.01" wire:model="city_tax" id="city_tax" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('city_tax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="quarterly_amount" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-chart-line"></i>
                        Kvartalni iznos (€)
                    </label>
                    <input type="number" step="0.01" wire:model="quarterly_amount" id="quarterly_amount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('quarterly_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" onclick="document.getElementById('tax-dialog').close()" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="fas fa-times"></i>
                        Odustani
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fas fa-save"></i>
                        Spremi
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        // Listen for Livewire events to open/close dialog
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-tax-dialog', () => {
                document.getElementById('tax-dialog').showModal();
            });

            Livewire.on('close-tax-dialog', () => {
                document.getElementById('tax-dialog').close();
            });
        });
    </script>
</div>
