<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postpartum_daily_tips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('day'); // postpartum day
            $table->string('delivery_type'); // vaginal or cesarean
            $table->string('language')->nullable(); // en or de
            $table->text('tip'); // tip text
            $table->text('description'); // explanation text

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postpartum_daily_tips');
    }
};
