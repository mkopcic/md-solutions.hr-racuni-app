<x-layouts.public>

    <section class="bg-gradient-to-b from-blue-50 to-white py-14 dark:from-zinc-900 dark:to-zinc-800">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Natrag na naslovnicu
                </a>
            </div>
            <h1 class="mt-4 text-3xl font-extrabold text-zinc-900 dark:text-white sm:text-4xl">Politika privatnosti</h1>
            <p class="mt-2 text-zinc-500 dark:text-zinc-400">Zadnja izmjena: {{ now()->format('d.m.Y') }}</p>
        </div>
    </section>

    <section class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-8 text-zinc-700 dark:text-zinc-300">

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">1. Uvod</h2>
                    <p class="text-sm leading-relaxed">
                        Ova Politika privatnosti opisuje kako prikupljamo, koristimo i štitimo osobne podatke korisnika aplikacije. Poštujemo vašu privatnost i posvećeni smo zaštiti vaših osobnih podataka u skladu s Općom uredbom o zaštiti podataka (GDPR) i važećim zakonodavstvom Republike Hrvatske.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">2. Podaci koje prikupljamo</h2>
                    <p class="mb-3 text-sm leading-relaxed">Prikupljamo sljedeće kategorije podataka:</p>
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Podaci o računu: ime, e-mail adresa, lozinka (pohranjena u hashiranom obliku)</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Poslovni podaci: naziv obrta, OIB, IBAN, adresa, podaci o kupcima</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Transakcijski podaci: računi, ponude, stavke, iznosi, datumi plaćanja</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Tehnički podaci: logovi aktivnosti, IP adrese, podaci o sesiji</span></li>
                    </ul>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">3. Svrha obrade podataka</h2>
                    <p class="mb-3 text-sm leading-relaxed">Vaše podatke koristimo isključivo za:</p>
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Pružanje funkcionalnosti aplikacije (računi, KPR, e-Račun)</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Autentikaciju i sigurnost korisničkog računa</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Ispunjavanje zakonskih obveza (fiskalizacija, e-Račun FINA)</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Poboljšanje kvalitete usluge</span></li>
                    </ul>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">4. Pohrana i sigurnost</h2>
                    <p class="text-sm leading-relaxed">
                        Svi podaci pohranjuju se na sigurnim serverima. Koristimo enkripciju, hashiranje lozinki i redovite sigurnosne sigurnosne kopije. Pristup podacima ograničen je isključivo na autorizirane korisnike. Certifikati za e-Račun pohranjena su u enkriptiranom obliku.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">5. Dijeljenje podataka s trećim stranama</h2>
                    <p class="text-sm leading-relaxed">
                        Vaše podatke ne prodajemo niti dijelimo s trećim stranama u komercijalne svrhe. Podaci se prenose FINA-i isključivo u sklopu zakonski propisanog procesa e-Račun B2B sustava, sukladno važećim propisima.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">6. Vaša prava</h2>
                    <p class="mb-3 text-sm leading-relaxed">Sukladno GDPR-u, imate pravo na:</p>
                    <ul class="space-y-1 text-sm">
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Pristup vašim osobnim podacima</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Ispravak netočnih podataka</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Brisanje podataka ("pravo na zaborav")</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Prenosivost podataka</span></li>
                        <li class="flex items-start gap-2"><i class="fas fa-circle-dot mt-1 text-xs text-blue-500"></i> <span>Prigovor na obradu podataka</span></li>
                    </ul>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">7. Kolačići (Cookies)</h2>
                    <p class="text-sm leading-relaxed">
                        Aplikacija koristi isključivo funkcionalne kolačiće neophodne za rad sesije i autentikaciju. Ne koristimo kolačiće za praćenje ili reklamne svrhe.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">8. Kontakt</h2>
                    <p class="text-sm leading-relaxed">
                        Za sva pitanja, zahtjeve ili pritužbe vezane uz obradu osobnih podataka, kontaktirajte administratora sustava. Imate pravo podnijeti pritužbu Agenciji za zaštitu osobnih podataka (AZOP).
                    </p>
                </div>

            </div>
        </div>
    </section>

</x-layouts.public>
