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
        Schema::table('facility_extra_items', function (Blueprint $table) {
            $table->foreignId('inventory_id')
                ->nullable()
                ->after('facility_id')
                ->constrained('inventories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_extra_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_id');
        });
    }
};
