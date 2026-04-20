<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">e-Račun Postavke</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Konfiguracija FINA e-Račun B2B integracije</p>
        </div>
        <a href="{{ route('eracun.outgoing.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" wire:navigate>
            <i class="fas fa-arrow-left"></i>
            Natrag na e-Račune
        </a>
    </div>

    @if (session()->has('config_saved'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-800 dark:bg-green-900/20 dark:text-green-400">
            <i class="fas fa-check-circle mr-2"></i>{{ session('config_saved') }}
        </div>
    @endif

    @if (session()->has('cert_saved'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="mb-4 rounded-lg bg-green-100 p-4 text-green-800 dark:bg-green-900/20 dark:text-green-400">
            <i class="fas fa-check-circle mr-2"></i>{{ session('cert_saved') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- CARD 1: Konfiguracija integracije --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 text-lg font-semibold text-zinc-900 dark:text-white">
                <i class="fas fa-cog mr-2 text-blue-500"></i>Konfiguracija integracije
            </h2>
            <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">Postavke FINA e-Račun servisa i podataka dobavljača</p>

            <form wire:submit.prevent="saveConfig" class="space-y-4">

                {{-- Okolina --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Okolina</label>
                    <select wire:model="environment" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        <option value="demo">Demo (testiranje)</option>
                        <option value="production">Produkcija</option>
                    </select>
                </div>

                {{-- Demo URL --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Demo endpoint URL</label>
                    <input wire:model="demoUrl" type="text" placeholder="https://demo-eracun.fina.hr/..." class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                    @error('demoUrl') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-zinc-400">Ostavite prazno dok FINA ne pošalje URL nakon aktivacije OIB-a.</p>
                </div>

                <hr class="border-zinc-200 dark:border-zinc-700" />
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Podaci dobavljača (SupplierParty u UBL)</p>

                {{-- OIB --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">OIB</label>
                    <input wire:model="supplierOib" type="text" maxlength="11" placeholder="86058362621" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                    @error('supplierOib') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Naziv --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Naziv obrta / tvrtke</label>
                    <input wire:model="supplierName" type="text" placeholder="MD SOLUTIONS VL. MARINA KOPČIĆ" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                    @error('supplierName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Adresa --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Adresa</label>
                    <input wire:model="supplierAddress" type="text" placeholder="KARDINALA FRANJE ŠEFERA 29" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                    @error('supplierAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Grad + Poštanski --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Poštanski</label>
                        <input wire:model="supplierPostalCode" type="text" maxlength="10" placeholder="31431" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                        @error('supplierPostalCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Grad</label>
                        <input wire:model="supplierCity" type="text" placeholder="ČEPIN" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                        @error('supplierCity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- IBAN --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">IBAN</label>
                    <input wire:model="supplierIban" type="text" maxlength="21" placeholder="HR9023400091160578001" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-mono text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" />
                    @error('supplierIban') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900" wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveConfig"><i class="fas fa-save mr-2"></i>Spremi konfiguraciju</span>
                    <span wire:loading wire:target="saveConfig"><i class="fas fa-spinner fa-spin mr-2"></i>Spremanje...</span>
                </button>
            </form>
        </div>

        {{-- CARD 2: Certificate Manager --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-1 text-lg font-semibold text-zinc-900 dark:text-white">
                <i class="fas fa-certificate mr-2 text-amber-500"></i>Certificate Manager
            </h2>
            <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">Status demo certifikata za potpisivanje e-računa</p>

            @if ($certExists && $certInfo)
                {{-- Status indikator --}}
                @php
                    $statusColor = $certValid
                        ? ($certDaysLeft > 30 ? 'green' : 'amber')
                        : 'red';
                    $statusText = $certValid
                        ? ($certDaysLeft > 30 ? 'Validan' : 'Uskoro ističe')
                        : 'Nevažeći / istekao';
                @endphp

                <div class="mb-5 flex items-center gap-3 rounded-lg border border-{{ $statusColor }}-200 bg-{{ $statusColor }}-50 p-4 dark:border-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900/20">
                    <div class="flex-shrink-0">
                        <i class="fas fa-{{ $certValid ? 'shield-alt' : 'exclamation-triangle' }} text-2xl text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-{{ $statusColor }}-800 dark:text-{{ $statusColor }}-400">{{ $statusText }}</p>
                        @if ($certValid)
                            <p class="text-sm text-{{ $statusColor }}-700 dark:text-{{ $statusColor }}-500">Ističe za {{ $certDaysLeft }} dana ({{ $certInfo['valid_to'] }})</p>
                        @else
                            <p class="text-sm text-red-700 dark:text-red-500">Certifikat nije validan ili je istekao</p>
                        @endif
                    </div>
                </div>

                {{-- Detalji certifikata --}}
                <div class="mb-5 space-y-2 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Datoteka</span>
                        <span class="font-mono text-xs font-medium text-zinc-900 dark:text-white">{{ $certInfo['filename'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Subject CN</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $certInfo['subject_cn'] }}</span>
                    </div>
                    @if ($certInfo['subject_o'])
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Subject O</span>
                            <span class="text-right font-medium text-zinc-900 dark:text-white">{{ $certInfo['subject_o'] }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Issuer</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $certInfo['issuer_cn'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Serijski broj</span>
                        <span class="font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $certInfo['serial'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Validan od</span>
                        <span class="text-zinc-900 dark:text-white">{{ $certInfo['valid_from'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Validan do</span>
                        <span class="text-zinc-900 dark:text-white">{{ $certInfo['valid_to'] }}</span>
                    </div>
                    @if ($certInfo['fingerprint'])
                        <div class="border-t border-zinc-200 pt-2 dark:border-zinc-700">
                            <p class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">SHA1 Fingerprint</p>
                            <p class="break-all font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $certInfo['fingerprint'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- Zamijeni certifikat --}}
                <button wire:click="$set('showUploadForm', true)" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    <i class="fas fa-upload mr-2"></i>Zamijeni certifikat
                </button>

            @else
                {{-- Nema certifikata --}}
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-times-circle text-2xl text-red-500"></i>
                        <div>
                            <p class="font-semibold text-red-800 dark:text-red-400">Certifikat nije pronađen</p>
                            <p class="text-sm text-red-700 dark:text-red-500">Učitajte .p12 certifikat dobiven od FINA-e</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Upload forma --}}
            @if ($showUploadForm || !$certExists)
                <form wire:submit.prevent="uploadCertificate" class="mt-4 space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <i class="fas fa-upload mr-1 text-blue-500"></i>
                        Učitaj novi certifikat (.p12)
                    </p>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Certifikat (.p12)</label>
                        <input wire:model="p12File" type="file" accept=".p12,.pfx" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" />
                        @error('p12File') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password certifikata</label>
                        <input wire:model="p12Password" type="password" placeholder="Ostavite prazno ako nema passworda" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500" autocomplete="off" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" wire:loading.attr="disabled" wire:loading.class="opacity-70">
                            <span wire:loading.remove wire:target="uploadCertificate"><i class="fas fa-upload mr-1"></i>Učitaj i spremi</span>
                            <span wire:loading wire:target="uploadCertificate"><i class="fas fa-spinner fa-spin mr-1"></i>Obrađivanje...</span>
                        </button>
                        @if ($certExists)
                            <button type="button" wire:click="$set('showUploadForm', false)" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                Odustani
                            </button>
                        @endif
                    </div>
                </form>
            @endif
        </div>

    </div>
</div>
