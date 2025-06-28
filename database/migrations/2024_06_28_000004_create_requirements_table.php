<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['functional', 'non_functional', 'business', 'technical'])->default('functional');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['draft', 'review', 'approved', 'implemented', 'tested'])->default('draft');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->json('acceptance_criteria')->nullable();
            $table->text('business_value')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
