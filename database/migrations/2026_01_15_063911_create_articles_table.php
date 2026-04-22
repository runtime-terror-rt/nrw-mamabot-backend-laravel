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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            // Basic Information
            $table->string('title');
            $table->string('slug')->unique(); // SEO friendly
            $table->foreignId('category_id')->constrained('article_categories')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');

            // Specific Meta

            $table->enum('phase_type', ['pregnancy','postpartum'])->default('pregnancy'); // e.g., pregnancy, postpartum
            $table->enum('delivery_type', ['vaginal_delivery','cesarean_delivery'])->nullable(); // e.g., vaginal_delivery, cesarean_delivery
            $table->integer('week')->nullable();

            // Descriptions
            $table->text('short_description');
            $table->longText('long_description');

            // Author and Meta
            $table->string('author_name')->nullable();
            $table->string('read_duration')->nullable(); // e.g., 5 min read

            // Media (Images)
            $table->text('thumb_img')->nullable(); // Thumbnail image path/URL
            $table->text('main_img')->nullable();  // Main featured image path/URL

            // Status and Performance
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->boolean('feature_status')->default(false); // Featured article
            $table->string('response_time')->nullable(); // Analytics/Performance field

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
        Schema::dropIfExists('articles');
    }
};
