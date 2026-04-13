<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); // primary key
            $table->string('title'); // tiêu đề task
            $table->text('description')->nullable(); // mô tả chi tiết về task có thể null nếu không có mô tả
            $table->string('status')->default('todo'); // trạng thái của task (ví dụ: todo, in_progress, done)
            $table->string('priority')->default('medium'); // mức độ ưu tiên: low, medium, high
            $table->date('due_date')->nullable(); // ngày hết hạn, có thể null nếu không có hạn
            $table->timestamps(); // created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
