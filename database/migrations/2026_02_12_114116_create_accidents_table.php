<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     * Accident = Déclaration et suivi des accidents impliquant les motos-tricycles.
     */
    public function up(): void
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('motard_id')->constrained('motards')->cascadeOnDelete();

            // Informations de base
            $table->dateTime('date_heure');
            $table->string('lieu');
            $table->text('description');
            $table->enum('gravite', ['mineur', 'modere', 'grave'])->default('mineur');

            // Témoignages
            $table->text('temoignage_motard')->nullable();
            $table->text('temoignage_temoin')->nullable();
            $table->string('temoin_nom')->nullable();
            $table->string('temoin_telephone')->nullable();

            // Preuves visuelles
            $table->string('photo_dommage_url')->nullable();
            $table->json('photos_supplementaires')->nullable(); // Array d'URLs
            $table->string('video_url')->nullable();

            // Évaluation des dommages
            $table->decimal('estimation_cout', 15, 2)->nullable();
            $table->decimal('cout_reel', 15, 2)->nullable();
            $table->text('pieces_endommagees')->nullable();
            $table->string('devis_url')->nullable();

            // Prise en charge et statut
            $table->enum('prise_en_charge', ['motard', 'proprietaire', 'assurance', 'nth'])->default('nth');
            $table->enum('statut', ['declare', 'evalue', 'reparation_programmee', 'repare', 'cloture'])->default('declare');

            // Suivi réparation
            $table->timestamp('reparation_programmee_at')->nullable();
            $table->timestamp('reparation_terminee_at')->nullable();

            // Validation Admin NTH
            $table->foreignId('valide_par')->nullable()->constrained('users');
            $table->timestamp('valide_at')->nullable();
            $table->text('notes_admin')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accidents');
    }
};
