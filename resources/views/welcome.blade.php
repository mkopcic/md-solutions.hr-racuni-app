<x-layouts.public>
    <div class="min-h-screen flex flex-col justify-between">
        <div>
            <div class="text-center py-4">
                <h1 class="text-4xl md:text-5xl font-extrabold text-blue-700 dark:text-blue-400 mb-2">
                    Aplikacija za obrtnike i fakture
                </h1>
                <p class="text-lg md:text-xl text-gray-700 dark:text-gray-300 mb-4">
                    Jednostavno izdavanje računa, vođenje knjige prometa i pregled poslovanja.
                </p>

                <div class="mt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white font-semibold rounded shadow hover:bg-blue-700 dark:hover:bg-blue-600 transition">
                            <i class="fas fa-columns mr-2"></i> Idi na Dashboard
                        </a>
                    @else
                        <div class="flex justify-center">
                            <a href="{{ route('login') }}" class="inline-block px-6 py-2 bg-blue-500 dark:bg-blue-600 text-white font-medium rounded">
                                <i class="fas fa-sign-in-alt"></i> Prijava
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

            <div class="mt-2">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-1 text-gray-800 dark:text-gray-200">Mogućnosti aplikacije</h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-3">Otkrijte sve što naša aplikacija nudi za jednostavnije i učinkovitije vođenje Vašeg obrta</p>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Card 1 - Računi -->
                        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 p-3 text-center">
                            <div class="mb-2">
                                <i class="fas fa-file-invoice text-blue-600 dark:text-blue-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Izrada računa</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Jednostavno izdavanje računa s automatskim numeriranjem, dodavanjem stavki i generiranjem PDF-a.
                            </p>
                        </div>

                        <!-- Card 2 - KPR -->
                        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 p-3 text-center">
                            <div class="mb-2">
                                <i class="fas fa-chart-line text-green-600 dark:text-green-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Knjiga prometa</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Automatsko generiranje KPR zapisa iz računa s pregledom mjesečnih i godišnjih prihoda.
                            </p>
                        </div>

                        <!-- Card 3 - Kupci -->
                        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 p-3 text-center">
                            <div class="mb-2">
                                <i class="fas fa-users text-purple-600 dark:text-purple-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Upravljanje kupcima</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Dodavanje, uređivanje i pregled kupaca s jednostavnim pretraživanjem po nazivu ili OIB-u.
                            </p>
                        </div>

                        <!-- Card 4 - Obrt -->
                        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 p-3 text-center">
                            <div class="mb-2">
                                <i class="fas fa-building text-yellow-600 dark:text-yellow-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Podaci o obrtu</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Unos i ažuriranje svih potrebnih podataka za pravilan prikaz na računima i dokumentima.
                            </p>
                        </div>

                        <!-- Card 5 - Porezni razredi -->
                        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 p-3 text-center">
                            <div class="mb-2">
                                <i class="fas fa-percent text-red-600 dark:text-red-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Porezni razredi</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Upravljanje poreznim razredima za paušalno oporezivanje i pregled poreznih obveza.
                            </p>
                        </div>

                        <!-- Card 6 - Logovi -->
                        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 p-3 text-center">
                            <div class="mb-2">
                                <i class="fas fa-file-alt text-indigo-600 dark:text-indigo-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Praćenje logova</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Detaljni pregled aktivnosti i događaja u sustavu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center text-xs text-gray-500 dark:text-gray-400 py-4 border-t border-gray-200 dark:border-zinc-700">
            <p>&copy; {{ now()->year }} Računi Obrt. Sva prava pridržana.</p>
        </footer>
    </div>
</x-layouts.public>
