<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_troubles', function (Blueprint $table) {
            $table->id();
            $table->string('trouble_number', 50)->unique(); // GT-YYYYMM-0001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('reported_at');
            $table->unsignedInteger('duration_value');
            $table->enum('duration_unit', ['minute', 'hour']);
            $table->text('problem');
            $table->text('analysis');
            $table->text('solution');
            $table->enum('type', ['hardware', 'software', 'network', 'security', 'others']);
            $table->foreignId('pic_id')->constrained('users')->cascadeOnDelete();
            $table->string('partner')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'on_progress', 'done', 'closed'])->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('trouble_number');
            $table->index('status');
            $table->index('type');
            $table->index('reported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_troubles');
    }
};
