<?php

namespace App\Providers;

use App\Actions\Subscriptions\AssignSubscription;
use App\Actions\Subscriptions\RevokeSubscription;
use App\Services\RouterOS\RouterOSManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RouterOSManager::class);

        $this->app->bind(AssignSubscription::class, fn ($app) =>
            new AssignSubscription($app->make(RouterOSManager::class))
        );

        $this->app->bind(RevokeSubscription::class, fn ($app) =>
            new RevokeSubscription($app->make(RouterOSManager::class))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
