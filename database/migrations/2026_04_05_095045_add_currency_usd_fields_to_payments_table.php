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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('montant_usd', 12, 2)->nullable();
            $table->decimal('taux_conversion', 12, 2)->nullable();
        });

        // Seed the exchange rate system setting (default: 2800 CDF for 1 USD)
        \App\Models\SystemSetting::set('taux_usd_cdf', 2800, 'decimal', 'general');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['montant_usd', 'taux_conversion']);
        });

        \App\Models\SystemSetting::where('key', 'taux_usd_cdf')->delete();
    }
};
