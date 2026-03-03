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
        Schema::create('depenses_lavage', function (Blueprint $table) {
            $table->id();
            $table->string('numero_depense')->unique(); // DEP-20260303-001
            $table->foreignId('cleaner_id')->constrained()->onDelete('cascade');

            // Détails de la dépense
            $table->enum('categorie', [
                'produits', // Savon, détergent, etc.
                'equipement', // Seaux, éponges, chiffons
                'eau', // Facture d'eau
                'electricite', // Facture électricité
                'loyer', // Loyer local
                'salaire', // Salaire assistant
                'transport', // Frais de transport
                'reparation', // Réparation équipement
                'autre' // Autres dépenses
            ])->default('produits');

            $table->string('description');
            $table->decimal('montant', 12, 2);
            $table->enum('mode_paiement', ['cash', 'mobile_money'])->default('cash');
            $table->string('reference_paiement')->nullable(); // Référence si mobile money
            $table->string('fournisseur')->nullable(); // Nom du fournisseur
            $table->string('piece_justificative')->nullable(); // Chemin vers fichier (facture, reçu)

            $table->date('date_depense');
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
        Schema::dropIfExists('depenses_lavage');
    }
};

