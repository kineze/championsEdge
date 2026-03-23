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
        Schema::create('training_session_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_gateway_id')->unique();
            $table->string('session_id')->nullable();
            $table->string('success_indicator')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('LKR');
            $table->string('api_operation')->default('AUTHORIZE');
            $table->string('status', 20)->default('PENDING')->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_session_payments');
    }
};
