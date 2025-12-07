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
        Schema::create('event_gate', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('gate_id');
            $table->uuid('agent_id')->nullable();

            // Configuration spécifique pour cet événement
            $table->enum('operational_status', ['active', 'inactive', 'paused'])
                ->default('active');
            $table->json('schedule')->nullable(); // Horaires pour cet événement
            $table->json('ticket_type_ids')->nullable(); // Types acceptés pour cet événement
            $table->integer('max_capacity')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('gate_id')->references('id')->on('gates')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['event_id', 'gate_id']);
            $table->index('agent_id');

            // Une gate ne peut être assignée qu'une fois par événement
            $table->unique(['event_id', 'gate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_gate');
    }
};
