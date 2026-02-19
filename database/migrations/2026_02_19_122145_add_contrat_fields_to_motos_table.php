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
        Schema::table('motos', function (Blueprint $table) {
            $table->date('contrat_debut')->nullable()->after('montant_journalier_attendu');
            $table->date('contrat_fin')->nullable()->after('contrat_debut');
            $table->string('contrat_numero')->nullable()->after('contrat_fin');
            $table->text('contrat_notes')->nullable()->after('contrat_numero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motos', function (Blueprint $table) {
            $table->dropColumn(['contrat_debut', 'contrat_fin', 'contrat_numero', 'contrat_notes']);
        });
    }
};

