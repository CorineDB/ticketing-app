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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // FedaPay references
            $table->string('fedapay_transaction_id')->unique()->nullable();
            $table->string('fedapay_reference')->nullable();

            // Amount (decimal pour supporter les centimes)
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('XOF');

            // Status
            $table->enum('status', ['pending', 'approved', 'canceled', 'failed', 'refunded'])
                ->default('pending');

            // Event details
            $table->uuid('event_id')->nullable();
            $table->string('event_title')->nullable();
            $table->dateTime('event_start_date')->nullable();
            $table->dateTime('event_end_date')->nullable();
            $table->text('event_location')->nullable();

            // Customer details
            $table->string('customer_firstname');
            $table->string('customer_lastname');
            $table->string('customer_email');
            $table->string('customer_phone', 50)->nullable();

            // Purchase details (peut contenir plusieurs types)
            $table->integer('ticket_count')->default(1);
            $table->json('ticket_types_summary')->nullable();  // [{type: 'VIP', count: 2}]

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');

            // Indexes
            $table->index('fedapay_transaction_id');
            $table->index('customer_email');
            $table->index('event_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
