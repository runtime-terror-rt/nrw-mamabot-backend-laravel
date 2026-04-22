<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePregnancyFoodMealPlansTable extends Migration
{
    public function up()
    {
        Schema::create('pregnancy_food_meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('food_item');
            $table->string('dietary_preference')->nullable();

            // storing meal plan JSON
            $table->json('meal_plan');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pregnancy_food_meal_plans');
    }
}
