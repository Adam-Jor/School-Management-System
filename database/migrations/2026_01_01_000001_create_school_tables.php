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
        Schema::create('tbl_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->default(36);
            $table->tinyInteger('archived')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('tbl_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('credit_hours')->default(4);
            $table->tinyInteger('archived')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('tbl_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained('tbl_users')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->index()->constrained('tbl_subjects')->nullOnDelete();
            $table->string('teacher_code', 30)->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('img_url')->nullable();
            $table->string('status', 30)->default('Aktif');
            $table->text('notes')->nullable();
            $table->tinyInteger('archived')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('tbl_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained('tbl_users')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->index()->constrained('tbl_classes')->nullOnDelete();
            $table->string('student_code', 30)->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('img_url')->nullable();
            $table->string('status', 30)->default('Aktif');
            $table->tinyInteger('archived')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('tbl_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->index()->constrained('tbl_students')->cascadeOnDelete();
            $table->foreignId('class_id')->index()->constrained('tbl_classes')->cascadeOnDelete();
            $table->date('date')->index();
            $table->string('status', 20)->default('Hadir');
            $table->string('notes')->nullable();
            $table->tinyInteger('archived')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('tbl_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->index()->constrained('tbl_students')->cascadeOnDelete();
            $table->foreignId('subject_id')->index()->constrained('tbl_subjects')->cascadeOnDelete();
            $table->string('semester', 30)->default('Ganjil 2026/2027');
            $table->decimal('score', 5, 2)->default(0);
            $table->string('predicate', 5)->default('A');
            $table->tinyInteger('archived')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_grades');
        Schema::dropIfExists('tbl_attendances');
        Schema::dropIfExists('tbl_students');
        Schema::dropIfExists('tbl_teachers');
        Schema::dropIfExists('tbl_subjects');
        Schema::dropIfExists('tbl_classes');
    }
};
