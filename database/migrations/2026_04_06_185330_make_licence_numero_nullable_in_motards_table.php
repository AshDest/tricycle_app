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
        // 1. D'abord rendre la colonne nullable
        Schema::table('motards', function (Blueprint $table) {
            $table->string('licence_numero')->nullable()->change();
        });

        // 2. Ensuite convertir les chaînes vides en NULL
        DB::table('motards')->where('licence_numero', '')->update(['licence_numero' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('motards')->whereNull('licence_numero')->update(['licence_numero' => '']);

        Schema::table('motards', function (Blueprint $table) {
            $table->string('licence_numero')->nullable(false)->change();
        });
    }
};
