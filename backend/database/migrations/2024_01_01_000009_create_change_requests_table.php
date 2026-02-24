<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->string('title', 200);
            $table->text('description');
            $table->foreignId('system_id')->constrained()->cascadeOnDelete();
            $table->enum('change_type', ['enhancement', 'bug_fix', 'configuration', 'emergency'])->default('enhancement');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            $table->text('impact_analysis');
            $table->text('rollback_plan');
            $table->dateTime('planned_start')->nullable();
            $table->dateTime('planned_end')->nullable();
            $table->dateTime('actual_start')->nullable();
            $table->dateTime('actual_end')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'in_progress', 'completed', 'failed'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('implementer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('ticket_number');
            $table->index('status');
            $table->index('risk_level');
            $table->index('change_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
