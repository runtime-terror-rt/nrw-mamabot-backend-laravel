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
        Schema::create('hydration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('log_date');
            $table->unsignedInteger('glass_count')->default(0);
            $table->integer('duration_seconds')->nullable(); // Optional session duration

            $table->timestamps();

            // Prevent duplicate log per user per date
            $table->unique(['user_id', 'log_date']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydration_logs');
    }
};
