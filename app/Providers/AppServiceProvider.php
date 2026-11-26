<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
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
        // Bo dem rieng: throttle:5,60 tran dung chung khoa IP voi throttle:60,1
        // cua nhom /ho-so nen luot XEM trang cung bi tinh vao 5 lan phan hoi.
        RateLimiter::for('phan-hoi', fn ($request) => Limit::perHour(5)->by('phan-hoi|'.$request->ip()));
    }

    /** Loc link nav theo co 'auth' / 'admin' trong config/navigation.php. */
    public static function visibleLinks(array $links): array
    {
        $user = auth()->user();

        return array_values(array_filter($links, fn ($l) => ! (($l['auth'] ?? false) && ! $user)
            && ! (($l['admin'] ?? false) && ! $user?->isAdmin())));
    }
}
