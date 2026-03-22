<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove per-day rows if a per-hour row already exists for the same facility.
        $facilityIdsWithPerHour = DB::table('reservation_prices')
            ->where('range_type', 'per_hour')
            ->select('facility_id');

        DB::table('reservation_prices')
            ->where('range_type', 'per_day')
            ->whereIn('facility_id', $facilityIdsWithPerHour)
            ->delete();

        // Convert remaining per-day rows into per-hour rows.
        DB::table('reservation_prices')
            ->where('range_type', 'per_day')
            ->update(['range_type' => 'per_hour']);

        // Tighten enum at DB level where supported.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE reservation_prices MODIFY range_type ENUM('per_hour') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE reservation_prices MODIFY range_type ENUM('per_hour','per_day') NOT NULL");
        }
    }
};
