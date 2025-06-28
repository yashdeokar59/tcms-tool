<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('preconditions')->nullable();
            $table->json('test_steps');
            $table->text('expected_result');
            $table->string('priority')->default('medium');
            $table->string('type')->default('functional');
            $table->string('status')->default('active');
            $table->foreignId('test_suite_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_cases');
    }
};
