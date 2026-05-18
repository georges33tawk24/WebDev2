<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Paginator::defaultView('vendor.pagination.eservices');
        Paginator::defaultSimpleView('vendor.pagination.eservices');

        View::composer('*', function ($view): void {
            $locale = app()->getLocale();
            $view->with('htmlLocale', str_replace('_', '-', $locale));
            $view->with('isRtl', $locale === 'ar');
        });
    }
}
