<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\Gate::define('viewApiDocs', function (?\App\Models\User $user) {
            return env('APP_ENV') === 'local' || env('API_DOCS_PUBLIC', true) || ($user && $user->role === 'admin');
        });
    }
}
