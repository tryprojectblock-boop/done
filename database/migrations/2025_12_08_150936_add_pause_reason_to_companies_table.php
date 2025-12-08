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
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('trial_ends_at');
            $table->string('pause_reason')->nullable()->after('paused_at');
            $table->text('pause_description')->nullable()->after('pause_reason');
            $table->uuid('paused_by')->nullable()->after('pause_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'pause_reason', 'pause_description', 'paused_by']);
        });
    }
};
