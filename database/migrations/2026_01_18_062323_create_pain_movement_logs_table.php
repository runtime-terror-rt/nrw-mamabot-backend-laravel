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
        Schema::create('pain_movement_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');

            $table->integer('pain_level')->nullable();
            $table->json('discomfort_areas')->nullable();

            $table->enum('movement_status', [
                'Difficulty walking',
                'Pain when sitting',
                'Hard to bend',
                'Limited mobility',
                'Normal movement'
            ])->default('Normal movement');

            $table->text('tip_shown')->nullable();
            $table->text('notes')->nullable();
             $table->enum('delivery_type', ['vaginal', 'cesarean'])->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'log_date']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pain_movement_logs');
    }
};
