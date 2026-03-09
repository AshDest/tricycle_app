<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne table commissions_mobile
        Schema::dropIfExists('commissions_mobile');
        // Nouvelle table pour les commissions Mobile Money (mensuelle)
        Schema::create('commissions_mobile_mensuelles', function (Blueprint $table) {
            $table->id();
            $table->string('numero_reference')->unique();
            $table->foreignId('collecteur_id')->constrained('collecteurs')->onDelete('cascade');
            $table->year('annee');
            $table->unsignedTinyInteger('mois'); // 1-12
            $table->decimal('montant_total', 15, 2); // Montant total de la commission
            $table->decimal('part_nth', 15, 2); // 70% pour NTH
            $table->decimal('part_okami', 15, 2); // 30% pour OKAMI
            $table->string('preuve_paiement')->nullable(); // Chemin du fichier
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_at')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Une seule commission par mois par collecteur
            $table->unique(['collecteur_id', 'annee', 'mois'], 'unique_commission_mensuelle');
            $table->index(['annee', 'mois']);
        });
        // Table pour les bénéfices de change de monnaie
        Schema::create('benefices_change', function (Blueprint $table) {
            $table->id();
            $table->string('numero_reference')->unique();
            $table->foreignId('collecteur_id')->constrained('collecteurs')->onDelete('cascade');
            $table->date('date_operation');
            $table->enum('type_saisie', ['journalier', 'hebdomadaire', 'mensuel']);
            $table->decimal('montant_recu_caissier', 15, 2)->nullable(); // Montant reçu du caissier
            $table->decimal('solde_general_caisse', 15, 2)->nullable(); // Solde général de la caisse
            $table->decimal('benefice', 15, 2); // Bénéfice réalisé
            $table->text('commentaire')->nullable();
            // Pour les saisies hebdomadaires/mensuelles
            $table->date('periode_debut')->nullable();
            $table->date('periode_fin')->nullable();
            // Audit et validation
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_at')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['collecteur_id', 'date_operation']);
            $table->index(['collecteur_id', 'type_saisie']);
            $table->index('statut');
        });
        // Table pour l'historique des modifications (traçabilité)
        Schema::create('audit_benefices_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // commissions_mobile_mensuelles ou benefices_change
            $table->unsignedBigInteger('auditable_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['creation', 'modification', 'validation', 'rejet', 'suppression']);
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_benefices_commissions');
        Schema::dropIfExists('benefices_change');
        Schema::dropIfExists('commissions_mobile_mensuelles');
    }
};
