<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('severity')->default('medium');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_case_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('test_execution_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('defects');
    }
};
