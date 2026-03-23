<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 30);
            $table->string('nic', 40)->nullable()->index();
            $table->text('address')->nullable();
            $table->date('date_of_birth');
            $table->string('password_hash');
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_pricings')->cascadeOnDelete();
            $table->decimal('plan_price', 10, 2);
            $table->string('plan_frequency', 20);
            $table->string('currency', 10)->default('LKR');
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_registrations');
    }
};
