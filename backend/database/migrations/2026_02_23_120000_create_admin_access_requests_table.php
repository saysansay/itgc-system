<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique(); // ADM-YYYYMM-0001
            $table->foreignId('requestor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->enum('request_type', ['temporary', 'permanent', 'emergency', 'maintenance']);
            $table->unsignedInteger('duration_value');
            $table->enum('duration_unit', ['hour', 'day']);
            $table->enum('method', ['vpn', 'rdp', 'local', 'server_console', 'others']);
            $table->string('hostname');
            $table->string('user_administrator');
            $table->text('purpose');
            $table->dateTime('requested_at');
            $table->string('partner')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('request_number');
            $table->index('status');
            $table->index('requested_at');
            $table->index(['requestor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_access_requests');
    }
};
