<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PerformanceMotard = Historique des scores de performance mensuelle des motards
     */
    public function up(): void
    {
        Schema::create('performance_motards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motard_id')->constrained('motards')->cascadeOnDelete();

            // Période
            $table->integer('mois');
            $table->integer('annee');

            // Statistiques versements
            $table->integer('jours_travailles')->default(0);
            $table->integer('versements_a_temps')->default(0);
            $table->integer('versements_en_retard')->default(0);
            $table->decimal('total_verse', 15, 2)->default(0);
            $table->decimal('total_attendu', 15, 2)->default(0);
            $table->decimal('arrieres_cumules', 15, 2)->default(0);

            // Statistiques accidents
            $table->integer('accidents_total')->default(0);
            $table->integer('accidents_mineurs')->default(0);
            $table->integer('accidents_moderes')->default(0);
            $table->integer('accidents_graves')->default(0);

            // Scores calculés (0-100)
            $table->integer('score_regularite')->default(0);     // % versements à temps
            $table->integer('score_securite')->default(0);       // Inversement proportionnel aux accidents
            $table->integer('score_versement')->default(0);      // % du montant versé vs attendu
            $table->integer('score_total')->default(0);          // Moyenne pondérée

            // Rang dans le classement
            $table->integer('rang_mensuel')->nullable();

            // Badge mérité pour ce mois
            $table->enum('badge', ['aucun', 'bronze', 'argent', 'or', 'diamant'])->default('aucun');

            $table->timestamps();

            // Un seul enregistrement par motard par mois
            $table->unique(['motard_id', 'mois', 'annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_motards');
    }
};
