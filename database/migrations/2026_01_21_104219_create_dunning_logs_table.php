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
        Schema::create('dunning_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('offset_type'); // pre, post, due
            $table->integer('offset_days');
            $table->string('channel'); // email, whatsapp
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['school_id', 'invoice_id']);
            $table->index(['invoice_id', 'offset_type', 'offset_days', 'channel'], 'dunning_uniqueness');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dunning_logs');
    }
};
