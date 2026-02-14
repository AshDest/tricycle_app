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
     * Collecte = ramassage de l'argent par le collecteur chez un CAISSIER (point de collecte).
     * Le collecteur visite plusieurs caissiers pendant sa tournée.
     */
    public function up(): void
    {
        Schema::create('collectes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournee_id')->constrained()->cascadeOnDelete();

            // Le caissier (point de collecte) visité par le collecteur
            $table->foreignId('caissier_id')->constrained('caissiers');

            // Montants
            $table->decimal('montant_attendu', 15, 2); // Total que le caissier devrait avoir
            $table->decimal('montant_collecte', 15, 2)->nullable(); // Montant réellement collecté
            $table->decimal('ecart', 15, 2)->nullable(); // Différence (montant_collecte - montant_attendu)

            // Statut de la collecte
            $table->enum('statut', ['reussie', 'partielle', 'non_realisee', 'en_litige'])->default('non_realisee');

            // Preuves et validation
            $table->longText('signature_base64')->nullable(); // Signature digitale du caissier
            $table->string('photo_cash_url')->nullable(); // Photo du cash (optionnel)
            $table->string('recu_url')->nullable(); // Reçu numérique généré

            // Horodatage de la visite
            $table->timestamp('heure_arrivee')->nullable();
            $table->timestamp('heure_depart')->nullable();

            // Notes et anomalies
            $table->text('notes_anomalies')->nullable();
            $table->text('commentaire_caissier')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collectes');
    }
};
