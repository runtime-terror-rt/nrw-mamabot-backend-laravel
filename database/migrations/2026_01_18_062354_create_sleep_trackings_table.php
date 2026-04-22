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
        Schema::create('sleep_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('log_date'); // <-- add this

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->enum('sleep_type', ['nap', 'night'])->nullable();
            $table->enum('sleep_quality',['calm','restless','interrupted'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('delivery_type', ['vaginal', 'cesarean'])->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'log_date']); // one log per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleep_trackings');
    }
};
