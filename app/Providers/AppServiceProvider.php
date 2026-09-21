<?php

namespace App\Providers;

use App\Models\ContactInquiry;
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
        View::composer('layouts.navigation', function ($view) {
            $isAdmin = auth()->check() && auth()->user()->is_admin;

            $view->with('newInquiryCount', $isAdmin
                ? ContactInquiry::query()->where('status', 'new')->count()
                : 0);
        });
    }
}
