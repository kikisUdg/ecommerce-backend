<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem; // <-- añade esta línea

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Parche: asegurar el binding 'files' si por alguna razón no está
        $this->app->singleton('files', function () {
            return new Filesystem();
        });
    }

    public function boot(): void
    {
        //
    }
}