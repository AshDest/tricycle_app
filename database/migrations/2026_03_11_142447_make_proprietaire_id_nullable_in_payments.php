<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rendre proprietaire_id nullable pour les paiements qui ne concernent pas un propriétaire
     * (ex: paiements depuis la caisse OKAMI ou lavage)
     */
    public function up(): void
    {
        // Supprimer la contrainte de clé étrangère existante
        DB::statement('ALTER TABLE payments DROP FOREIGN KEY payments_proprietaire_id_foreign');

        // Modifier la colonne pour être nullable
        DB::statement('ALTER TABLE payments MODIFY COLUMN proprietaire_id BIGINT UNSIGNED NULL');

        // Recréer la contrainte de clé étrangère avec NULL ON DELETE
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_proprietaire_id_foreign FOREIGN KEY (proprietaire_id) REFERENCES proprietaires(id) ON DELETE SET NULL');

        // Ajouter 'lavage' comme option de source_caisse si ce n'est pas déjà fait
        DB::statement("ALTER TABLE payments MODIFY COLUMN source_caisse ENUM('proprietaire', 'okami', 'lavage') DEFAULT 'proprietaire'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert source_caisse
        DB::statement("ALTER TABLE payments MODIFY COLUMN source_caisse ENUM('proprietaire', 'okami') DEFAULT 'proprietaire'");

        // Supprimer la contrainte
        DB::statement('ALTER TABLE payments DROP FOREIGN KEY payments_proprietaire_id_foreign');

        // Remettre NOT NULL (attention: peut échouer s'il y a des valeurs NULL)
        DB::statement('ALTER TABLE payments MODIFY COLUMN proprietaire_id BIGINT UNSIGNED NOT NULL');

        // Recréer la contrainte CASCADE
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_proprietaire_id_foreign FOREIGN KEY (proprietaire_id) REFERENCES proprietaires(id) ON DELETE CASCADE');
    }
};
