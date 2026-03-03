<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute la répartition de la caisse du collecteur entre part OKAMI et part Propriétaire
     */
    public function up(): void
    {
        Schema::table('collecteurs', function (Blueprint $table) {
            // Part OKAMI dans la caisse
            $table->decimal('solde_part_okami', 12, 2)->default(0)->after('solde_caisse');
            // Part Propriétaire dans la caisse
            $table->decimal('solde_part_proprietaire', 12, 2)->default(0)->after('solde_part_okami');
        });

        // Ajouter également les colonnes dans les collectes pour tracer la répartition
        Schema::table('collectes', function (Blueprint $table) {
            $table->decimal('part_okami', 12, 2)->default(0)->after('montant_collecte');
            $table->decimal('part_proprietaire', 12, 2)->default(0)->after('part_okami');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collecteurs', function (Blueprint $table) {
            $table->dropColumn(['solde_part_okami', 'solde_part_proprietaire']);
        });

        Schema::table('collectes', function (Blueprint $table) {
            $table->dropColumn(['part_okami', 'part_proprietaire']);
        });
    }
};

