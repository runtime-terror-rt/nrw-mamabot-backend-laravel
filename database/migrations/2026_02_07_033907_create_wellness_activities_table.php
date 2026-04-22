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
        Schema::create('wellness_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('short_description');
            $table->enum('phase_type', ['pregnancy', 'postpartum']); 
            $table->enum('trimester', ['1', '2', '3', 'all'])->default('all'); 
            $table->string('duration');
            $table->string('image'); 
            $table->string('video_url')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_activities');
    }
};
