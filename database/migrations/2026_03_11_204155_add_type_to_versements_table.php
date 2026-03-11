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
            // Type de versement: 'journalier' (normal), 'arrieres_only' (remboursement d'arriérés uniquement)
            $table->string('type')->nullable()->default('journalier')->after('mode_paiement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
