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
        Schema::table('tickets', function (Blueprint $table) {
            $table->uuid('payment_id')->nullable()->after('ticket_type_id');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropIndex(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
};
