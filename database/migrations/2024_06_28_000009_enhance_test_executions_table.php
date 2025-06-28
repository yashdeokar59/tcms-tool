<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_executions', function (Blueprint $table) {
            $table->dropColumn('attachments'); // We'll use the attachments table instead
            $table->integer('execution_time')->nullable(); // in minutes
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('build_version')->nullable();
            $table->json('environment_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_executions', function (Blueprint $table) {
            $table->json('attachments')->nullable();
            $table->dropColumn(['execution_time', 'browser', 'os', 'build_version', 'environment_data']);
        });
    }
};
