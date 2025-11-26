<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->string('payment_url')->nullable()->after('quota');
            $table->string('payment_transaction_id')->nullable()->after('payment_url');
            $table->string('payment_token')->nullable()->after('payment_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'payment_transaction_id', 'payment_token']);
        });
    }
};
