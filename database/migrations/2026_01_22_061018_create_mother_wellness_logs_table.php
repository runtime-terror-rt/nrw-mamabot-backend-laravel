<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMotherWellnessLogsTable extends Migration
{
    public function up()
    {
        Schema::create('mother_wellness_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('log_date');

            $table->enum('mood', ['good', 'neutral', 'low'])->nullable();
            $table->enum('energy_level', ['low', 'medium', 'good'])->nullable();

            $table->boolean('provider_override')->default(false);
            $table->text('override_reason')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'log_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mother_wellness_logs');
    }
}
