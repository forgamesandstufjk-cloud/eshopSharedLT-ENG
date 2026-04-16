<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('review_id')
                ->constrained('review')
                ->cascadeOnDelete();

            $table->foreignId('reported_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reporter_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

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
                ['review_id', 'reporter_user_id', 'reason'],
                'review_reports_unique_report'
            );

            $table->index(['status', 'created_at'], 'review_reports_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reports');
    }
};