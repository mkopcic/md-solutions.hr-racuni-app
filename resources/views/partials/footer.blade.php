<footer class="border-t border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">

            {{-- Brand --}}
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <x-app-logo />
                </a>
                <p class="mt-3 text-sm text-gray-500 dark:text-zinc-400">
                    Aplikacija za obrtnike — izdavanje računa, knjiga prometa i e-Račun B2B sustav.
                </p>
            </div>

            {{-- Navigacija --}}
            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-zinc-500">Navigacija</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400">
                            Naslovna
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400">
                            Prijava
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Pravni --}}
            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-zinc-500">Pravno</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('terms') }}" class="text-gray-600 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400">
                            Pravila korištenja
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400">
                            Politika privatnosti
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="mt-8 border-t border-gray-200 pt-6 dark:border-zinc-700">
            <p class="text-center text-xs text-gray-400 dark:text-zinc-500">
                &copy; {{ now()->year }} MD Solutions. Sva prava pridržana.
            </p>
        </div>
    </div>
</footer>
