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
        // First, convert existing single type values to JSON array format
        DB::statement("UPDATE tasks SET type = JSON_ARRAY(type) WHERE type IS NOT NULL AND type != '' AND JSON_VALID(type) = 0");

        // Change column type to JSON
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('types')->nullable()->after('description');
        });

        // Copy data from type to types
        DB::statement("UPDATE tasks SET types = type WHERE type IS NOT NULL");

        // Drop old type column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Rename types to type
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('types', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back string column
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('types_old')->nullable()->after('description');
        });

        // Extract first type from JSON array
        DB::statement("UPDATE tasks SET types_old = JSON_UNQUOTE(JSON_EXTRACT(type, '$[0]')) WHERE type IS NOT NULL");

        // Drop JSON column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Rename back
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('types_old', 'type');
        });
    }
};
