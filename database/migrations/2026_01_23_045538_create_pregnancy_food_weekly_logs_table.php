<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pregnancy_food_weekly_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('pregnancy_week');
            $table->string('dietary_preference')->nullable();

            $table->json('food_items')->nullable();  // request array
            $table->integer('week')->nullable();     // response week
            $table->json('daily_plan')->nullable();  // response daily plan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancy_food_weekly_logs');
    }
};
