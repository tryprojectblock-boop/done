<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('member'); // Role in this specific company
            $table->boolean('is_primary')->default(false); // Is this the user's primary company?
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            // Each user can only have one role per company
            $table->unique(['company_id', 'user_id']);

            // Index for quick lookups
            $table->index(['user_id', 'is_primary']);
        });

        // Migrate existing users to the pivot table
        // Each user's current company becomes their primary company
        DB::statement("
            INSERT INTO company_user (company_id, user_id, role, is_primary, joined_at, created_at, updated_at)
            SELECT company_id, id, role, 1, created_at, NOW(), NOW()
            FROM users
            WHERE company_id IS NOT NULL AND deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
