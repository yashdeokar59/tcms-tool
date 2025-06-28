<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->dropColumn('attachments'); // We'll use the attachments table instead
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('set null');
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_behavior')->nullable();
            $table->text('actual_behavior')->nullable();
            $table->json('environment')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->json('tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->json('attachments')->nullable();
            $table->dropForeign(['module_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'module_id', 'steps_to_reproduce', 'expected_behavior', 'actual_behavior',
                'environment', 'browser', 'os', 'resolution', 'resolved_at', 
                'verified_by', 'verified_at', 'tags'
            ]);
        });
    }
};
