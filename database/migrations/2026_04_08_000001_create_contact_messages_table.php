<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('nom', 100);
                $table->string('email', 150);
                $table->string('telephone', 20)->nullable();
                $table->string('sujet', 200);
                $table->text('message');
                $table->string('ip_address', 45)->nullable();
                $table->boolean('is_read')->default(false);
                $table->text('reponse')->nullable();
                $table->timestamp('repondu_at')->nullable();
                $table->foreignId('repondu_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};

