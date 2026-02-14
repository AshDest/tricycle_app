<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Le caissier est un point de collecte terrain où les motards versent leur argent.
     */
    public function up(): void
    {
        Schema::create('caissiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('numero_identifiant')->unique();
            $table->string('nom_point_collecte'); // Ex: "Caisse Limete Centre"
            $table->string('zone')->default('Kinshasa');
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->decimal('solde_actuel', 15, 2)->default(0); // Argent en caisse non encore collecté
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caissiers');
    }
};

