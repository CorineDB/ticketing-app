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
            // Rename gate_type to type for consistency
            if (Schema::hasColumn('gates', 'gate_type')) {
                $table->renameColumn('gate_type', 'type');
            }

            // Gates are now reusable entities
            // No event_id or agent_id here - these go in event_gate pivot
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gates', function (Blueprint $table) {
            if (Schema::hasColumn('gates', 'type')) {
                $table->renameColumn('type', 'gate_type');
            }
        });
    }
};
