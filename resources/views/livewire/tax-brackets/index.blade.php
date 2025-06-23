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

    <!-- Modal za dodavanje/uređivanje poreznog razreda -->
    @if($showModal)
    <div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
            <div class="inline-block transform overflow-hidden rounded-lg bg-white p-5 text-left align-bottom shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="pb-3 sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-zinc-900 dark:text-zinc-100" id="modal-title">
                            {{ $isEdit ? 'Uredi porezni razred' : 'Dodaj novi porezni razred' }}
                        </h3>
                    </div>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="from_amount" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Od iznosa (€)</label>
                            <input type="number" step="0.01" wire:model="from_amount" id="from_amount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            @error('from_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="to_amount" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Do iznosa (€)</label>
                            <input type="number" step="0.01" wire:model="to_amount" id="to_amount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            @error('to_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="yearly_base" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Godišnja osnovica (€)</label>
                            <input type="number" step="0.01" wire:model="yearly_base" id="yearly_base" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            @error('yearly_base') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="yearly_tax" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Godišnji porez (€)</label>
                            <input type="number" step="0.01" wire:model="yearly_tax" id="yearly_tax" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            @error('yearly_tax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="monthly_tax" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Mjesečni porez (€)</label>
                            <input type="number" step="0.01" wire:model="monthly_tax" id="monthly_tax" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            @error('monthly_tax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="city_tax" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Prirez (€)</label>
                            <input type="number" step="0.01" wire:model="city_tax" id="city_tax" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                            @error('city_tax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="quarterly_amount" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Kvartalni iznos (€)</label>
                        <input type="number" step="0.01" wire:model="quarterly_amount" id="quarterly_amount" class="w-full rounded-lg border border-zinc-300 bg-white p-2 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        @error('quarterly_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            Spremi
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-base font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-600 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                            Odustani
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
