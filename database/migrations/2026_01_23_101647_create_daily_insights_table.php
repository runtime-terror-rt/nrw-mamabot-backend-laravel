<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_insights', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('mode');          // pregnancy | postpartum
            $table->string('language')->nullable();  // nullable now

            $table->integer('pregnancy_week')->nullable();
            $table->integer('postpartum_day')->nullable();
            $table->string('delivery_type')->nullable();

            $table->text('insight');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_insights');
    }
};
