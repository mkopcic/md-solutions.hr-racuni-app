<x-layouts.auth.card>
    <x-slot:title>
        Registracija onemogućena
    </x-slot:title>

    <div class="text-center space-y-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
            <svg class="mx-auto h-12 w-12 text-yellow-600 dark:text-yellow-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>

            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Registracija trenutno nije dostupna
            </h2>

            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Novi korisnički računi mogu biti kreirani samo od strane administratora.
            </p>

            <flux:button variant="primary" href="{{ route('login') }}">
                Prijava
            </flux:button>
        </div>
    </div>
</x-layouts.auth.card>
