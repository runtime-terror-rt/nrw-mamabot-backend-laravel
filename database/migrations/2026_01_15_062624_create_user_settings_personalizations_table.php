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
        Schema::create('user_settings_personalizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('AI_tone',['empathetic','casual','professional'])->default('empathetic');
            $table->enum('chatbot_speed',['normal','calm','fast'])->default('normal');
            $table->enum('background_sound',['enabled','disabled'])->default('disabled');
            $table->boolean('motherhood_context')->default(false);
            $table->boolean('activity_awareness')->default(false);
            $table->boolean('personalized_nutrition')->default(false);
            $table->enum('reminder_style',['normal','loud'])->default('normal');
            $table->boolean('mood_tracking')->default(false);
            $table->boolean('voice_feedback')->default(false);
            $table->boolean('analytics_cookies')->default(false);
            $table->boolean('two_factor_auth')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings_personalizations');
    }
};
