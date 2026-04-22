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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // 🔤 Slug-based routing
            $table->string('slug')->unique(); // e.g. 'privacy-policy', 'terms-of-service'

            // 📝 Page content
            $table->string('title');
            $table->longText('content')->nullable(); // HTML or Markdown

            // 🔍 SEO metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_image')->nullable(); // URL or path

            $table->boolean('is_active')->default(true);
            $table->boolean('is_indexable')->default(true); // for robots/meta tags

            $table->timestamps();
        });
    }

        /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
