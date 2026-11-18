<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // e.g. "2024/2025"
            $table->string('semester', 20); // "Ganjil" / "Genap"
            $table->string('slug')->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('archived')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_academic_periods');
    }
};
