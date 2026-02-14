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
        Schema::table('maintenances', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            if (!Schema::hasColumn('maintenances', 'technicien_telephone')) {
                $table->string('technicien_telephone')->nullable()->after('technicien_garage_nom');
            }
            if (!Schema::hasColumn('maintenances', 'garage_adresse')) {
                $table->text('garage_adresse')->nullable()->after('technicien_telephone');
            }
            if (!Schema::hasColumn('maintenances', 'valide_par')) {
                $table->foreignId('valide_par')->nullable()->constrained('users')->after('statut');
            }
            if (!Schema::hasColumn('maintenances', 'valide_at')) {
                $table->timestamp('valide_at')->nullable()->after('valide_par');
            }
            if (!Schema::hasColumn('maintenances', 'notes')) {
                $table->text('notes')->nullable()->after('valide_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumnIfExists('technicien_telephone');
            $table->dropColumnIfExists('garage_adresse');
            $table->dropForeignIdFor('valide_par');
            $table->dropColumnIfExists('valide_at');
            $table->dropColumnIfExists('notes');
        });
    }
};
