<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Notifications du système pour tous les utilisateurs.
     * Types: retard_paiement, validation_versement, incident, arrieres_critique,
     *        ramassage_prevu, accident, reparation, maintenance, etc.
     */
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Type et contenu
            $table->string('type'); // retard_paiement, validation_versement, etc.
            $table->string('titre');
            $table->text('message');
            $table->string('icon')->nullable(); // Icône à afficher
            $table->string('couleur')->default('blue'); // Couleur de l'alerte

            // Lien vers l'entité concernée
            $table->string('notifiable_type')->nullable(); // App\Models\Versement, etc.
            $table->unsignedBigInteger('notifiable_id')->nullable();

            // Statut
            $table->boolean('lu')->default(false);
            $table->timestamp('lu_at')->nullable();

            // Priorité
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');

            // Expiration (optionnel)
            $table->timestamp('expire_at')->nullable();

            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['user_id', 'lu']);
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};

