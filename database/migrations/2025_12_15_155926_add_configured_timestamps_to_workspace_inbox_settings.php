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
        Schema::table('workspace_inbox_settings', function (Blueprint $table) {
            $table->timestamp('working_hours_configured_at')->nullable()->after('timezone');
            $table->timestamp('departments_configured_at')->nullable()->after('working_hours_configured_at');
            $table->timestamp('priorities_configured_at')->nullable()->after('departments_configured_at');
            $table->timestamp('holidays_configured_at')->nullable()->after('priorities_configured_at');
            $table->timestamp('sla_configured_at')->nullable()->after('holidays_configured_at');
            $table->timestamp('ticket_rules_configured_at')->nullable()->after('sla_configured_at');
            $table->timestamp('sla_rules_configured_at')->nullable()->after('ticket_rules_configured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_inbox_settings', function (Blueprint $table) {
            $table->dropColumn([
                'working_hours_configured_at',
                'departments_configured_at',
                'priorities_configured_at',
                'holidays_configured_at',
                'sla_configured_at',
                'ticket_rules_configured_at',
                'sla_rules_configured_at',
            ]);
        });
    }
};
