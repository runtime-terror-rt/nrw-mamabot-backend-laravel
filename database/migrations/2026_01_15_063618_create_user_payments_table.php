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
        Schema::create('user_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->string('payment_provider'); // e.g. Stripe, PayPal
            $table->string('last_four_digit', 4)->nullable(); // card ending
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2); // e.g. 9999.99
            $table->string('currency', 3); // ISO code: USD, EUR, BDT
            $table->string('stripe_payment_id')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('issued_at')->nullable();
            $table->string('download_url')->nullable(); // PDF or hosted invoice

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payments');
    }
};
