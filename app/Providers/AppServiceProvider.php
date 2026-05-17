<?php

namespace App\Providers;

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
        // Register the Helper facade alias used by Vuexy Blade templates
        if (!class_exists('Helper')) {
            class_alias(\App\Helpers\Helpers::class, 'Helper');
        }

        // Share menu data with all views (consumed by sidebar / vertical menu)
        View::composer('*', function ($view) {
            $verticalMenuJson  = file_get_contents(resource_path('menu/verticalMenu.json'));
            $verticalMenuData  = json_decode($verticalMenuJson);

            $view->with('menuData', [$verticalMenuData]);
        });
    }
}
