<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute un champ motard_secondaire_id pour permettre qu'un motard remplaçant
     * puisse effectuer un versement à la place du motard titulaire.
     * Si null => c'est le motard titulaire qui a travaillé et versé.
     */
    public function up(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->foreignId('motard_secondaire_id')
                  ->nullable()
                  ->after('motard_id')
                  ->constrained('motards')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropForeign(['motard_secondaire_id']);
            $table->dropColumn('motard_secondaire_id');
        });
    }
};
