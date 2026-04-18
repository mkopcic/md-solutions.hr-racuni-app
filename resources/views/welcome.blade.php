<x-layouts.public>

    {{-- Hero --}}
    <section class="bg-gradient-to-b from-blue-50 to-white py-20 dark:from-zinc-900 dark:to-zinc-800">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                <i class="fas fa-file-invoice"></i>
                Digitalno računovodstvo za obrtnike
            </span>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl lg:text-6xl">
                Izdajte račune<br class="hidden sm:block">
                <span class="text-blue-600 dark:text-blue-400">brzo i jednostavno</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                Sve što trebate za vođenje paušalnog obrta na jednom mjestu — od izdavanja računa do e-Računa za B2B i knjige prometa.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fas fa-columns"></i>
                        Idi na Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fas fa-sign-in-alt"></i>
                        Prijava
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white sm:text-3xl">Mogućnosti aplikacije</h2>
                <p class="mt-2 text-zinc-500 dark:text-zinc-400">Sve što treba paušalnom obrtniku na jednom mjestu</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-900/20">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="mb-1 font-semibold text-zinc-900 dark:text-white">Izrada računa</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Automatsko numeriranje, dodavanje stavki, generiranje PDF-a i slanje e-mailom u par klikova.
                    </p>
                </div>

                <div class="rounded-xl border border-green-100 bg-green-50 p-6 dark:border-green-900 dark:bg-green-900/20">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-green-600 text-white">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="mb-1 font-semibold text-zinc-900 dark:text-white">Knjiga prometa</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Automatski generirani KPR zapisi iz računa s pregledom mjesečnih i godišnjih prihoda.
                    </p>
                </div>

                <div class="rounded-xl border border-purple-100 bg-purple-50 p-6 dark:border-purple-900 dark:bg-purple-900/20">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-600 text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1 font-semibold text-zinc-900 dark:text-white">Upravljanje kupcima</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Baza kupaca s pretraživanjem po OIB-u, pregledom ukupnih prihoda i izvozom podataka.
                    </p>
                </div>

                <div class="rounded-xl border border-amber-100 bg-amber-50 p-6 dark:border-amber-900 dark:bg-amber-900/20">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-amber-600 text-white">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="mb-1 font-semibold text-zinc-900 dark:text-white">Ponude</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Izrada ponuda s praćenjem statusa — od nacrta i slanja do prihvaćanja ili odbijanja.
                    </p>
                </div>

                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-6 dark:border-indigo-900 dark:bg-indigo-900/20">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3 class="mb-1 font-semibold text-zinc-900 dark:text-white">e-Račun B2B</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Slanje i primanje e-Računa putem FINA-e s automatskim praćenjem statusa i logovima.
                    </p>
                </div>

                <div class="rounded-xl border border-red-100 bg-red-50 p-6 dark:border-red-900 dark:bg-red-900/20">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-red-600 text-white">
                        <i class="fas fa-percent"></i>
                    </div>
                    <h3 class="mb-1 font-semibold text-zinc-900 dark:text-white">Porezni razredi</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Pregled i upravljanje paušalnim poreznim razredima s kvartalnim iznosima i prireze.
                    </p>
                </div>
            </div>
        </div>
    </section>


</x-layouts.public>
