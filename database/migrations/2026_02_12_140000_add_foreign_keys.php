<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajout des contraintes de clés étrangères après création de toutes les tables
     */
    public function up(): void
    {
        // Ajouter la contrainte FK versements -> collectes
        Schema::table('versements', function (Blueprint $table) {
            $table->foreign('collecte_id')
                  ->references('id')
                  ->on('collectes')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropForeign(['collecte_id']);
        });
    }
};
