<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restore_test_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_log_id')->constrained()->cascadeOnDelete();
            $table->dateTime('test_date');
            $table->foreignId('tested_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['success', 'partial', 'failed'])->default('success');
            $table->text('test_notes');
            $table->text('issues_found')->nullable();
            $table->string('evidence_file_path', 500)->nullable();
            $table->timestamps();
            
            $table->index('backup_log_id');
            $table->index('test_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restore_test_logs');
    }
};
