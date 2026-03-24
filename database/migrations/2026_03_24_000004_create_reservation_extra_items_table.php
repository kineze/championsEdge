<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_extra_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_extra_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('price_per_unit', 12, 2);
            $table->string('unit_type', 50);
            $table->decimal('units', 10, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_extra_items');
    }
};
