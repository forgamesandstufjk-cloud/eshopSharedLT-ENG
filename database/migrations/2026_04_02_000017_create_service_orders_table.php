<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('listing_id')
                ->constrained('listing')
                ->cascadeOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('buyer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('converted_order_id')
                ->nullable()
                ->constrained('order')
                ->nullOnDelete();

            $table->string('status', 40)->default('agreed');
            $table->string('completion_method', 20)->nullable();
            $table->string('payment_status', 30)->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->unsignedBigInteger('amount_charged_cents')->nullable();
            $table->string('reimbursement_transfer_id')->nullable();

            $table->boolean('is_anonymous')->default(false);
            $table->string('buyer_code_snapshot', 20)->nullable();

            $table->string('original_listing_title');
            $table->decimal('original_listing_price', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);

            $table->json('agreed_details')->nullable();
            $table->text('notes')->nullable();
            $table->text('shipping_notes')->nullable();
            $table->text('custom_requirements')->nullable();
            $table->text('timeline_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->string('carrier', 20)->nullable();
            $table->string('package_size', 2)->nullable();
            $table->integer('shipping_cents')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('shipment_status', 30)->nullable();
            $table->timestamp('shipment_submitted_at')->nullable();
            $table->timestamp('shipment_approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_to_ship_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_status_change_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamp('removed_from_board_at')->nullable();

            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index(['buyer_id', 'status']);
            $table->index(['shipment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
