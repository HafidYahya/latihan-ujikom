<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Filament\Facades\Filament;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewFilament', function ($user) {
            return in_array($user->role, ['sales', 'petugas']);
        });
    }
}
