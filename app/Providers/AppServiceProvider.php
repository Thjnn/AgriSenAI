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
        //
    }

    /** Loc link nav theo co 'auth' / 'admin' trong config/navigation.php. */
    public static function visibleLinks(array $links): array
    {
        $user = auth()->user();

        return array_values(array_filter($links, fn ($l) => ! (($l['auth'] ?? false) && ! $user)
            && ! (($l['admin'] ?? false) && ! $user?->isAdmin())));
    }
}
