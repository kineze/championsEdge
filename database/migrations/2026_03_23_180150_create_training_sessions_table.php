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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_title');
            $table->string('display_image')->nullable();
            $table->foreignId('trainer_id')->constrained('users');
            $table->foreignId('facility_id')->constrained('facilities');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['monthly'])->default('monthly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
