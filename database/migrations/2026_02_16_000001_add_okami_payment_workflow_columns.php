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
        // Ajouter des colonnes au modèle Payment pour la demande OKAMI
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('demande_par')->nullable()->after('traite_par')->constrained('users')->nullOnDelete();
            $table->timestamp('demande_at')->nullable()->after('demande_par');
            $table->foreignId('valide_par')->nullable()->after('demande_at')->constrained('users')->nullOnDelete();
            $table->timestamp('valide_at')->nullable()->after('valide_par');
            $table->string('numero_envoi')->nullable()->after('reference_paiement');
            $table->text('notes_validation')->nullable()->after('notes');
        });

        // Ajouter des colonnes à la table collectes pour le dépôt du caissier
        Schema::table('collectes', function (Blueprint $table) {
            $table->boolean('valide_par_collecteur')->default(false)->after('statut');
            $table->timestamp('valide_collecteur_at')->nullable()->after('valide_par_collecteur');
            $table->text('notes_collecteur')->nullable()->after('notes_anomalies');
        });

        // Ajouter solde_caissier pour suivre le solde du caissier
        Schema::table('caissiers', function (Blueprint $table) {
            if (!Schema::hasColumn('caissiers', 'solde_actuel')) {
                $table->decimal('solde_actuel', 15, 2)->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['demande_par']);
            $table->dropForeign(['valide_par']);
            $table->dropColumn(['demande_par', 'demande_at', 'valide_par', 'valide_at', 'numero_envoi', 'notes_validation']);
        });

        Schema::table('collectes', function (Blueprint $table) {
            $table->dropColumn(['valide_par_collecteur', 'valide_collecteur_at', 'notes_collecteur']);
        });
    }
};
