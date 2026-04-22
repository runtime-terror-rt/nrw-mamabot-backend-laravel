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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->text('name'); 
            $table->integer('price'); 
            $table->enum('billing_cycle', ['monthly', 'yearly', 'lifetime']); 
            $table->enum('plan_type', ['free', 'premium']); 
            $table->string('limit')->nullable();
            $table->json('features')->nullable();
            $table->text('description')->nullable(); 
            $table->boolean('is_active')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
