<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les colonnes pour la gestion des semaines et répartition
     */
    public function up(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            // Semaine concernée par le versement
            $table->date('semaine_debut')->nullable()->after('date_versement');
            $table->date('semaine_fin')->nullable()->after('semaine_debut');
            $table->integer('numero_semaine')->nullable()->after('semaine_fin');

            // Répartition automatique calculée
            $table->decimal('part_proprietaire', 15, 2)->default(0)->after('arrieres');
            $table->decimal('part_okami', 15, 2)->default(0)->after('part_proprietaire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropColumn([
                'semaine_debut',
                'semaine_fin',
                'numero_semaine',
                'part_proprietaire',
                'part_okami'
            ]);
        });
    }
};

