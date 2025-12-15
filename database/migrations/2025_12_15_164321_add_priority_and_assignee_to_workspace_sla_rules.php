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
        Schema::table('workspace_sla_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('priority_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('assigned_user_id')->nullable()->after('priority_id');

            $table->foreign('priority_id')->references('id')->on('workspace_priorities')->onDelete('set null');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_sla_rules', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn(['priority_id', 'assigned_user_id']);
        });
    }
};
