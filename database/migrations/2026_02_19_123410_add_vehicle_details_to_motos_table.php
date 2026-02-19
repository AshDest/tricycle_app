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
            $table->string('marque', 100)->nullable()->after('numero_chassis');
            $table->string('modele', 100)->nullable()->after('marque');
            $table->year('annee_fabrication')->nullable()->after('modele');
            $table->string('couleur', 50)->nullable()->after('annee_fabrication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motos', function (Blueprint $table) {
            $table->dropColumn(['marque', 'modele', 'annee_fabrication', 'couleur']);
        });
    }
};
