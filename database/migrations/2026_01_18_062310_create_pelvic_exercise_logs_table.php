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
        Schema::create('pelvic_exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('log_date');

            $table->integer('duration_seconds')->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('skipped')->default(false);

            $table->integer('streak_count')->default(0);
            $table->text('tip_shown')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelvic_exercise_logs');
    }
};
