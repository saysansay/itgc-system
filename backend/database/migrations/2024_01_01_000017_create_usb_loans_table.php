<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usb_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 50)->unique(); // USB-YYYYMM-0001
            $table->foreignId('requestor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->text('purpose'); // Tujuan
            $table->dateTime('loan_datetime'); // Tanggal dan Jam Peminjaman
            $table->dateTime('return_datetime')->nullable(); // Tanggal dan Jam Pengembalian
            $table->dateTime('actual_return_datetime')->nullable(); // Actual return time
            $table->foreignId('pic_id')->constrained('users')->cascadeOnDelete(); // PIC IT/Admin
            $table->text('notes')->nullable(); // Keterangan
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('loan_number');
            $table->index('status');
            $table->index('loan_datetime');
            $table->index(['requestor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usb_loans');
    }
};
