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
        Schema::table('workspace_ticket_forms', function (Blueprint $table) {
            // Spam protection settings (confirmation fields already exist)
            $table->boolean('enable_rate_limiting')->default(true)->after('enable_honeypot');
            $table->integer('rate_limit_per_hour')->default(10)->after('enable_rate_limiting');
            $table->boolean('block_disposable_emails')->default(false)->after('rate_limit_per_hour');
            $table->text('blocked_emails')->nullable()->after('block_disposable_emails');
            $table->text('blocked_domains')->nullable()->after('blocked_emails');
            $table->text('blocked_ips')->nullable()->after('blocked_domains');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_ticket_forms', function (Blueprint $table) {
            $table->dropColumn([
                'enable_rate_limiting',
                'rate_limit_per_hour',
                'block_disposable_emails',
                'blocked_emails',
                'blocked_domains',
                'blocked_ips',
            ]);
        });
    }
};
