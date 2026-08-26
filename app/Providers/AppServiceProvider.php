<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Observers\DoctorObserver;
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
        Doctor::observe(DoctorObserver::class);
    }
}
