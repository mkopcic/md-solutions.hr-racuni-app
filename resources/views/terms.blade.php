<x-layouts.public>

    <section class="bg-gradient-to-b from-blue-50 to-white py-14 dark:from-zinc-900 dark:to-zinc-800">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Natrag na naslovnicu
                </a>
            </div>
            <h1 class="mt-4 text-3xl font-extrabold text-zinc-900 dark:text-white sm:text-4xl">Pravila korištenja</h1>
            <p class="mt-2 text-zinc-500 dark:text-zinc-400">Zadnja izmjena: {{ now()->format('d.m.Y') }}</p>
        </div>
    </section>

    <section class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-8 text-zinc-700 dark:text-zinc-300">

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">1. Prihvaćanje uvjeta</h2>
                    <p class="text-sm leading-relaxed">
                        Korištenjem ove aplikacije prihvaćate ova Pravila korištenja u cijelosti. Ako se ne slažete s bilo kojim dijelom ovih pravila, molimo vas da prestanete koristiti aplikaciju.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">2. Opis usluge</h2>
                    <p class="text-sm leading-relaxed">
                        Aplikacija je namijenjena paušalnim obrtnicima za digitalno upravljanje računima, ponudama, kupcima, knjigom prometa i e-Računima putem FINA B2B sustava. Aplikacija nije zamjena za profesionalne računovodstvene ili pravne usluge.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">3. Korisnički račun</h2>
                    <p class="text-sm leading-relaxed">
                        Pristup aplikaciji omogućen je isključivo autoriziranim korisnicima. Korisnik je odgovoran za čuvanje lozinke i svih aktivnosti koje se odvijaju putem njegova računa. U slučaju sumnje na neovlašteni pristup, korisnik je dužan odmah obavijestiti administratora.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">4. Unos podataka i točnost</h2>
                    <p class="text-sm leading-relaxed">
                        Korisnik je u potpunosti odgovoran za točnost svih podataka koje unosi u aplikaciju — OIB-ove, iznose, datume i ostale poslovne informacije. Aplikacija ne provjerava valjanost unesenih podataka s nadležnim tijelima.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">5. Zabrana zlouporabe</h2>
                    <p class="text-sm leading-relaxed">
                        Zabranjeno je koristiti aplikaciju za protuzakonite aktivnosti, unos lažnih podataka ili pokušaje neovlaštenog pristupa sustavu. Kršenje ovih pravila može rezultirati trenutnim ukidanjem pristupa.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">6. Ograničenje odgovornosti</h2>
                    <p class="text-sm leading-relaxed">
                        Aplikacija se pruža "kakva jest". Ne jamčimo neprekidan rad niti odgovaramo za gubitak podataka ili poslovnu štetu nastalu korištenjem aplikacije. Preporučujemo redovito sigurnosno kopiranje važnih podataka.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">7. Izmjene pravila</h2>
                    <p class="text-sm leading-relaxed">
                        Zadržavamo pravo izmjene ovih Pravila korištenja u bilo kojem trenutku. Nastavak korištenja aplikacije nakon objave izmjena smatra se prihvaćanjem novih uvjeta.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">8. Kontakt</h2>
                    <p class="text-sm leading-relaxed">
                        Za sva pitanja vezana uz ova Pravila korištenja, slobodno nas kontaktirajte putem administratora sustava.
                    </p>
                </div>

            </div>
        </div>
    </section>

</x-layouts.public>
