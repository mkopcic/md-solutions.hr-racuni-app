<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Podaci o obrtu</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Uređivanje osnovnih podataka o obrtu</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300">
            <i class="fas fa-check-circle shrink-0"></i>
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Osnovni podaci --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Osnovni podaci</h2>
            <div class="mb-4 border-b border-zinc-100 dark:border-zinc-800"></div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>Naziv obrta</flux:label>
                    <flux:input wire:model="name" id="name" />
                    @error('name') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>OIB</flux:label>
                    <flux:input wire:model="oib" id="oib" />
                    @error('oib') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Adresa</flux:label>
                    <flux:input wire:model="address" id="address" />
                    @error('address') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Mjesto izdavanja računa</flux:label>
                    <flux:input wire:model="location" id="location" />
                    @error('location') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" wire:model="email" id="email" />
                    @error('email') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Telefon</flux:label>
                    <flux:input wire:model="phone" id="phone" />
                    @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>
        </div>

        {{-- Financije --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Financije</h2>
            <div class="mb-4 border-b border-zinc-100 dark:border-zinc-800"></div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>IBAN</flux:label>
                    <flux:input wire:model="iban" id="iban" class="font-mono" />
                    @error('iban') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Broj mjeseci obavljanja djelatnosti</flux:label>
                    <flux:input type="number" wire:model="months_active" id="months_active" min="1" max="12" />
                    <flux:description>Za izračun paušala</flux:description>
                    @error('months_active') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:checkbox wire:model="in_vat_system" id="in_vat_system" label="U sustavu PDV-a" />
                    <flux:description>Obrt je u sustavu PDV-a (za e-Račun)</flux:description>
                    @error('in_vat_system') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>
        </div>

        {{-- Fiskalizacija --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Fiskalizacija</h2>
            <div class="mb-4 border-b border-zinc-100 dark:border-zinc-800"></div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>Oznaka poslovnog prostora</flux:label>
                    <flux:input wire:model="business_space_label" id="business_space_label" placeholder="npr. PP1, SALON" maxlength="10" />
                    <flux:description>Za fiskalizaciju (max 10 znakova)</flux:description>
                    @error('business_space_label') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Oznaka naplatnog uređaja</flux:label>
                    <flux:input wire:model="cash_register_label" id="cash_register_label" placeholder="npr. 1, NAP1" maxlength="10" />
                    <flux:description>Za fiskalizaciju (max 10 znakova)</flux:description>
                    @error('cash_register_label') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">
                Spremi promjene
            </flux:button>
        </div>
    </form>
</div>
