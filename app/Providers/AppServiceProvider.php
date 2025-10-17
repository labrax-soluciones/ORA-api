<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Municipality;

class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        //
    }

    public function boot(): void {
        // {municipality} aceptará tanto ID como SLUG
        Route::bind('municipality', function ($value) {
            return Municipality::query()
                ->where('slug', $value)
                ->orWhere('id', is_numeric($value) ? (int) $value : 0)
                ->firstOrFail();
        });
    }
}
