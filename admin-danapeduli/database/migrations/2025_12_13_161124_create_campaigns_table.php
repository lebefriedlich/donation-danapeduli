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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');

            // DONATION = donasi umum (boleh tanpa target)
            // CROWDFUND = galang dana (wajib target)
            $table->enum('type', ['DONATION', 'CROWDFUND'])->default('DONATION');

            // AMOUNT = pakai target nominal
            // NONE = tanpa target (khusus DONATION)
            $table->enum('goal_type', ['AMOUNT', 'NONE'])->default('AMOUNT');

            // Target & progress (rupiah, integer)
            $table->unsignedBigInteger('target_amount')->default(0);
            $table->unsignedBigInteger('total_paid')->default(0);

            $table->enum('status', ['DRAFT', 'ACTIVE', 'CLOSED', 'ARCHIVED'])->default('DRAFT');

            // Jadwal
            $table->timestamp('open_at')->nullable();
            $table->timestamp('close_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Auto close saat target tercapai (biasanya untuk CROWDFUND)
            $table->boolean('auto_close_on_target')->default(true);

            $table->string('cover_image')->nullable();

            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['status', 'open_at', 'close_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
