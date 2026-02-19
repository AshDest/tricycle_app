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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, decimal, boolean, json
            $table->string('group')->default('general'); // general, versements, motos, etc.
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        \DB::table('system_settings')->insert([
            [
                'key' => 'montant_journalier_defaut',
                'value' => '5000',
                'type' => 'decimal',
                'group' => 'versements',
                'label' => 'Montant journalier par défaut',
                'description' => 'Montant journalier attendu par défaut pour les motos sans tarif défini',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seuil_arriere_faible',
                'value' => '25000',
                'type' => 'decimal',
                'group' => 'versements',
                'label' => 'Seuil arriéré faible',
                'description' => 'Montant à partir duquel un arriéré est considéré comme faible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seuil_arriere_moyen',
                'value' => '50000',
                'type' => 'decimal',
                'group' => 'versements',
                'label' => 'Seuil arriéré moyen',
                'description' => 'Montant à partir duquel un arriéré est considéré comme moyen',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seuil_arriere_critique',
                'value' => '100000',
                'type' => 'decimal',
                'group' => 'versements',
                'label' => 'Seuil arriéré critique',
                'description' => 'Montant à partir duquel un arriéré est considéré comme critique',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'devise',
                'value' => 'FC',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Devise',
                'description' => 'Symbole de la devise utilisée',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'nom_societe',
                'value' => 'New Technology Hub Sarl',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nom de la société',
                'description' => 'Nom de la société gestionnaire',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
