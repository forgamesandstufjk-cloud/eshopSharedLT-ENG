<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->string('seller_transfer_id')->nullable()->after('reimbursement_transfer_id');
            $table->string('seller_transfer_reversal_id')->nullable()->after('seller_transfer_id');
            $table->integer('seller_transfer_reversed_cents')->nullable()->after('seller_transfer_reversal_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->dropColumn([
                'seller_transfer_id',
                'seller_transfer_reversal_id',
                'seller_transfer_reversed_cents',
            ]);
        });
    }
};
