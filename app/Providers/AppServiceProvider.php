<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Vite::prefetch(concurrency: 3);

        if ($this->app->isProduction()) {
            URL::forceScheme('https');

            if (config('session.secure') === null) {
                config(['session.secure' => true]);
            }
        }

        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->letters()->mixedCase()->numbers()
                : $rule;
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by(($request->user('web')?->id ?: 'guest').'|'.$request->ip());
        });

        RateLimiter::for('order-lookup', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('order-verify', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->route('orderCode'));
        });

        RateLimiter::for('order-mutation', function (Request $request) {
            return Limit::perMinute(15)->by(($request->user('web')?->id ?: 'guest').'|'.$request->ip());
        });

        RateLimiter::for('cart-sync', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('auth-sensitive', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
