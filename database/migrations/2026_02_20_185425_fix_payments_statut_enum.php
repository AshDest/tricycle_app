<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix statut enum to use values without accents for consistency
     */
    public function up(): void
    {
        // D'abord, mettre à jour les valeurs existantes
        DB::statement("UPDATE payments SET statut = 'paye' WHERE statut = 'payé'");
        DB::statement("UPDATE payments SET statut = 'rejete' WHERE statut = 'rejeté'");

        // Modifier l'enum pour utiliser des valeurs sans accents
        DB::statement("ALTER TABLE payments MODIFY COLUMN statut ENUM('en_attente', 'approuve', 'paye', 'rejete') NOT NULL DEFAULT 'en_attente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN statut ENUM('en_attente', 'approuve', 'payé', 'rejeté') NOT NULL DEFAULT 'en_attente'");
    }
};
