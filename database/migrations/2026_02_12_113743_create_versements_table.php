<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Versement = paiement journalier du motard au caissier (point de collecte).
     * Flux: Motard → Caissier (validation) → Collecteur (ramassage) → NTH (Admin)
     */
    public function up(): void
    {
        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('moto_id')->constrained()->cascadeOnDelete();
            $table->decimal('montant', 15, 2);
            $table->decimal('montant_attendu', 15, 2);
            $table->date('date_versement');
            $table->time('heure_versement')->nullable();
            $table->enum('mode_paiement', ['cash', 'mobile_money', 'depot'])->default('cash');
            $table->enum('statut', ['payé', 'en_retard', 'partiellement_payé', 'non_effectué'])->default('non_effectué');

            // Caissier qui a réceptionné le versement du motard
            $table->foreignId('caissier_id')->nullable()->constrained('caissiers');
            $table->timestamp('validated_by_caissier_at')->nullable();

            // Validation par OKAMI (uniquement pour versements douteux/litigieux)
            $table->boolean('valide_par_okami')->default(false);
            $table->foreignId('validated_by_okami_id')->nullable()->constrained('users');
            $table->timestamp('validated_by_okami_at')->nullable();
            $table->text('okami_notes')->nullable();

            // Référence à la collecte - ajoutée plus tard via migration séparée
            $table->unsignedBigInteger('collecte_id')->nullable();

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
        Schema::dropIfExists('versements');
    }
};
