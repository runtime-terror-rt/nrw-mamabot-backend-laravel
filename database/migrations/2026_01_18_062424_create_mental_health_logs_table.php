<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mental_health_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');

            $table->enum('mood', ['calm', 'tired', 'sad', 'overwhelmed', 'anxious'])->nullable();
            $table->enum('energy_level',['low','medium','good'])->nullable();
            $table->enum('sleep_quality',['poor','fair','good'])->nullable();

            $table->text('tip')->nullable();

            $table->timestamps();

            // unique per user per date
            $table->unique(['user_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mental_health_logs');
    }
};
