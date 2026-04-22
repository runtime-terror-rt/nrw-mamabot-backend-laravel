<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('baby_movement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Session metadata
            $table->date('log_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            // Movement tracking
            $table->unsignedSmallInteger('kick_count')->default(0);
            $table->enum('movement_status', ['normal', 'reduced', 'urgent'])->default('normal');
            $table->text('note')->nullable();

            // Pregnancy context
            $table->unsignedTinyInteger('pregnancy_week')->nullable();

            $table->timestamps();

            // prevent duplicate session
            $table->unique(['user_id', 'log_date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baby_movement_logs');
    }
};
