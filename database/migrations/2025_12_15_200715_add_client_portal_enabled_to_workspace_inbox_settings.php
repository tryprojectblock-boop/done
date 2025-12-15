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
            $table->boolean('client_portal_enabled')->default(false)->after('email_templates_configured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_inbox_settings', function (Blueprint $table) {
            $table->dropColumn('client_portal_enabled');
        });
    }
};
