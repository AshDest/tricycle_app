<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tournees', function (Blueprint $table) {
            // Renommer heure_debut et heure_fin en heure_debut_prevue et heure_fin_prevue
            // et ajouter les colonnes manquantes
            if (!Schema::hasColumn('tournees', 'heure_debut_prevue')) {
                $table->time('heure_debut_prevue')->nullable()->after('zone');
            }
            if (!Schema::hasColumn('tournees', 'heure_fin_prevue')) {
                $table->time('heure_fin_prevue')->nullable()->after('heure_debut_prevue');
            }
            if (!Schema::hasColumn('tournees', 'heure_debut_reelle')) {
                $table->time('heure_debut_reelle')->nullable()->after('heure_fin_prevue');
            }
            if (!Schema::hasColumn('tournees', 'heure_fin_reelle')) {
                $table->time('heure_fin_reelle')->nullable()->after('heure_debut_reelle');
            }
            if (!Schema::hasColumn('tournees', 'presence_confirmee')) {
                $table->boolean('presence_confirmee')->default(false)->after('heure_fin_reelle');
            }
            if (!Schema::hasColumn('tournees', 'presence_confirmee_at')) {
                $table->timestamp('presence_confirmee_at')->nullable()->after('presence_confirmee');
            }
            if (!Schema::hasColumn('tournees', 'transmis_nth')) {
                $table->boolean('transmis_nth')->default(false)->after('anomalies_notes');
            }
            if (!Schema::hasColumn('tournees', 'transmis_nth_at')) {
                $table->timestamp('transmis_nth_at')->nullable()->after('transmis_nth');
            }
            if (!Schema::hasColumn('tournees', 'valide_par_nth_id')) {
                $table->foreignId('valide_par_nth_id')->nullable()->constrained('users')->after('transmis_nth_at');
            }
            if (!Schema::hasColumn('tournees', 'valide_par_nth_at')) {
                $table->timestamp('valide_par_nth_at')->nullable()->after('valide_par_nth_id');
            }
            if (!Schema::hasColumn('tournees', 'latitude_debut')) {
                $table->decimal('latitude_debut', 10, 8)->nullable()->after('valide_par_nth_at');
            }
            if (!Schema::hasColumn('tournees', 'longitude_debut')) {
                $table->decimal('longitude_debut', 11, 8)->nullable()->after('latitude_debut');
            }
            if (!Schema::hasColumn('tournees', 'latitude_fin')) {
                $table->decimal('latitude_fin', 10, 8)->nullable()->after('longitude_debut');
            }
            if (!Schema::hasColumn('tournees', 'longitude_fin')) {
                $table->decimal('longitude_fin', 11, 8)->nullable()->after('latitude_fin');
            }
            if (!Schema::hasColumn('tournees', 'ecart_total')) {
                $table->decimal('ecart_total', 15, 2)->nullable()->after('longitude_fin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournees', function (Blueprint $table) {
            $table->dropColumnIfExists('heure_debut_prevue');
            $table->dropColumnIfExists('heure_fin_prevue');
            $table->dropColumnIfExists('heure_debut_reelle');
            $table->dropColumnIfExists('heure_fin_reelle');
            $table->dropColumnIfExists('presence_confirmee');
            $table->dropColumnIfExists('presence_confirmee_at');
            $table->dropColumnIfExists('transmis_nth');
            $table->dropColumnIfExists('transmis_nth_at');
            $table->dropForeignIdFor('valide_par_nth_id');
            $table->dropColumnIfExists('valide_par_nth_at');
            $table->dropColumnIfExists('latitude_debut');
            $table->dropColumnIfExists('longitude_debut');
            $table->dropColumnIfExists('latitude_fin');
            $table->dropColumnIfExists('longitude_fin');
            $table->dropColumnIfExists('ecart_total');
        });
    }
};
