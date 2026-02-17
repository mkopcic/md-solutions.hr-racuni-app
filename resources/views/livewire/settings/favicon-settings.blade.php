<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Favicon')" :subheading="__('Update your website favicon images')">
        <form wire:submit="updateFavicons" class="my-6 w-full space-y-6">

            <!-- Current Favicons Display -->
            <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Current Favicons') }}</flux:heading>

                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Favicon ICO -->
                    <div class="text-center">
                        @if(file_exists(public_path('favicon.ico')))
                            <img src="{{ asset('favicon.ico') }}?v={{ time() }}" alt="Favicon ICO" class="mx-auto mb-2 size-16">
                            <flux:text size="sm">favicon.ico</flux:text>
                            <flux:badge color="green" size="sm">{{ __('Exists') }}</flux:badge>
                        @else
                            <div class="mx-auto mb-2 flex size-16 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="photo" class="size-8 text-zinc-400" />
                            </div>
                            <flux:text size="sm">favicon.ico</flux:text>
                            <flux:badge color="zinc" size="sm">{{ __('Not uploaded') }}</flux:badge>
                        @endif
                    </div>

                    <!-- Favicon SVG -->
                    <div class="text-center">
                        @if(file_exists(public_path('favicon.svg')))
                            <img src="{{ asset('favicon.svg') }}?v={{ time() }}" alt="Favicon SVG" class="mx-auto mb-2 size-16">
                            <flux:text size="sm">favicon.svg</flux:text>
                            <flux:badge color="green" size="sm">{{ __('Exists') }}</flux:badge>
                        @else
                            <div class="mx-auto mb-2 flex size-16 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="photo" class="size-8 text-zinc-400" />
                            </div>
                            <flux:text size="sm">favicon.svg</flux:text>
                            <flux:badge color="zinc" size="sm">{{ __('Not uploaded') }}</flux:badge>
                        @endif
                    </div>

                    <!-- Apple Touch Icon -->
                    <div class="text-center">
                        @if(file_exists(public_path('apple-touch-icon.png')))
                            <img src="{{ asset('apple-touch-icon.png') }}?v={{ time() }}" alt="Apple Touch Icon" class="mx-auto mb-2 size-16 rounded-lg">
                            <flux:text size="sm">apple-touch-icon.png</flux:text>
                            <flux:badge color="green" size="sm">{{ __('Exists') }}</flux:badge>
                        @else
                            <div class="mx-auto mb-2 flex size-16 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="photo" class="size-8 text-zinc-400" />
                            </div>
                            <flux:text size="sm">apple-touch-icon.png</flux:text>
                            <flux:badge color="zinc" size="sm">{{ __('Not uploaded') }}</flux:badge>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Upload New Favicons -->
            <div class="space-y-4">
                <flux:heading size="sm">{{ __('Upload New Favicons') }}</flux:heading>

                <!-- Favicon ICO Upload -->
                <div>
                    <flux:field>
                        <flux:label>{{ __('Favicon ICO') }}</flux:label>
                        <flux:description>{{ __('Upload a .ico file (max 1MB)') }}</flux:description>
                        <flux:input type="file" wire:model="favicon_ico" accept=".ico" />
                        <flux:error name="favicon_ico" />
                    </flux:field>
                    @if ($favicon_ico)
                        <flux:text size="sm" class="mt-2">{{ __('Selected:') }} {{ $favicon_ico->getClientOriginalName() }}</flux:text>
                    @endif
                </div>

                <!-- Favicon SVG Upload -->
                <div>
                    <flux:field>
                        <flux:label>{{ __('Favicon SVG') }}</flux:label>
                        <flux:description>{{ __('Upload a .svg file (max 1MB)') }}</flux:description>
                        <flux:input type="file" wire:model="favicon_svg" accept=".svg" />
                        <flux:error name="favicon_svg" />
                    </flux:field>
                    @if ($favicon_svg)
                        <flux:text size="sm" class="mt-2">{{ __('Selected:') }} {{ $favicon_svg->getClientOriginalName() }}</flux:text>
                    @endif
                </div>

                <!-- Apple Touch Icon Upload -->
                <div>
                    <flux:field>
                        <flux:label>{{ __('Apple Touch Icon') }}</flux:label>
                        <flux:description>{{ __('Upload a .png file (recommended 180x180px, max 1MB)') }}</flux:description>
                        <flux:input type="file" wire:model="apple_touch_icon" accept=".png" />
                        <flux:error name="apple_touch_icon" />
                    </flux:field>
                    @if ($apple_touch_icon)
                        <flux:text size="sm" class="mt-2">{{ __('Selected:') }} {{ $apple_touch_icon->getClientOriginalName() }}</flux:text>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Upload Favicons') }}</span>
                    <span wire:loading>{{ __('Uploading...') }}</span>
                </flux:button>

                <x-action-message class="me-3" on="favicon-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
