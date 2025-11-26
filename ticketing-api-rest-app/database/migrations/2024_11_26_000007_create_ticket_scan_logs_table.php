<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_scan_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ticket_id');
            $table->uuid('agent_id')->nullable();
            $table->uuid('gate_id')->nullable();
            $table->enum('scan_type', ['entry', 'exit'])->default('entry');
            $table->timestamp('scan_time')->useCurrent();
            $table->enum('result', ['ok', 'already_in', 'already_out', 'invalid', 'expired', 'capacity_full']);
            $table->json('details')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('gate_id')->references('id')->on('gates')->onDelete('set null');

            $table->index('ticket_id');
            $table->index('created_at');
            $table->index('scan_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scan_logs');
    }
};
