<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamp('validity_from')->nullable();
            $table->timestamp('validity_to')->nullable();
            $table->integer('usage_limit')->default(1);
            $table->integer('quota')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->unique(['event_id', 'name']);
            $table->index('event_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
