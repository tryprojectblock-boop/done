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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL: Modify enum to include 'invited' status
            DB::statement("ALTER TABLE `client_crm` MODIFY COLUMN `status` ENUM('active', 'inactive', 'invited') DEFAULT 'active'");
        } else {
            // SQLite and others: Recreate the column approach
            // For SQLite, we need to work with string type since it doesn't support ENUM
            Schema::table('client_crm', function (Blueprint $table) {
                $table->string('status_new')->default('active')->after('status');
            });

            DB::table('client_crm')->update(['status_new' => DB::raw('status')]);

            Schema::table('client_crm', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('client_crm', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Revert to original enum values
            DB::statement("ALTER TABLE `client_crm` MODIFY COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active'");
        }
        // For SQLite, no action needed since we use string type
    }
};
