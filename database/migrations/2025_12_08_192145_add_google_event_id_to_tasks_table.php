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
            $table->string('google_event_id')->nullable()->after('closed_at');
            $table->timestamp('google_synced_at')->nullable()->after('google_event_id');
            $table->string('google_sync_source')->nullable()->after('google_synced_at'); // 'project_block' or 'google_calendar'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'google_event_id',
                'google_synced_at',
                'google_sync_source',
            ]);
        });
    }
};
