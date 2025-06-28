<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_runs', function (Blueprint $table) {
            $table->foreignId('cycle_id')->nullable()->constrained('test_cycles')->onDelete('set null');
            $table->foreignId('environment_id')->nullable()->constrained('test_environments')->onDelete('set null');
            $table->string('build_version')->nullable();
            $table->json('configuration')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_runs', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
            $table->dropForeign(['environment_id']);
            $table->dropColumn(['cycle_id', 'environment_id', 'build_version', 'configuration']);
        });
    }
};
