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
     * Maintenance = Suivi technique complet des motos-tricycles.
     */
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('motard_id')->nullable()->constrained('motards')->nullOnDelete();

            // Type et description
            $table->enum('type', ['preventive', 'corrective', 'remplacement'])->default('corrective');
            $table->text('description');

            // Photos avant/après
            $table->string('photo_avant_url')->nullable();
            $table->string('photo_apres_url')->nullable();

            // Informations intervention
            $table->dateTime('date_intervention');
            $table->string('technicien_garage_nom');
            $table->string('technicien_telephone')->nullable();
            $table->string('garage_adresse')->nullable();
            $table->date('prochain_entretien')->nullable();

            // Coûts
            $table->decimal('cout_pieces', 15, 2)->default(0);
            $table->decimal('cout_main_oeuvre', 15, 2)->default(0);
            $table->string('facture_url')->nullable();

            // Qui a payé
            $table->enum('qui_a_paye', ['motard', 'proprietaire', 'nth', 'okami'])->default('nth');

            // Statut et validation
            $table->enum('statut', ['en_attente', 'en_cours', 'termine'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users');
            $table->timestamp('valide_at')->nullable();

            // Notes
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
        Schema::dropIfExists('maintenances');
    }
};
