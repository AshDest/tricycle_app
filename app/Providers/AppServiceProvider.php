<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder;
use Illuminate\Pagination\Paginator;

// Models
use App\Models\Versement;
use App\Models\Accident;
use App\Models\Maintenance;
use App\Models\Tournee;
use App\Models\Payment;
use App\Models\Moto;

// Observers
use App\Observers\VersementObserver;
use App\Observers\AccidentObserver;
use App\Observers\MaintenanceObserver;
use App\Observers\TourneeObserver;
use App\Observers\PaymentObserver;
use App\Observers\MotoObserver;

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
        Builder::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Enregistrement des Observers pour les notifications automatiques
        Versement::observe(VersementObserver::class);
        Accident::observe(AccidentObserver::class);
        Maintenance::observe(MaintenanceObserver::class);
        Tournee::observe(TourneeObserver::class);
        Payment::observe(PaymentObserver::class);
        Moto::observe(MotoObserver::class);
    }
}
