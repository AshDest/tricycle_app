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
        // Modifier l'enum pour ajouter 'cash'
        DB::statement("ALTER TABLE payments MODIFY COLUMN mode_paiement ENUM('cash', 'mpesa', 'airtel_money', 'orange_money', 'virement_bancaire') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN mode_paiement ENUM('mpesa', 'airtel_money', 'orange_money', 'virement_bancaire') NOT NULL");
    }
};
