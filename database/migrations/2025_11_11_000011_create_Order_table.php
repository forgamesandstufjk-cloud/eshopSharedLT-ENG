<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('pirkimo_data')->useCurrent();
            $table->decimal('bendra_suma', 10, 2)->default(0);

            $table->integer('amount_charged_cents')->nullable();
            $table->integer('platform_fee_cents')->nullable();
            $table->integer('small_order_fee_cents')->nullable();
            $table->integer('shipping_total_cents')->default(0);

            $table->string('statusas', 20)->default('paid');

            $table->string('payment_provider')->nullable()->index();
            $table->string('payment_reference')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->json('payment_intents')->nullable();
            $table->json('shipping_address')->nullable();

            $table->foreignId('address_id')
                ->nullable()
                ->constrained('address')
                ->nullOnDelete();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};