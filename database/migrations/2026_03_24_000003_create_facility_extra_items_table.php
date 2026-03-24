<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_extra_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price_per_unit', 12, 2);
            $table->string('unit_type', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_extra_items');
    }
};
