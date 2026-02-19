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
        Schema::table('versements', function (Blueprint $table) {
            $table->decimal('arrieres', 12, 2)->default(0)->after('montant_attendu')
                  ->comment('Montant des arriérés (différence entre attendu et versé)');
        });

        // Mettre à jour les arriérés pour les versements existants
        \DB::statement('UPDATE versements SET arrieres = GREATEST(0, COALESCE(montant_attendu, 0) - COALESCE(montant, 0))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropColumn('arrieres');
        });
    }
};
