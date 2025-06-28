<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_run_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_case_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('not_executed');
            $table->text('actual_result')->nullable();
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable();
            $table->datetime('executed_at')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_executions');
    }
};
