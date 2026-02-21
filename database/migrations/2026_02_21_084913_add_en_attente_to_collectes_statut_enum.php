<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum pour ajouter 'en_attente'
        DB::statement("ALTER TABLE collectes MODIFY COLUMN statut ENUM('en_attente', 'reussie', 'partielle', 'non_realisee', 'en_litige') NOT NULL DEFAULT 'en_attente'");

        // Mettre à jour les collectes existantes sans dépôt comme 'en_attente'
        DB::statement("UPDATE collectes SET statut = 'en_attente' WHERE statut = 'non_realisee' AND montant_collecte = 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les 'en_attente' en 'non_realisee'
        DB::statement("UPDATE collectes SET statut = 'non_realisee' WHERE statut = 'en_attente'");

        // Revenir à l'enum original
        DB::statement("ALTER TABLE collectes MODIFY COLUMN statut ENUM('reussie', 'partielle', 'non_realisee', 'en_litige') NOT NULL DEFAULT 'non_realisee'");
    }
};
