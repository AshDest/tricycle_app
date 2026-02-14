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
        Schema::table('accidents', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            if (!Schema::hasColumn('accidents', 'temoin_nom')) {
                $table->string('temoin_nom')->nullable()->after('temoignage_temoin');
            }
            if (!Schema::hasColumn('accidents', 'temoin_telephone')) {
                $table->string('temoin_telephone')->nullable()->after('temoin_nom');
            }
            if (!Schema::hasColumn('accidents', 'photos_supplementaires')) {
                $table->json('photos_supplementaires')->nullable()->after('photo_dommage_url');
            }
            if (!Schema::hasColumn('accidents', 'cout_reel')) {
                $table->decimal('cout_reel', 15, 2)->nullable()->after('estimation_cout');
            }
            if (!Schema::hasColumn('accidents', 'gravite')) {
                $table->enum('gravite', ['mineur', 'modere', 'grave'])->default('mineur')->after('description');
            }
            if (!Schema::hasColumn('accidents', 'reparation_programmee_at')) {
                $table->timestamp('reparation_programmee_at')->nullable()->after('statut');
            }
            if (!Schema::hasColumn('accidents', 'reparation_terminee_at')) {
                $table->timestamp('reparation_terminee_at')->nullable()->after('reparation_programmee_at');
            }
            if (!Schema::hasColumn('accidents', 'valide_par')) {
                $table->foreignId('valide_par')->nullable()->constrained('users')->after('reparation_terminee_at');
            }
            if (!Schema::hasColumn('accidents', 'valide_at')) {
                $table->timestamp('valide_at')->nullable()->after('valide_par');
            }
            if (!Schema::hasColumn('accidents', 'notes_admin')) {
                $table->text('notes_admin')->nullable()->after('valide_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->dropColumnIfExists('temoin_nom');
            $table->dropColumnIfExists('temoin_telephone');
            $table->dropColumnIfExists('photos_supplementaires');
            $table->dropColumnIfExists('cout_reel');
            $table->dropColumnIfExists('gravite');
            $table->dropColumnIfExists('reparation_programmee_at');
            $table->dropColumnIfExists('reparation_terminee_at');
            $table->dropForeignIdFor('valide_par');
            $table->dropColumnIfExists('notes_admin');
        });
    }
};
