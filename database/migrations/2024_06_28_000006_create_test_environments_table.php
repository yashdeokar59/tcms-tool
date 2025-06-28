<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_environments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->enum('type', ['development', 'testing', 'staging', 'production'])->default('testing');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->json('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_environments');
    }
};
