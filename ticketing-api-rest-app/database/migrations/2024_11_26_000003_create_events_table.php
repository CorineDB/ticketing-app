<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organisateur_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->string('location')->nullable();
            $table->integer('capacity')->default(0);
            $table->string('timezone')->default('UTC');
            $table->string('dress_code')->nullable();
            $table->boolean('allow_reentry')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('organisateur_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['organisateur_id', 'title']);
            $table->index('start_datetime');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
