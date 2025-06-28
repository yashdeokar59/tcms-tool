<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_runs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_suite_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('status')->default('planned');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_runs');
    }
};
