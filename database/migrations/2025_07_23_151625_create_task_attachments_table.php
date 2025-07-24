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
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade'); // العلاقة مع المهام
            $table->string('filename');   // اسم الملف على السيرفر
            $table->string('original_name'); // الاسم الأصلي للملف وقت الرفع
            $table->string('mime_type')->nullable(); // نوع الملف
            $table->unsignedBigInteger('size')->nullable(); // حجم الملف بالبايت
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
    }
};
