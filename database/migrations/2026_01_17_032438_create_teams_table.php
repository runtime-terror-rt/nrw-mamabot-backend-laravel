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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->text('thumbnail_img')->nullable();
            $table->text('name');
            $table->text('title');
            $table->text('bio')->nullable();
            $table->text('fb_link')->nullable();
            $table->text('linkedin_link')->nullable();
            $table->text('twitter_link')->nullable();
            $table->longText('long_description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
