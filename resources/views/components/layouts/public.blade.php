<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 text-gray-800 dark:text-gray-200 antialiased">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 rtl:space-x-reverse">
                        <x-app-logo />
                    </a>

                    <!-- Dark Mode Switcher -->
                    <flux:dropdown position="bottom" align="end">
                        <flux:button size="sm" variant="ghost" icon="swatch" inset="top bottom"></flux:button>

                        <flux:menu class="w-40">
                            <flux:menu.radio.group x-data variant="items" x-model="$flux.appearance">
                                <flux:menu.radio value="light" icon="sun">{{ __('Light') }}</flux:menu.radio>
                                <flux:menu.radio value="dark" icon="moon">{{ __('Dark') }}</flux:menu.radio>
                                <flux:menu.radio value="system" icon="computer-desktop">{{ __('System') }}</flux:menu.radio>
                            </flux:menu.radio.group>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
