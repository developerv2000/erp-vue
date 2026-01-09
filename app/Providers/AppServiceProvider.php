<?php

namespace App\Providers;

use App\Support\GateDefiners\CMDGatesDefiner;
use App\Support\GateDefiners\DDGatesDefiner;
use App\Support\GateDefiners\GlobalGatesDefiner;
use App\Support\GateDefiners\MADGatesDefiner;
use App\Support\GateDefiners\MDGatesDefiner;
use App\Support\GateDefiners\NotificationGatesDefiner;
use App\Support\GateDefiners\PLDGatesDefiner;
use App\Support\GateDefiners\PRDGatesDefiner;
use App\Support\GateDefiners\StorageGatesDefiner;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // Gate definers
        GlobalGatesDefiner::defineAll();
        NotificationGatesDefiner::defineAll();
        StorageGatesDefiner::defineAll();
        MADGatesDefiner::defineAll();
        CMDGatesDefiner::defineAll();
        PLDGatesDefiner::defineAll();
        PRDGatesDefiner::defineAll();
        DDGatesDefiner::defineAll();
        MDGatesDefiner::defineAll();
    }
}
