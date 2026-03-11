<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recompense = Système de récompenses pour les motards performants
     * Basé sur: régularité versements, sécurité (accidents), arriérés
     */
    public function up(): void
    {
        Schema::create('recompenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motard_id')->constrained('motards')->cascadeOnDelete();

            // Type de récompense
            $table->enum('type', [
                'badge_bronze',
                'badge_argent',
                'badge_or',
                'badge_diamant',
                'prime_mensuelle',
                'prime_trimestrielle',
                'certificat',
                'bonus_special',
            ]);

            // Catégorie de performance
            $table->enum('categorie', [
                'regularite',
                'securite',
                'versement_complet',
                'excellence',
            ]);

            // Détails
            $table->string('titre');
            $table->text('description')->nullable();
            $table->decimal('montant_prime', 15, 2)->nullable();
            $table->date('periode_debut');
            $table->date('periode_fin');

            // Scores
            $table->integer('score_regularite')->default(0);
            $table->integer('score_securite')->default(0);
            $table->integer('score_versement')->default(0);
            $table->integer('score_total')->default(0);

            // Statut
            $table->enum('statut', ['attribue', 'remis', 'annule'])->default('attribue');
            $table->date('date_remise')->nullable();
            $table->foreignId('remis_par')->nullable()->constrained('users');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recompenses');
    }
};
