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
        Schema::create('transactions_mobile', function (Blueprint $table) {
            $table->id();
            $table->string('numero_transaction')->unique();
            $table->foreignId('collecteur_id')->constrained('collecteurs')->onDelete('cascade');
            $table->enum('type', ['envoi', 'retrait']); // envoi = envoyer de l'argent, retrait = retirer de l'argent
            $table->decimal('montant', 15, 2);
            $table->decimal('frais', 12, 2)->default(0); // Frais de transaction
            $table->decimal('montant_net', 15, 2); // Montant après frais
            $table->enum('operateur', ['mpesa', 'airtel_money', 'orange_money', 'afrimoney']);
            $table->string('numero_telephone'); // Numéro du bénéficiaire ou source
            $table->string('nom_beneficiaire')->nullable(); // Nom du bénéficiaire
            $table->string('reference_operateur')->nullable(); // Référence de l'opérateur
            $table->enum('statut', ['en_attente', 'complete', 'echoue', 'annule'])->default('en_attente');
            $table->text('motif')->nullable(); // Motif de la transaction
            $table->text('notes')->nullable();
            $table->timestamp('date_transaction')->useCurrent();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['collecteur_id', 'date_transaction']);
            $table->index(['type', 'statut']);
            $table->index('operateur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_mobile');
    }
};

