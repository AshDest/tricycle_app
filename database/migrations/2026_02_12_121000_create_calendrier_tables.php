<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Calendrier de ramassage = Planification automatique des tournées.
     * Paramètres: jours ouvrables, jours fériés, zones, rotation collecteurs.
     */
    public function up(): void
    {
        // Table des zones de collecte
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('description')->nullable();
            $table->json('communes')->nullable(); // Liste des communes/quartiers
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table des jours fériés
        Schema::create('jours_feries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('is_recurrent')->default(false); // Se répète chaque année
            $table->timestamps();
        });

        // Table de configuration du calendrier
        Schema::create('calendrier_config', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Table de rotation des collecteurs par zone
        Schema::create('zone_collecteur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignId('collecteur_id')->constrained('collecteurs')->cascadeOnDelete();
            $table->integer('ordre_rotation')->default(0);
            $table->boolean('is_principal')->default(false); // Collecteur principal de la zone
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['zone_id', 'collecteur_id']);
        });

        // Table de liaison zone-caissier
        Schema::create('zone_caissier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignId('caissier_id')->constrained('caissiers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['zone_id', 'caissier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_caissier');
        Schema::dropIfExists('zone_collecteur');
        Schema::dropIfExists('calendrier_config');
        Schema::dropIfExists('jours_feries');
        Schema::dropIfExists('zones');
    }
};

