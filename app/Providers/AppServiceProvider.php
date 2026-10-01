<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Toàn bộ giao diện (admin + trang khách hàng) dùng Bootstrap, không phải Tailwind
        // nên phải ép $paginator->links() render theo view Bootstrap, tránh icon/nút bị vỡ layout.
        Paginator::useBootstrapFive();
    }
}
