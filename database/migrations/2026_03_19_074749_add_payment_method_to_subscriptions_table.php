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
        if (!Schema::hasColumn('subscriptions', 'payment_method')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('payment_method', 50)->nullable()->after('facility_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('subscriptions', 'payment_method')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }
    }
};
