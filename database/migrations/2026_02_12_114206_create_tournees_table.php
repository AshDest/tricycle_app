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
     * Tournée = itinéraire journalier d'un collecteur pour ramasser l'argent chez les caissiers.
     */
    public function up(): void
    {
        Schema::create('tournees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collecteur_id')->constrained('collecteurs');
            $table->date('date');
            $table->string('zone')->nullable();

            // Statut de la tournée
            $table->enum('statut', ['planifiee', 'confirmee', 'en_cours', 'terminee', 'en_retard', 'annulee'])->default('planifiee');

            // Horodatage
            $table->time('heure_debut_prevue')->nullable();
            $table->time('heure_fin_prevue')->nullable();
            $table->timestamp('heure_debut_reelle')->nullable();
            $table->timestamp('heure_fin_reelle')->nullable();

            // Confirmation de présence au début de tournée
            $table->boolean('presence_confirmee')->default(false);
            $table->timestamp('presence_confirmee_at')->nullable();

            // Montants
            $table->decimal('total_attendu', 15, 2)->default(0);
            $table->decimal('total_encaisse', 15, 2)->default(0);
            $table->decimal('ecart_total', 15, 2)->default(0);

            // Transmission à NTH
            $table->boolean('transmis_nth')->default(false);
            $table->timestamp('transmis_nth_at')->nullable();
            $table->foreignId('valide_par_nth_id')->nullable()->constrained('users');
            $table->timestamp('valide_par_nth_at')->nullable();

            // Notes et anomalies
            $table->text('anomalies_notes')->nullable();

            // Géolocalisation (optionnelle)
            $table->decimal('latitude_debut', 10, 8)->nullable();
            $table->decimal('longitude_debut', 11, 8)->nullable();
            $table->decimal('latitude_fin', 10, 8)->nullable();
            $table->decimal('longitude_fin', 11, 8)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournees');
    }
};
