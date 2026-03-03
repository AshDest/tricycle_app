<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les paramètres de prix de lavage
        SystemSetting::set('prix_lavage_simple', 2000, 'integer', 'lavage');
        SystemSetting::set('prix_lavage_complet', 3500, 'integer', 'lavage');
        SystemSetting::set('prix_lavage_premium', 5000, 'integer', 'lavage');
        SystemSetting::set('part_okami_lavage_percent', 20, 'integer', 'lavage');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('group', 'lavage')->delete();
    }
};

