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
        Schema::create('web_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->text('logo')->nullable();
            $table->text('favicon')->nullable();
            $table->text('footer_description')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('insta_link')->nullable();
            $table->string('fb_link')->nullable();
            $table->string('tiktok_link')->nullable();
            $table->string('mail_1')->nullable();
            $table->string('mail_2')->nullable();
            $table->string('working_hour')->nullable();
            $table->text('headquarter_address')->nullable();

            //SEO friendly meta tags
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_image')->nullable();
            $table->longText('google_schema')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_settings');
    }
};
