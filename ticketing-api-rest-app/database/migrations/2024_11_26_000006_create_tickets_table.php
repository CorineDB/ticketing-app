<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('ticket_type_id')->nullable();
            $table->string('code');
            $table->string('qr_path')->nullable();
            $table->string('qr_hmac')->nullable();
            $table->string('magic_link_token')->nullable();
            $table->enum('status', ['issued', 'reserved', 'paid', 'in', 'out', 'invalid', 'refunded'])->default('issued');
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->uuid('gate_in')->nullable();
            $table->uuid('last_gate_out')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('set null');
            $table->foreign('gate_in')->references('id')->on('gates')->onDelete('set null');
            $table->foreign('last_gate_out')->references('id')->on('gates')->onDelete('set null');

            $table->unique(['event_id', 'code']);
            $table->index('event_id');
            $table->index('code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
