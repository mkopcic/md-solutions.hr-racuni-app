<div x-data="{ customerDialog: false, customerViewDialog: false }">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Kupci</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Upravljanje kupcima i klijentima</p>
        </div>
        <button wire:click="create" @click="customerDialog = true"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-user-plus"></i>
            Dodaj kupca
        </button>
    </div>

    <!-- Stats -->
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Ukupno kupaca</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">S računima</p>
            <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['with_invoices'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Bez računa</p>
            <p class="mt-1 text-2xl font-bold text-zinc-400 dark:text-zinc-500">{{ $stats['without_invoices'] }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-900/20">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">Gradovi</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['cities'] }}</p>
        </div>
    </div>

    <!-- Tab navigacija -->
    <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('list')"
                class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors {{ $activeTab === 'list' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }}">
                <i class="fas fa-list mr-2"></i>
                Lista kupaca
            </button>
            <button wire:click="switchTab('report')"
                class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors {{ $activeTab === 'report' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }}">
                <i class="fas fa-chart-bar mr-2"></i>
                Izvještaj po kupcima
            </button>
        </nav>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($activeTab === 'list')
    <!-- Toolbar -->
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-zinc-400"></i>
            </div>
            <input type="text" wire:model.live="search" placeholder="Pretraži po nazivu, OIB-u ili gradu..."
                class="w-full rounded-lg border border-zinc-300 bg-white py-2.5 pl-10 pr-4 text-sm text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400" />
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <button wire:click="exportExcel" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <i class="fas fa-file-excel text-green-600"></i>
                Excel
            </button>
            <button wire:click="exportCsv" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <i class="fas fa-file-csv text-orange-600"></i>
                CSV
            </button>
        </div>
    </div>

    <!-- Card Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($customers as $customer)
            <div wire:key="customer-{{ $customer->id }}" class="flex flex-col rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Card header -->
                <div class="flex items-start justify-between p-4 pb-2">
                    <h3 class="min-w-0 flex-1 pr-2 text-sm font-semibold leading-snug text-zinc-900 dark:text-white" title="{{ $customer->name }}">
                        {{ Str::limit($customer->name, 45) }}
                    </h3>
                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $customer->invoices_count > 0 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        <i class="fas fa-file-invoice text-xs"></i>
                        {{ $customer->invoices_count }}
                    </span>
                </div>

                <!-- Info -->
                <div class="flex-1 space-y-1.5 px-4 pb-4 pt-1">
                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-id-card w-3 shrink-0"></i>
                        <span class="font-mono">{{ $customer->oib }}</span>
                    </div>
                    <div class="flex items-start gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-map-marker-alt w-3 shrink-0 mt-0.5"></i>
                        <span>{{ $customer->address }}, {{ $customer->city }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-1 border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                    <button wire:click="view({{ $customer->id }})" @click="customerViewDialog = true"
                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20">
                        <i class="fas fa-eye"></i>
                        Pregled
                    </button>
                    <button wire:click="edit({{ $customer->id }})" @click="customerDialog = true"
                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                        <i class="fas fa-edit"></i>
                        Uredi
                    </button>
                    <button wire:click="delete({{ $customer->id }})"
                        wire:confirm="Jeste li sigurni da želite obrisati ovog kupca?"
                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <i class="fas fa-trash"></i>
                        Obriši
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <i class="fas fa-users mb-3 text-4xl text-zinc-300 dark:text-zinc-600"></i>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nema pronađenih kupaca.</p>
                @if($search)
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Pokušajte s drugim pojmom pretrage.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $customers->links() }}
    </div>

    @elseif($activeTab === 'report')
    <!-- Izvještaj po kupcima -->

    <!-- Export Radnje -->
    <div class="mb-4 flex items-center gap-2">
        <button wire:click="exportReportExcel"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-excel"></i>
            Izvezi Excel
        </button>
        <button wire:click="exportReportCsv"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <i class="fas fa-file-csv"></i>
            Izvezi CSV
        </button>
    </div>

    <!-- Search polje za izvještaj -->
    <div class="mb-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-zinc-500"></i>
            </div>
            <input type="text" wire:model.live="reportSearch" placeholder="Pretraži kupce..."
                class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 pl-10 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
            <div class="grid w-full max-w-xl grid-cols-2 gap-4">
                <div>
                    <label for="reportYear" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-calendar-alt"></i>
                        Godina
                    </label>
                    <select wire:model.live="reportYear" id="reportYear"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="all">Sve godine</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="reportMonth" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-calendar"></i>
                        Mjesec
                    </label>
                    <select wire:model.live="reportMonth" id="reportMonth"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="">Svi mjeseci</option>
                        @foreach($months as $key => $monthName)
                            <option value="{{ $key }}">{{ $monthName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid w-full grid-cols-2 gap-4 md:max-w-md">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                        <i class="fas fa-euro-sign"></i>
                        Ukupan prihod
                    </h3>
                    <p class="text-xl font-bold text-blue-900 dark:text-blue-200">{{ number_format($totalRevenue, 2, ',', '.') }} €</p>
                </div>
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">
                        <i class="fas fa-users"></i>
                        Aktivni kupci
                    </h3>
                    <p class="text-xl font-bold text-green-900 dark:text-green-200">{{ $activeCustomersCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop tablica - izvještaj -->
    <div class="hidden lg:block overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-user"></i>
                        Kupac
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-id-card"></i>
                        OIB
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-map-marker-alt"></i>
                        Grad
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-file-invoice"></i>
                        Broj računa
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-euro-sign"></i>
                        Ukupan prihod
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse ($customers as $customer)
                    <tr class="{{ $customer->total_revenue == 0 ? 'opacity-60' : '' }}">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $customer->name }}
                            @if($customer->invoices_count == 0)
                                <span class="ml-2 text-xs text-zinc-400 dark:text-zinc-500">(nema računa)</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->oib }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->city }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                            <span
                                class="inline-flex items-center rounded-full {{ $customer->invoices_count > 0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500' }} px-3 py-1 text-xs font-medium">
                                {{ $customer->invoices_count }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold">
                            @if($customer->total_revenue > 0)
                                <span class="text-green-600 dark:text-green-400">
                                    {{ number_format($customer->total_revenue, 2, ',', '.') }} €
                                </span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">
                                    0,00 €
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-chart-bar text-3xl text-zinc-300 dark:text-zinc-600 mb-2"></i>
                            <p>Nema podataka za odabrani period.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobilni prikaz kartica - izvještaj -->
    <div class="lg:hidden space-y-4">
        @forelse ($customers as $customer)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 {{ $customer->total_revenue == 0 ? 'opacity-60' : '' }}">
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $customer->name }}
                            @if($customer->invoices_count == 0)
                                <span class="ml-2 text-xs font-normal text-zinc-400 dark:text-zinc-500">(nema računa)</span>
                            @endif
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">OIB: {{ $customer->oib }}</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm mb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-city text-zinc-400"></i>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $customer->city }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                            <i class="fas fa-file-invoice"></i>
                            Broj računa
                        </p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $customer->invoices_count }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                            <i class="fas fa-euro-sign"></i>
                            Ukupan prihod
                        </p>
                        @if($customer->total_revenue > 0)
                            <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                                {{ number_format($customer->total_revenue, 2, ',', '.') }} €
                            </p>
                        @else
                            <p class="text-lg font-semibold text-zinc-400 dark:text-zinc-600">
                                0,00 €
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <i class="fas fa-chart-bar text-4xl text-zinc-300 dark:text-zinc-600 mb-3"></i>
                <p class="text-zinc-500 dark:text-zinc-400">Nema podataka za odabrani period.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
    @endif

    <!-- Customer Dialog -->
    <div x-show="customerDialog" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4" style="display: none;"
        @keydown.escape="customerDialog = false" @click.self="customerDialog = false">

        <div class="bg-white dark:bg-zinc-800 rounded-lg p-8 shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                    @if ($editingCustomerId)
                        <i class="fas fa-edit"></i>
                        Uredi kupca
                    @else
                        <i class="fas fa-plus"></i>
                        Dodaj novog kupca
                    @endif
                </h3>
                <button @click="customerDialog = false"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit="save">
                <div class="mb-4">
                    <label for="name" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-user"></i>
                        Naziv
                    </label>
                    <input type="text" wire:model="name" id="name"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Naziv kupca">
                    @error('name')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="oib" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-id-card"></i>
                        OIB
                    </label>
                    <input type="text" wire:model="oib" id="oib"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="OIB kupca">
                    @error('oib')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="address" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresa
                    </label>
                    <input type="text" wire:model="address" id="address"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Adresa kupca">
                    @error('address')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="city" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-city"></i>
                        Grad
                    </label>
                    <input type="text" wire:model="city" id="city"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-zinc-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="Grad">
                    @error('city')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeDialog" @click="customerDialog = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="fas fa-times"></i>
                        Odustani
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fas fa-save"></i>
                        Spremi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer View Dialog (Read-only) -->
    <div x-show="customerViewDialog" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4" style="display: none;"
        @keydown.escape="customerViewDialog = false" @click.self="customerViewDialog = false">

        <div class="bg-white dark:bg-zinc-800 rounded-lg p-8 shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                    <i class="fas fa-eye"></i>
                    Pregled kupca
                </h3>
                <button @click="customerViewDialog = false"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-user"></i>
                        Naziv
                    </label>
                    <p class="text-base font-medium text-zinc-900 dark:text-white">{{ $name }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-id-card"></i>
                        OIB
                    </label>
                    <p class="text-base font-medium text-zinc-900 dark:text-white">{{ $oib }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresa
                    </label>
                    <p class="text-base text-zinc-700 dark:text-zinc-300">{{ $address }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-city"></i>
                        Grad
                    </label>
                    <p class="text-base text-zinc-700 dark:text-zinc-300">{{ $city }}</p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <i class="fas fa-file-invoice"></i>
                        Broj računa
                    </label>
                    <p class="text-base font-semibold text-blue-600 dark:text-blue-400">
                        {{ $viewingCustomer?->invoices_count ?? 0 }}</p>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <button type="button" @click="customerViewDialog = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="fas fa-times"></i>
                        Zatvori
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Listen for successful form save to close dialog
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-customer-dialog', () => {
                console.log('Livewire signaled to close edit dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerDialog = false;
                }
            });

            Livewire.on('open-customer-dialog', () => {
                console.log('Livewire signaled to open edit dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerDialog = true;
                }
            });

            Livewire.on('close-customer-view-dialog', () => {
                console.log('Livewire signaled to close view dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerViewDialog = false;
                }
            });

            Livewire.on('open-customer-view-dialog', () => {
                console.log('Livewire signaled to open view dialog');
                const rootElement = document.querySelector('[x-data]');
                if (rootElement && rootElement.__x) {
                    rootElement.__x.$data.customerViewDialog = true;
                }
            });
        });
    </script>
</div>
