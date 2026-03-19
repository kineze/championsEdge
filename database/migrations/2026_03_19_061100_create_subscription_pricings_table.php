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
        Schema::create('subscription_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('age_group_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->enum('frequency', ['monthly', 'yearly']);
            $table->timestamps();

            $table->unique(['facility_id', 'age_group_id', 'frequency'], 'subscription_pricing_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_pricings');
    }
};
