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
        // Table des laveurs (cleaners)
        Schema::create('cleaners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('identifiant')->unique(); // CLN-001, CLN-002...
            $table->string('zone')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->decimal('solde_actuel', 12, 2)->default(0); // Solde du laveur
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Table des lavages
        Schema::create('lavages', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lavage')->unique(); // LAV-20260303-001
            $table->foreignId('cleaner_id')->constrained()->onDelete('cascade');

            // Moto - peut être nulle si externe
            $table->foreignId('moto_id')->nullable()->constrained()->onDelete('set null');

            // Pour les motos externes (non enregistrées dans le système)
            $table->boolean('is_externe')->default(false);
            $table->string('plaque_externe')->nullable();
            $table->string('proprietaire_externe')->nullable();
            $table->string('telephone_externe')->nullable();

            // Détails du lavage
            $table->enum('type_lavage', ['simple', 'complet', 'premium'])->default('simple');
            $table->decimal('prix_base', 10, 2); // Prix configuré
            $table->decimal('prix_final', 10, 2); // Prix après remise éventuelle
            $table->decimal('remise', 10, 2)->default(0);

            // Répartition (uniquement pour motos du système)
            $table->decimal('part_cleaner', 10, 2)->default(0); // 80%
            $table->decimal('part_okami', 10, 2)->default(0); // 20%

            // Paiement
            $table->enum('mode_paiement', ['cash', 'mobile_money'])->default('cash');
            $table->enum('statut_paiement', ['payé', 'en_attente', 'annulé'])->default('payé');
            $table->timestamp('date_lavage')->useCurrent();

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
        Schema::dropIfExists('lavages');
        Schema::dropIfExists('cleaners');
    }
};

