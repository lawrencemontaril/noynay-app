<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Vite;
use Carbon\Carbon;

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
        DB::prohibitDestructiveCommands(app()->isProduction());
        Model::shouldBeStrict();
        JsonResource::withoutWrapping();
        Vite::prefetch(concurrency: 6);

        Carbon::macro('diffFromDate', static function ($date, $absolute = false, $short = false, $parts = 1) {
            return self::this()->diffForHumans(Carbon::parse($date), $absolute, $short, $parts);
        });
    }
}
