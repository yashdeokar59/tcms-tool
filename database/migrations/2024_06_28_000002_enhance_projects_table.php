<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['start_date', 'end_date', 'manager_id', 'settings', 'is_active']);
        });
    }
};
