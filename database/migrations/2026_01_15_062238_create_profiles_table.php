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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('address')->nullable();
            $table->string('image')->nullable();
            $table->string('language')->nullable();
            $table->enum('pregnancy_status',['pregnancy', 'postpartum'])
                ->default('pregnancy');
            $table->enum('delivery_type', ['vaginal_delivery', 'cesarean_delivery'])->nullable();
            $table->integer('postpartum_day')->default(0);
            $table->date('due_date')->nullable();
            $table->integer('current_week')->nullable();
            $table->string('baby_nickname')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('hospital_name')->nullable();
            $table->boolean('isKickRemind')->nullable();
            $table->boolean('isHydrationGoal')->nullable();
            $table->boolean('isWeightTrack')->nullable();
            $table->string('AI_tone')->nullable();
            $table->string('support_type')->nullable();
            $table->string('product_interest')->nullable();
            $table->string('dietary_preferences')->nullable();
            $table->boolean('two_factor_auth')->nullable();
            $table->string('pregnancy_document')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
