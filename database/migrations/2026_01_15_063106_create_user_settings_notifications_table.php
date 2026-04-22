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
        Schema::create('user_settings_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('health_wellness')->default(false);
            $table->boolean('baby_movement_recovery')->default(false);
            $table->boolean('community')->default(false);
            $table->boolean('recommendation')->default(false);
            $table->boolean('mindful_moments')->default(false);
            $table->boolean('announcements')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings_notifications');
    }
};
