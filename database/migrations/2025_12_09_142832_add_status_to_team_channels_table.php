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
        Schema::table('team_channels', function (Blueprint $table) {
            // Add status column to replace is_private
            $table->enum('status', ['active', 'inactive', 'archive'])->default('active')->after('color');
            // Drop is_private column
            $table->dropColumn('is_private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_channels', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->boolean('is_private')->default(false)->after('color');
        });
    }
};
