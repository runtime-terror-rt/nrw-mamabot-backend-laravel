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
        Schema::create('diaper_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->enum('diaper_type', ['wet', 'dirty', 'both'])->nullable();
            $table->text('tip')->nullable();
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
        Schema::dropIfExists('diaper_logs');
    }
};
