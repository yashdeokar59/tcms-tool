<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_cases', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('set null');
            $table->json('test_data')->nullable();
            $table->text('postconditions')->nullable();
            $table->enum('automation_status', ['manual', 'automated', 'to_be_automated'])->default('manual');
            $table->integer('estimated_time')->nullable(); // in minutes
            $table->enum('complexity', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('is_template')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('test_cases')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('test_cases', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'module_id', 'test_data', 'postconditions', 'automation_status', 
                'estimated_time', 'complexity', 'is_template', 'parent_id'
            ]);
        });
    }
};
