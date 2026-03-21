<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kwado_services', function (Blueprint $table) {
            $table->id();
            $table->string('numero_service')->unique();
            $table->foreignId('cleaner_id')->constrained()->onDelete('cascade');

            $table->foreignId('moto_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_externe')->default(false);
            $table->string('plaque_externe')->nullable();
            $table->string('proprietaire_externe')->nullable();
            $table->string('telephone_externe')->nullable();

            $table->string('type_service');
            $table->string('description_service')->nullable();
            $table->string('position_pneu')->nullable();

            $table->decimal('prix', 10, 2);
            $table->decimal('cout_pieces', 10, 2)->default(0);
            $table->decimal('montant_encaisse', 10, 2);

            $table->enum('mode_paiement', ['cash', 'mobile_money'])->default('cash');
            $table->enum('statut_paiement', ['payé', 'en_attente', 'annulé'])->default('payé');
            $table->timestamp('date_service')->useCurrent();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kwado_services');
    }
};
