<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePregnancyFoodRecipesTable extends Migration
{
    public function up()
    {
        Schema::create('pregnancy_food_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('food_item');
            $table->string('dietary_preference')->nullable();
            $table->json('recipes'); // API response stored here
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pregnancy_food_recipes');
    }
}
