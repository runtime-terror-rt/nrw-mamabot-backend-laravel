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
            Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->uuid('chat_id')->nullable()->unique();
            $table->boolean('accepted_terms')->default(false);
            $table->boolean('consent_health_data')->default(false);
            $table->boolean('newsletter_opt_in')->default(false);
            $table->boolean('accepted_withdrawal_waiver')->default(false);
            $table->boolean('accepted_auto_renewal')->default(false);


            $table->boolean('is_blocked')->default(false);


            // ✅ First time flag (boolean)
            $table->boolean('is_first_time')
                  ->default(true);

            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_expire_at')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->string('fcm_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
