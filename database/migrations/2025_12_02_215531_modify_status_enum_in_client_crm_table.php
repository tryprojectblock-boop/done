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
        // Modify enum to include 'invited' status
        DB::statement("ALTER TABLE `client_crm` MODIFY COLUMN `status` ENUM('active', 'inactive', 'invited') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE `client_crm` MODIFY COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active'");
    }
};
