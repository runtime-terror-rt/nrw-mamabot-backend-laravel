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
       Schema::create('feeding_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('log_date'); // Feeding date
            $table->time('feeding_time'); // Feeding time (e.g., 02:04 PM)

            $table->enum('feeding_method', [
                'breastfeeding',
                'bottle_formula',
                'bottle_pumped',
                'mixed_feeding'
            ])->nullable();

            $table->integer('duration_left')->nullable(); // Breastfeeding left side
            $table->integer('duration_right')->nullable(); // Breastfeeding right side

            $table->enum('latch_quality', [
                'good',
                'difficult',
                'painful'
            ])->nullable();
            // $table->enum('delivery_type', ['vaginal', 'cesarean'])->nullable();
            $table->enum('delivery_type', ['vaginal', 'cesarean'])->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'log_date', 'feeding_time']); // Prevent duplicate logs
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeding_logs');
    }
};
