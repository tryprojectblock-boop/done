<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('source')->nullable()->after('description')->index();
            $table->string('source_email')->nullable()->after('source');
        });

        Schema::table('task_comments', function (Blueprint $table) {
            $table->string('source')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['source', 'source_email']);
        });

        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
