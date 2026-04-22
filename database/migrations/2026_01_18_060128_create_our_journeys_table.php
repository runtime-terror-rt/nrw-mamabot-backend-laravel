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
        Schema::create('our_journeys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('count')->default(0);
            $table->string('title');
            $table->text('description');
            $table->text('image_url_1')->nullable();
            $table->text('image_url_2')->nullable();
            $table->string('locale')->default('en');
            $table->string('subtitle_1')->nullable();
            $table->string('subtitle_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('our_journeys');
    }
};
