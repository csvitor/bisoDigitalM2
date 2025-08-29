<?php

namespace App\Providers;

use App\Models\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Env;

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
        if (Schema::hasTable('config')) {
            $config = Config::orderBy('id', 'desc')->limit(1)->first();
            if ($config) {
                foreach ($config->toArray() as $key => $value) {
                    Env::getRepository()->set(strtoupper($key), $value ?? '');
                }
            }
        }
    }
}
