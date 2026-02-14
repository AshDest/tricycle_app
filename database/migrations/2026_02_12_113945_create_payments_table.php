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
     * Payment = Paiement vers le propriétaire (reversement des recettes collectées).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('proprietaires')->cascadeOnDelete();

            // Montants
            $table->decimal('total_du', 15, 2)->default(0);
            $table->decimal('total_paye', 15, 2)->default(0);

            // Mode de paiement (Mobile Money + Virement)
            $table->enum('mode_paiement', ['mpesa', 'airtel_money', 'orange_money', 'virement_bancaire']);
            $table->string('numero_compte')->nullable(); // Numéro de téléphone ou compte bancaire

            // Statut
            $table->enum('statut', ['en_attente', 'approuve', 'payé', 'rejeté'])->default('en_attente');

            // Dates
            $table->date('date_demande');
            $table->date('date_paiement')->nullable();
            $table->date('periode_debut')->nullable(); // Début période couverte
            $table->date('periode_fin')->nullable();   // Fin période couverte

            // Référence et preuve
            $table->string('reference_paiement')->nullable();
            $table->string('recu_url')->nullable(); // Reçu PDF généré

            // Traitement
            $table->foreignId('traite_par')->nullable()->constrained('users');
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
        Schema::dropIfExists('payments');
    }
};
