<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute la possibilité de distinguer les paiements:
     * - Depuis la caisse Propriétaire (vers un propriétaire)
     * - Depuis la caisse OKAMI (vers un bénéficiaire quelconque)
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Type de source: 'proprietaire' ou 'okami'
            $table->enum('source_caisse', ['proprietaire', 'okami'])->default('proprietaire')->after('proprietaire_id');

            // Pour les paiements depuis la caisse OKAMI (si source_caisse = 'okami')
            $table->string('beneficiaire_nom')->nullable()->after('source_caisse');
            $table->string('beneficiaire_telephone')->nullable()->after('beneficiaire_nom');
            $table->text('beneficiaire_motif')->nullable()->after('beneficiaire_telephone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['source_caisse', 'beneficiaire_nom', 'beneficiaire_telephone', 'beneficiaire_motif']);
        });
    }
};

