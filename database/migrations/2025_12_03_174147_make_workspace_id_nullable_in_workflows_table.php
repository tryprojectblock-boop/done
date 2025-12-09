<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable()->change();
        });

        Schema::table('workflow_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete rows with NULL workspace_id before making column NOT NULL
        // This is acceptable for rollback as it represents reverting to old state
        DB::table('workflows')->whereNull('workspace_id')->delete();
        DB::table('workflow_statuses')->whereNull('workspace_id')->delete();

        Schema::table('workflows', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable(false)->change();
        });

        Schema::table('workflow_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable(false)->change();
        });
    }
};
