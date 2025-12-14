<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            // Donor
            $table->string('donor_name')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('donor_email')->nullable();
            $table->string('message', 255)->nullable();

            // Nominal (rupiah)
            $table->unsignedBigInteger('amount');

            // Midtrans
            $table->string('order_id')->unique();
            $table->string('snap_token')->nullable();

            $table->enum('payment_status', ['PENDING', 'PAID', 'EXPIRED', 'FAILED', 'REFUNDED'])
                ->default('PENDING');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['campaign_id', 'payment_status']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
