<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_reports', function (Blueprint $table) { 
            $table->id();

            $table->foreignId('reported_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reporter_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('listing_id')
                ->nullable()
                ->constrained('listing')
                ->nullOnDelete();

            $table->string('reason', 100);
            $table->text('details')->nullable();

            $table->enum('status', [
                'pending',
                'dismissed',
                'resolved',
            ])->default('pending');

            $table->foreignId('reviewed_by_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->unique(
                ['reporter_user_id', 'reported_user_id', 'listing_id', 'reason'],
                'user_reports_unique_report'
            );

            $table->index(['status', 'created_at'], 'user_reports_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_reports');
    }
};
