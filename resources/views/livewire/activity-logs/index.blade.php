<div class="space-y-6">
    <flux:header>
        <flux:heading size="xl">Activity Logs</flux:heading>
        <flux:subheading>Pregled svih aktivnosti u sustavu</flux:subheading>
    </flux:header>

    <!-- Activity Details Dialog -->
    @if($showModal && $selectedActivity)
        <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Detalji aktivnosti</h3>
                <button
                    wire:click="closeModal"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                    aria-label="Zatvori"
                >
                    <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Datum</div>
                        <div class="text-zinc-900 dark:text-zinc-100">{{ $selectedActivity->created_at->format('d.m.Y H:i:s') }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Tip loga</div>
                        @php
                            $colors = [
                                'customers' => 'blue',
                                'invoices' => 'green',
                                'authentication' => 'purple',
                                'http_requests' => 'orange',
                                'livewire_actions' => 'pink',
                                'business' => 'indigo',
                                'services' => 'teal',
                                'users' => 'red'
                            ];
                            $color = $colors[$selectedActivity->log_name] ?? 'gray';
                        @endphp
                        <flux:badge color="{{ $color }}" size="sm">
                            {{ ucfirst(str_replace('_', ' ', $selectedActivity->log_name)) }}
                        </flux:badge>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Event</div>
                        <div class="text-zinc-900 dark:text-zinc-100">{{ $selectedActivity->event ?? 'N/A' }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Model</div>
                        <div class="text-zinc-900 dark:text-zinc-100">{{ $selectedActivity->subject_type ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="space-y-4">
                    @if($selectedActivity->causer)
                        <div>
                            <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Korisnik</div>
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-md p-3">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $selectedActivity->causer->name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $selectedActivity->causer->email }}</div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Opis</div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-md p-3">
                            <div class="text-zinc-900 dark:text-zinc-100">{{ $selectedActivity->description }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($selectedActivity->properties->isNotEmpty() || $selectedActivity->subject)
                <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    @if($selectedActivity->subject)
                        <div class="mb-6">
                            <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Subject Model</div>
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-md p-3">
                                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                    <strong>ID:</strong> {{ $selectedActivity->subject_id }}<br>
                                    <strong>Type:</strong> {{ $selectedActivity->subject_type }}
                                    @if($selectedActivity->subject && method_exists($selectedActivity->subject, 'toArray'))
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">Prikaži podatke</summary>
                                            <pre class="mt-2 text-xs bg-zinc-100 dark:bg-zinc-900 p-2 rounded overflow-auto">{{ json_encode($selectedActivity->subject->toArray(), JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($selectedActivity->properties->isNotEmpty())
                        <div>
                            <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Properties</div>
                            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-md p-3">
                                <details>
                                    <summary class="cursor-pointer text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">Prikaži svojstva</summary>
                                    <pre class="mt-2 text-xs bg-zinc-100 dark:bg-zinc-900 p-2 rounded overflow-auto">{{ json_encode($selectedActivity->properties, JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
        <flux:heading size="sm" class="mb-4">Filteri</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="xl:col-span-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Pretraži opis, model ili korisnika..."
                    icon="magnifying-glass"
                />
            </div>

            <!-- Log Name Filter -->
            <div>
                <flux:select wire:model.live="logName">
                    <option value="">Svi logovi</option>
                    @foreach($availableLogNames as $name)
                        <option value="{{ $name }}">{{ ucfirst(str_replace('_', ' ', $name)) }}</option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Event Filter -->
            <div>
                <flux:select wire:model.live="event">
                    <option value="">Svi eventi</option>
                    @foreach($availableEvents as $eventName)
                        <option value="{{ $eventName }}">{{ ucfirst($eventName) }}</option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Date From -->
            <div>
                <flux:input
                    wire:model.live="dateFrom"
                    type="date"
                    placeholder="Od datuma"
                />
            </div>

            <!-- Date To -->
            <div>
                <flux:input
                    wire:model.live="dateTo"
                    type="date"
                    placeholder="Do datuma"
                />
            </div>
        </div>

        <div class="flex justify-between items-center mt-4">
            <div class="flex gap-2">
                <button
                    wire:click="clearFilters"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Očisti filtere
                </button>
            </div>

            <!-- Per Page -->
            <div class="flex items-center gap-2">
                <flux:text size="sm">Po stranici:</flux:text>
                <flux:select wire:model.live="perPage" class="w-20">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Activity Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Datum/Vrijeme
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Opis
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Korisnik
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Tip
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Event
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Detalji
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                <div>
                                    <div class="font-medium">{{ $activity->created_at->format('d.m.Y') }}</div>
                                    <div class="text-zinc-500 dark:text-zinc-400">{{ $activity->created_at->format('H:i:s') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-100">
                                <div class="max-w-xs">
                                    {{ $activity->description }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                @if($activity->causer)
                                    <div>
                                        <div class="font-medium">{{ $activity->causer->name }}</div>
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ $activity->causer->email }}</div>
                                    </div>
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400">System</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $colors = [
                                        'customers' => 'blue',
                                        'invoices' => 'green',
                                        'authentication' => 'purple',
                                        'http_requests' => 'orange',
                                        'livewire_actions' => 'pink',
                                        'business' => 'indigo',
                                        'services' => 'teal',
                                        'users' => 'red'
                                    ];
                                    $color = $colors[$activity->log_name] ?? 'gray';
                                @endphp
                                <flux:badge color="{{ $color }}" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($activity->event)
                                    @php
                                        $eventColors = [
                                            'created' => 'green',
                                            'updated' => 'blue',
                                            'deleted' => 'red',
                                            'login' => 'purple',
                                            'logout' => 'gray'
                                        ];
                                        $eventColor = $eventColors[$activity->event] ?? 'gray';
                                    @endphp
                                    <flux:badge color="{{ $eventColor }}" size="sm" variant="outline">
                                        {{ ucfirst($activity->event) }}
                                    </flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($activity->properties->isNotEmpty() || $activity->subject || $activity->causer)
                                    <button
                                        wire:click="showDetails({{ $activity->id }})"
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Detalji
                                    </button>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                <flux:icon.document-text class="mx-auto size-12 text-zinc-300 dark:text-zinc-600 mb-4" />
                                <div class="text-lg font-medium mb-2">Nema pronađenih aktivnosti</div>
                                <div>Pokušajte promijeniti filtere ili dodati neku aktivnost</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
