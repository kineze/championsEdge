<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'payment_status')) {
                $table->string('payment_status', 30)->default('pending')->after('status')->index();
            }

            if (!Schema::hasColumn('reservations', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }

            if (Schema::hasColumn('reservations', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
