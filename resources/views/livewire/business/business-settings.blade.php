<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Podaci o obrtu</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Uređivanje osnovnih podataka o obrtu</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Naziv obrta</label>
                    <input type="text" wire:model="name" id="name" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('name') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="oib" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">OIB</label>
                    <input type="text" wire:model="oib" id="oib" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('oib') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="in_vat_system" class="mb-2 flex items-center text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model="in_vat_system" id="in_vat_system" class="mr-2 h-4 w-4 rounded border-zinc-300 bg-zinc-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:ring-offset-zinc-800 dark:focus:ring-blue-600">
                        U sustavu PDV-a
                    </label>
                    @error('in_vat_system') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Obrt je u sustavu PDV-a (za e-Račun)</p>
                </div>

                <div>
                    <label for="business_space_label" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Oznaka poslovnog prostora</label>
                    <input type="text" wire:model="business_space_label" id="business_space_label" placeholder="npr. PP1, SALON" maxlength="10" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('business_space_label') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Za fiskalizaciju (max 10 znakova)</p>
                </div>

                <div>
                    <label for="cash_register_label" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Oznaka naplatnog uređaja</label>
                    <input type="text" wire:model="cash_register_label" id="cash_register_label" placeholder="npr. 1, NAP1" maxlength="10" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('cash_register_label') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Za fiskalizaciju (max 10 znakova)</p>
                </div>

                <div>
                    <label for="address" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Adresa</label>
                    <input type="text" wire:model="address" id="address" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('address') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="location" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Mjesto izdavanja računa</label>
                    <input type="text" wire:model="location" id="location" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('location') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="iban" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">IBAN</label>
                    <input type="text" wire:model="iban" id="iban" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('iban') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                    <input type="email" wire:model="email" id="email" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('email') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Telefon</label>
                    <input type="text" wire:model="phone" id="phone" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('phone') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="months_active" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Broj mjeseci obavljanja djelatnosti (za izračun paušala)</label>
                    <input type="number" wire:model="months_active" id="months_active" min="1" max="12" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('months_active') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Spremi promjene
                </button>
            </div>
        </form>
    </div>
</div>
