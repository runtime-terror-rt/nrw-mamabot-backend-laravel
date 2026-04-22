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
        Schema::create('community_groups', function (Blueprint $table) {
            $table->id();
            $table->text('name'); 
            $table->string('slug')->unique(); 
            $table->text('description')->nullable(); 
            $table->enum('stage', ['pregnancy', 'postpartum'])->nullable();
            $table->integer('member_count')->default(0); 
            $table->boolean('is_active')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_groups');
    }
};
