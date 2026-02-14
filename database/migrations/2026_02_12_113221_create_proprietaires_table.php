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
     * Proprietaire = Bailleur qui possède des motos-tricycles.
     */
    public function up(): void
    {
        Schema::create('proprietaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('raison_sociale')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();

            // Comptes pour les paiements Mobile Money
            $table->string('numero_compte_mpesa')->nullable();
            $table->string('numero_compte_airtel')->nullable();
            $table->string('numero_compte_orange')->nullable();

            // Compte bancaire
            $table->string('numero_compte_bancaire')->nullable();
            $table->string('banque_nom')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proprietaires');
    }
};
