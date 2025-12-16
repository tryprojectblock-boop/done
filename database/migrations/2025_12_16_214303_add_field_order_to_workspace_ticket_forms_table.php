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
            $table->json('field_order')->nullable()->after('enable_honeypot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_ticket_forms', function (Blueprint $table) {
            $table->dropColumn('field_order');
        });
    }
};
