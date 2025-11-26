<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_counters', function (Blueprint $table) {
            $table->uuid('event_id')->primary();
            $table->integer('current_in')->default(0);
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_counters');
    }
};
