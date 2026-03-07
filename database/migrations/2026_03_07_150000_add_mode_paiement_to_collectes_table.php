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
        Schema::table('collectes', function (Blueprint $table) {
            $table->enum('mode_paiement', ['cash', 'mpesa', 'airtel_money', 'orange_money', 'afrimoney'])
                  ->default('cash')
                  ->after('statut');
            $table->string('numero_transaction_mobile', 100)->nullable()->after('mode_paiement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collectes', function (Blueprint $table) {
            $table->dropColumn(['mode_paiement', 'numero_transaction_mobile']);
        });
    }
};

