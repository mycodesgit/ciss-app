<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Serve livewire.js relative to the app's base URL so it also works from a subfolder
        config(['livewire.asset_url' => url('livewire/livewire.js')]);
    }
}
