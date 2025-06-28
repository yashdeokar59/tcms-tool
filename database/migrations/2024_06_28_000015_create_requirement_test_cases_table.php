<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_case_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['requirement_id', 'test_case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_test_cases');
    }
};
