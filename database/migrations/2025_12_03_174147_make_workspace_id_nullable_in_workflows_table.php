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
        // Note: Reversing could fail if there are null values
        Schema::table('workflows', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable(false)->change();
        });

        Schema::table('workflow_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable(false)->change();
        });
    }
};
