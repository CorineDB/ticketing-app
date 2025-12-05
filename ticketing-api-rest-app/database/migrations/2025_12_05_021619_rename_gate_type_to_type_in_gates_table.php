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
        Schema::table('gates', function (Blueprint $table) {
            $table->renameColumn('gate_type', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gates', function (Blueprint $table) {
            $table->renameColumn('type', 'gate_type');
        });
    }
};
