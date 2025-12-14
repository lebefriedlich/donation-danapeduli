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
        Schema::create('campaign_updates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('content');
            $table->string('attachment')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('disbursed_amount')->nullable()->after('content');
            $table->boolean('is_financial_update')->default(false)->after('disbursed_amount');

            $table->timestamps();

            $table->index(['campaign_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_updates');
    }
};
