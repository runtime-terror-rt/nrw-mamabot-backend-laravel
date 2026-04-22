<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();

            // user
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('chat_id')->nullable();

            // context (from profile)
            $table->enum('mode', ['pregnancy', 'postpartum'])->nullable();
            $table->integer('pregnancy_week')->nullable();
            $table->integer('postpartum_day')->nullable();
            $table->enum('delivery_type', ['vaginal', 'cesarean'])->nullable();

            $table->string('language', 5)->nullable();
            $table->string('country')->nullable();
            $table->string('dietary_preferences')->nullable();

            // AI behavior
            $table->string('tone_of_ai')->nullable();
            $table->string('support_type')->nullable();

            // messages
            $table->text('user_message')->nullable();
            $table->longText('ai_response')->nullable();

            // AI meta
            $table->boolean('is_emergency')->default(false);
            $table->boolean('quota_exceeded')->default(false);
            $table->integer('used_today')->default(0);
            $table->integer('daily_query_limit')->default(10);

            // attachments
            $table->string('image_path')->nullable();
            $table->string('file_path')->nullable();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
    }
};
