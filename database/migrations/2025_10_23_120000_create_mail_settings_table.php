<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('provider')->default('smtp'); // smtp, mailgun, ses
            $table->string('sending_domain')->nullable(); // ex.: mg.exemplo.com
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->text('credentials_encrypted')->nullable(); // JSON criptografado específico do provedor
            $table->json('dns_requirements')->nullable(); // Registros esperados calculados
            $table->json('dns_status')->nullable(); // Resultado da última verificação
            $table->boolean('verified')->default(false); // Libera envio quando true
            $table->boolean('active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};