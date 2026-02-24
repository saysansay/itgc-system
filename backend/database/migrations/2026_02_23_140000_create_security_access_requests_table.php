<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique(); // SAF-YYYYMM-0001
            $table->dateTime('requested_at');
            $table->foreignId('requestor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('username');
            $table->enum('user_id_action', ['new', 'change', 'delete'])->nullable();
            $table->enum('password_action', ['change', 'no_change'])->nullable();
            $table->enum('email_action', ['new', 'change', 'delete'])->nullable();
            $table->enum('internet_access', ['control_manager', 'control_staff'])->nullable();
            $table->enum('file_sharing', ['full_access', 'modify', 'read_only'])->nullable();
            $table->enum('vpn_action', ['new', 'change', 'delete'])->nullable();
            $table->text('reason')->nullable();
            $table->enum('accpac_action', ['new', 'change', 'delete'])->nullable();
            $table->enum('ifs_action', ['new', 'change', 'delete'])->nullable();
            $table->enum('administrator_action', ['new', 'change', 'delete'])->nullable();
            $table->boolean('restore')->default(false);
            $table->enum('fingerprint_action', ['new', 'change', 'delete'])->nullable();
            $table->enum('change_data_action', ['new', 'change', 'delete'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamp('approval_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('request_number');
            $table->index('status');
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_access_requests');
    }
};
