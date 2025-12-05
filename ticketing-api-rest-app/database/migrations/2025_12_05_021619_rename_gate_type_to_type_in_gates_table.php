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
            // Only rename if gate_type exists
            if (Schema::hasColumn('gates', 'gate_type')) {
                $table->renameColumn('gate_type', 'type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gates', function (Blueprint $table) {
            // Only rename back if type exists
            if (Schema::hasColumn('gates', 'type')) {
                $table->renameColumn('type', 'gate_type');
            }
        });
    }
};
