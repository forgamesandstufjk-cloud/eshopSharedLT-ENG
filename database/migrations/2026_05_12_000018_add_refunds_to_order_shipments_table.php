<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('reimbursement_transfer_id');
            $table->string('refund_id')->nullable()->after('refunded_at');
            $table->integer('refund_amount_cents')->nullable()->after('refund_id');
            $table->string('refund_reason')->nullable()->after('refund_amount_cents');
        });
    }

    public function down(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->dropColumn([
                'refunded_at',
                'refund_id',
                'refund_amount_cents',
                'refund_reason',
            ]);
        });
    }
};