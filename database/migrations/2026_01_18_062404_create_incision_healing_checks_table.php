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
        Schema::create('incision_healing_checks', function (Blueprint $table) {
           $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');

            // Step 1: Visual symptoms
            $table->enum('redness', ['none','mild','moderate','severe'])->nullable();
            $table->boolean('swelling')->default(false);
            $table->boolean('warmth')->default(false);
            $table->boolean('tenderness')->default(false);

            // Step 2: Pain & sensations
            $table->integer('pain_score')->nullable(); // 0–10 scale
            $table->json('sensations')->nullable(); // multi-select

            // Step 3: Infection signs
            $table->boolean('chills_fever')->default(false);
            $table->enum('discharge_type', ['none','clear','bloody','yellow'])->default('none');

            // Step 5: Summary
            $table->enum('healing_status', ['normal','attention_needed','urgent'])->default('normal');
            $table->text('guidance')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incision_healing_checks');
    }
};
