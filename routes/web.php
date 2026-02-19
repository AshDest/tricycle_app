<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Tricycle App
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard principal (redirige selon le rôle)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profil utilisateur
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    Route::get('/profile/settings', function () {
        return view('profile.settings');
    })->name('profile.settings');

    // Notifications
    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');

    /*
    |--------------------------------------------------------------------------
    | Routes Admin (NTH Sarl)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Motards
        Route::get('/motards', \App\Livewire\Admin\Motards\Index::class)->name('motards.index');
        Route::get('/motards/create', \App\Livewire\Admin\Motards\Create::class)->name('motards.create');
        Route::get('/motards/{motard}', \App\Livewire\Admin\Motards\Show::class)->name('motards.show');
        Route::get('/motards/{motard}/edit', \App\Livewire\Admin\Motards\Edit::class)->name('motards.edit');

        // Motos
        Route::get('/motos', \App\Livewire\Admin\Motos\Index::class)->name('motos.index');
        Route::get('/motos/create', \App\Livewire\Admin\Motos\Create::class)->name('motos.create');
        Route::get('/motos/{moto}', \App\Livewire\Admin\Motos\Show::class)->name('motos.show');
        Route::get('/motos/{moto}/edit', \App\Livewire\Admin\Motos\Edit::class)->name('motos.edit');

        // Propriétaires
        Route::get('/proprietaires', \App\Livewire\Admin\Proprietaires\Index::class)->name('proprietaires.index');
        Route::get('/proprietaires/create', \App\Livewire\Admin\Proprietaires\Create::class)->name('proprietaires.create');
        Route::get('/proprietaires/{proprietaire}', \App\Livewire\Admin\Proprietaires\Show::class)->name('proprietaires.show');
        Route::get('/proprietaires/{proprietaire}/edit', \App\Livewire\Admin\Proprietaires\Edit::class)->name('proprietaires.edit');

        // Caissiers
        Route::get('/caissiers', \App\Livewire\Admin\Caissiers\Index::class)->name('caissiers.index');
        Route::get('/caissiers/create', \App\Livewire\Admin\Caissiers\Create::class)->name('caissiers.create');
        Route::get('/caissiers/{caissier}', \App\Livewire\Admin\Caissiers\Show::class)->name('caissiers.show');
        Route::get('/caissiers/{caissier}/edit', \App\Livewire\Admin\Caissiers\Edit::class)->name('caissiers.edit');

        // Collecteurs
        Route::get('/collecteurs', \App\Livewire\Admin\Collecteurs\Index::class)->name('collecteurs.index');
        Route::get('/collecteurs/create', \App\Livewire\Admin\Collecteurs\Create::class)->name('collecteurs.create');
        Route::get('/collecteurs/{collecteur}', \App\Livewire\Admin\Collecteurs\Show::class)->name('collecteurs.show');
        Route::get('/collecteurs/{collecteur}/edit', \App\Livewire\Admin\Collecteurs\Edit::class)->name('collecteurs.edit');

        // Versements
        Route::get('/versements', \App\Livewire\Admin\Versements\Index::class)->name('versements.index');
        Route::get('/versements/{versement}', \App\Livewire\Admin\Versements\Show::class)->name('versements.show');

        // Paiements Propriétaires
        Route::get('/payments', \App\Livewire\Admin\Payments\Index::class)->name('payments.index');
        Route::get('/payments/create', \App\Livewire\Admin\Payments\Create::class)->name('payments.create');
        Route::get('/payments/{payment}', \App\Livewire\Admin\Payments\Show::class)->name('payments.show');

        // Tournées
        Route::get('/tournees', \App\Livewire\Admin\Tournees\Index::class)->name('tournees.index');
        Route::get('/tournees/create', \App\Livewire\Admin\Tournees\Create::class)->name('tournees.create');
        Route::get('/tournees/{tournee}', \App\Livewire\Admin\Tournees\Show::class)->name('tournees.show');

        // Zones
        Route::get('/zones', \App\Livewire\Admin\Zones\Index::class)->name('zones.index');
        Route::get('/zones/create', \App\Livewire\Admin\Zones\Create::class)->name('zones.create');
        Route::get('/zones/{zone}/edit', \App\Livewire\Admin\Zones\Edit::class)->name('zones.edit');

        // Maintenances
        Route::get('/maintenances', \App\Livewire\Admin\Maintenances\Index::class)->name('maintenances.index');
        Route::get('/maintenances/create', \App\Livewire\Admin\Maintenances\Create::class)->name('maintenances.create');
        Route::get('/maintenances/{maintenance}', \App\Livewire\Admin\Maintenances\Show::class)->name('maintenances.show');

        // Accidents
        Route::get('/accidents', \App\Livewire\Admin\Accidents\Index::class)->name('accidents.index');
        Route::get('/accidents/{accident}', \App\Livewire\Admin\Accidents\Show::class)->name('accidents.show');

        // Rapports
        Route::get('/reports/daily', \App\Livewire\Admin\Reports\Daily::class)->name('reports.daily');
        Route::get('/reports/weekly', \App\Livewire\Admin\Reports\Weekly::class)->name('reports.weekly');
        Route::get('/reports/monthly', \App\Livewire\Admin\Reports\Monthly::class)->name('reports.monthly');

        // Utilisateurs
        Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
        Route::get('/users/create', \App\Livewire\Admin\Users\Create::class)->name('users.create');
        Route::get('/users/{user}', \App\Livewire\Admin\Users\Show::class)->name('users.show');
        Route::get('/users/{user}/edit', \App\Livewire\Admin\Users\Edit::class)->name('users.edit');

        // Paramètres
        Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Supervisor (OKAMI)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
        // Motards (CRUD complet)
        Route::get('/motards', \App\Livewire\Supervisor\Motards\Index::class)->name('motards.index');
        Route::get('/motards/create', \App\Livewire\Supervisor\Motards\Create::class)->name('motards.create');
        Route::get('/motards/{motard}', \App\Livewire\Supervisor\Motards\Show::class)->name('motards.show');
        Route::get('/motards/{motard}/edit', \App\Livewire\Supervisor\Motards\Edit::class)->name('motards.edit');

        // Motos (CRUD complet)
        Route::get('/motos', \App\Livewire\Supervisor\Motos\Index::class)->name('motos.index');
        Route::get('/motos/create', \App\Livewire\Supervisor\Motos\Create::class)->name('motos.create');
        Route::get('/motos/{moto}/edit', \App\Livewire\Supervisor\Motos\Edit::class)->name('motos.edit');

        // Versements (Visualisation seulement)
        Route::get('/versements', \App\Livewire\Supervisor\Versements\Index::class)->name('versements.index');


        // Propriétaires (Enregistrement et modification)
        Route::get('/proprietaires', \App\Livewire\Supervisor\Proprietaires\Index::class)->name('proprietaires.index');
        Route::get('/proprietaires/create', \App\Livewire\Supervisor\Proprietaires\Create::class)->name('proprietaires.create');
        Route::get('/proprietaires/{proprietaire}/edit', \App\Livewire\Supervisor\Proprietaires\Edit::class)->name('proprietaires.edit');

        // Paiements Propriétaires (demandes et validations)
        Route::get('/payments', \App\Livewire\Supervisor\Payments\Index::class)->name('payments.index');
        Route::get('/payments/create', \App\Livewire\Supervisor\Payments\Create::class)->name('payments.create');

        // Maintenances (consultation et enregistrement)
        Route::get('/maintenances', \App\Livewire\Supervisor\Maintenances\Index::class)->name('maintenances.index');
        Route::get('/maintenances/create', \App\Livewire\Supervisor\Maintenances\Create::class)->name('maintenances.create');
        Route::get('/maintenances/{maintenance}', \App\Livewire\Supervisor\Maintenances\Show::class)->name('maintenances.show');

        // Accidents (consultation et enregistrement)
        Route::get('/accidents', \App\Livewire\Supervisor\Accidents\Index::class)->name('accidents.index');
        Route::get('/accidents/create', \App\Livewire\Supervisor\Accidents\Create::class)->name('accidents.create');
        Route::get('/accidents/{accident}', \App\Livewire\Supervisor\Accidents\Show::class)->name('accidents.show');

        // Rapports
        Route::get('/reports/daily', \App\Livewire\Supervisor\Reports\Daily::class)->name('reports.daily');
        Route::get('/reports/weekly', \App\Livewire\Supervisor\Reports\Weekly::class)->name('reports.weekly');
        Route::get('/reports/monthly', \App\Livewire\Supervisor\Reports\Monthly::class)->name('reports.monthly');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Owner (Propriétaire)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:owner'])->prefix('owner')->name('owner.')->group(function () {
        Route::get('/motos', \App\Livewire\Owner\Motos\Index::class)->name('motos.index');
        Route::get('/versements', \App\Livewire\Owner\Versements\Index::class)->name('versements.index');
        Route::get('/payments', \App\Livewire\Owner\Payments\Index::class)->name('payments.index');
        Route::get('/reports', \App\Livewire\Owner\Reports\Index::class)->name('reports.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Driver (Motard)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:driver'])->prefix('driver')->name('driver.')->group(function () {
        Route::get('/statut', \App\Livewire\Driver\Statut::class)->name('statut');
        Route::get('/historique', \App\Livewire\Driver\Historique::class)->name('historique');
        Route::get('/accidents/create', \App\Livewire\Driver\Accidents\Create::class)->name('accidents.create');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Cashier (Caissier)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/versements', \App\Livewire\Cashier\Versements\Index::class)->name('versements.index');
        Route::get('/versements/create', \App\Livewire\Cashier\Versements\Create::class)->name('versements.create');
        Route::get('/solde', \App\Livewire\Cashier\Solde::class)->name('solde');
        Route::get('/depot', \App\Livewire\Cashier\Depot::class)->name('depot');
        Route::get('/depots/historique', \App\Livewire\Cashier\Depots\Historique::class)->name('depots.historique');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes Collector (Collecteur)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:collector'])->prefix('collector')->name('collector.')->group(function () {
        Route::get('/tournee', \App\Livewire\Collector\Tournee\Index::class)->name('tournee.index');
        Route::get('/collectes', \App\Livewire\Collector\Collectes\Index::class)->name('collectes.index');
        Route::get('/historique', \App\Livewire\Collector\Historique::class)->name('historique');

        // Demandes de paiement à traiter
        Route::get('/payments', \App\Livewire\Collector\Payments\Index::class)->name('payments.index');

        // Dépôts reçus des caissiers
        Route::get('/depots', \App\Livewire\Collector\Depots\Index::class)->name('depots.index');

        // Solde des propriétaires
        Route::get('/proprietaires', \App\Livewire\Collector\Proprietaires\Index::class)->name('proprietaires.index');
    });
});

require __DIR__.'/auth.php';
