<?php

namespace App\Providers;

use App\Events\SalaryChanged;
use App\Listeners\HandelSalaryChanged;
use Illuminate\Support\Facades\Event;
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
        Event::listen(
            SalaryChanged::class,
            HandelSalaryChanged::class,
        );

    }
}
