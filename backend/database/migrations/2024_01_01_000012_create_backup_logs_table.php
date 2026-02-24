<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_id')->constrained()->cascadeOnDelete();
            $table->string('backup_type', 50); // Full, Incremental, Differential
            $table->dateTime('scheduled_time');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'success', 'failed'])->default('scheduled');
            $table->string('backup_location', 500)->nullable();
            $table->unsignedBigInteger('backup_size')->nullable(); // in bytes
            $table->text('error_message')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->string('evidence_file_path', 500)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index(['system_id', 'backup_type']);
            $table->index('scheduled_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
