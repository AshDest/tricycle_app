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
        Schema::table('collecteurs', function (Blueprint $table) {
            $table->decimal('solde_caisse', 15, 2)->default(0)->after('zone_affectation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collecteurs', function (Blueprint $table) {
            $table->dropColumn('solde_caisse');
        });
    }
};
