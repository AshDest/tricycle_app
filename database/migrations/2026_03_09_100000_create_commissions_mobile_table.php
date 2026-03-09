<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions_mobile', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commission')->unique();
            $table->foreignId('collecteur_id')->constrained('collecteurs')->onDelete('cascade');
            $table->foreignId('transaction_mobile_id')->nullable()->constrained('transactions_mobile')->nullOnDelete();
            $table->enum('type_operation', ['envoi', 'retrait', 'depot', 'paiement_facture', 'autre']);
            $table->decimal('montant_operation', 15, 2);
            $table->decimal('commission', 12, 2);
            $table->enum('operateur', ['mpesa', 'airtel_money', 'orange_money', 'afrimoney']);
            $table->string('numero_client', 50)->nullable();
            $table->string('reference_operation', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('ajoutee_au_solde')->default(false);
            $table->timestamp('ajoutee_au_solde_at')->nullable();
            $table->timestamp('date_operation')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['collecteur_id', 'date_operation']);
            $table->index('operateur');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('commissions_mobile');
    }
};
