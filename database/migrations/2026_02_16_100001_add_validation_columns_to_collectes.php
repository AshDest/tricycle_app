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
        // Ajouter des colonnes à la table collectes pour le dépôt du caissier
        Schema::table('collectes', function (Blueprint $table) {
            if (!Schema::hasColumn('collectes', 'valide_par_collecteur')) {
                $table->boolean('valide_par_collecteur')->default(false)->after('statut');
            }
            if (!Schema::hasColumn('collectes', 'valide_collecteur_at')) {
                $table->timestamp('valide_collecteur_at')->nullable()->after('statut');
            }
            if (!Schema::hasColumn('collectes', 'notes_collecteur')) {
                $table->text('notes_collecteur')->nullable()->after('notes_anomalies');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collectes', function (Blueprint $table) {
            $table->dropColumn(['valide_par_collecteur', 'valide_collecteur_at', 'notes_collecteur']);
        });
    }
};
