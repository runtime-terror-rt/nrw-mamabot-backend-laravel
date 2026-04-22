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
        Schema::create('recovery_logs', function (Blueprint $table) {
             $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                $table->integer('pain_range')->nullable();
                $table->json('pain_type')->nullable();
                $table->enum('bleeding_today', ['None', 'Light', 'Moderate', 'Heavy'])->default('None');
                $table->boolean('clots_present')->default(false);

                $table->enum('energy_level', ['Very Low', 'Low', 'Normal', 'Good', 'High'])->default('Normal');
                $table->json('mood')->nullable();
                $table->text('notes')->nullable();

                $table->date('log_date');
                $table->timestamps();

                $table->unique(['user_id', 'log_date']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_logs');
    }
};
