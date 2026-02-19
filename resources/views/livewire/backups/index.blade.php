<div class="space-y-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Backupovi</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje backup datotekama</p>
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="loadBackups" variant="outline" icon="arrow-path">
                Osvježi
            </flux:button>
            <flux:button wire:click="runBackup" wire:loading.attr="disabled" variant="primary" icon="cloud-arrow-up">
                <span wire:loading.remove wire:target="runBackup">Kreiraj novi backup</span>
                <span wire:loading wire:target="runBackup">Kreiranje backupa...</span>
            </flux:button>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="rounded-lg bg-green-100 p-4 text-green-700 dark:bg-green-900 dark:text-green-200">
            <div class="flex items-center gap-2">
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="rounded-lg bg-red-100 p-4 text-red-700 dark:bg-red-900 dark:text-red-200">
            <div class="flex items-center gap-2">
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading.delay wire:target="runBackup"
        class="rounded-lg bg-blue-100 p-4 text-blue-700 dark:bg-blue-900 dark:text-blue-200">
        <div class="flex items-center gap-3">
            <svg class="size-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span>Backup se kreira, molimo pričekajte...</span>
        </div>
    </div>

    <!-- Statistics -->
    @if (count($backups) > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3">
                        <svg class="size-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ count($backups) }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Ukupno backupova</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-green-100 dark:bg-green-900 p-3">
                        <svg class="size-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ $this->formatBytes(collect($backups)->sum('size_bytes')) }}
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Ukupna veličina</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900 p-3">
                        <svg class="size-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ $backups[0]['formatted_date'] ?? 'N/A' }}
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Zadnji backup</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Backups Table - Desktop -->
    <div
        class="hidden lg:block bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        @if (count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Naziv
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Datum kreiranja
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Veličina
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Akcije
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($backups as $backup)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    <div class="flex items-center gap-2">
                                        <svg class="size-5 text-zinc-400" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                        {{ $backup['name'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $backup['formatted_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <flux:button wire:click="downloadBackup('{{ $backup['path'] }}')"
                                            variant="outline" size="sm" icon="arrow-down-tray">
                                            Preuzmi
                                        </flux:button>
                                        <flux:button wire:click="deleteBackup('{{ $backup['path'] }}')"
                                            wire:confirm="Jeste li sigurni da želite obrisati ovaj backup?"
                                            variant="danger" size="sm" icon="trash">
                                            Obriši
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto size-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">Nema backupova</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Započnite kreiranjem prvog backupa.</p>
            </div>
        @endif
    </div>

    <!-- Backups Cards - Mobile -->
    <div class="lg:hidden space-y-4">
        @forelse($backups as $backup)
            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="size-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            <h3 class="font-medium text-zinc-900 dark:text-white text-sm break-all">
                                {{ $backup['name'] }}</h3>
                        </div>
                        <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <div class="flex items-center gap-2">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $backup['formatted_date'] }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                </svg>
                                {{ $backup['size'] }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="downloadBackup('{{ $backup['path'] }}')" variant="outline"
                        size="sm" icon="arrow-down-tray" class="flex-1">
                        Preuzmi
                    </flux:button>
                    <flux:button wire:click="deleteBackup('{{ $backup['path'] }}')"
                        wire:confirm="Jeste li sigurni da želite obrisati ovaj backup?" variant="danger"
                        size="sm" icon="trash" class="flex-1">
                        Obriši
                    </flux:button>
                </div>
            </div>
        @empty
            <div
                class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                <svg class="mx-auto size-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">Nema backupova</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Započnite kreiranjem prvog backupa.</p>
            </div>
        @endforelse
    </div>
</div>
