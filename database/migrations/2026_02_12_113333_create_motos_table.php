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
        Schema::create('motos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_matricule')->unique();
            $table->string('plaque_immatriculation')->unique();
            $table->string('numero_chassis')->unique();
            $table->foreignId('proprietaire_id')->constrained('proprietaires')->cascadeOnDelete();
            $table->foreignId('motard_id')->nullable()->constrained('motards')->nullOnDelete();
            $table->string('photo_url')->nullable();
            $table->string('document_administratif_url')->nullable();
            $table->enum('statut', ['actif', 'suspendu', 'maintenance'])->default('actif');
            $table->decimal('montant_journalier_attendu', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motos');
    }
};
