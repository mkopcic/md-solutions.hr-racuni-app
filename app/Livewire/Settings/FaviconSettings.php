<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app', ['title' => 'Favicon Settings'])]
class FaviconSettings extends Component
{
    use WithFileUploads;

    public $favicon_ico;

    public $favicon_svg;

    public $apple_touch_icon;

    public bool $showSuccessMessage = false;

    /**
     * Upload and update favicon files
     */
    public function updateFavicons(): void
    {
        $this->validate([
            'favicon_ico' => 'nullable|file|mimes:ico|max:1024',
            'favicon_svg' => 'nullable|file|mimes:svg|max:1024',
            'apple_touch_icon' => 'nullable|file|mimes:png|max:1024',
        ]);

        if ($this->favicon_ico) {
            $this->favicon_ico->storeAs('/', 'favicon.ico', 'public');
            copy(storage_path('app/public/favicon.ico'), public_path('favicon.ico'));
            unlink(storage_path('app/public/favicon.ico'));
        }

        if ($this->favicon_svg) {
            $this->favicon_svg->storeAs('/', 'favicon.svg', 'public');
            copy(storage_path('app/public/favicon.svg'), public_path('favicon.svg'));
            unlink(storage_path('app/public/favicon.svg'));
        }

        if ($this->apple_touch_icon) {
            $this->apple_touch_icon->storeAs('/', 'apple-touch-icon.png', 'public');
            copy(storage_path('app/public/apple-touch-icon.png'), public_path('apple-touch-icon.png'));
            unlink(storage_path('app/public/apple-touch-icon.png'));
        }

        $this->showSuccessMessage = true;
        $this->reset(['favicon_ico', 'favicon_svg', 'apple_touch_icon']);

        $this->dispatch('favicon-updated');
    }

    /**
     * Check if favicon files exist
     */
    public function getCurrentFavicons(): array
    {
        return [
            'favicon_ico' => file_exists(public_path('favicon.ico')),
            'favicon_svg' => file_exists(public_path('favicon.svg')),
            'apple_touch_icon' => file_exists(public_path('apple-touch-icon.png')),
        ];
    }
}
