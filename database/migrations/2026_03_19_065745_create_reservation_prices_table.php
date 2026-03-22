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
        Schema::create('reservation_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->enum('range_type', ['per_hour']);
            $table->decimal('price', 10, 2);
            $table->boolean('is_deposit_required')->default(false);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['facility_id', 'range_type'], 'reservation_price_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_prices');
    }
};
