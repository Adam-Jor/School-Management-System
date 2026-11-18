<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('tbl_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('tbl_subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('tbl_teachers')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->nullable()->constrained('tbl_academic_periods')->nullOnDelete();
            $table->enum('day_of_week', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->tinyInteger('archived')->default(0);
            $table->timestamps();

            $table->index(['class_id', 'day_of_week', 'archived']);
            $table->index(['teacher_id', 'day_of_week', 'archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_schedules');
    }
};
