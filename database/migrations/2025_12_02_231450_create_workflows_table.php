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
        // Create workflows table
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['workspace_id', 'name']);
            $table->index(['workspace_id', 'is_archived']);
        });

        // Update workflow_statuses to reference workflows instead of workspaces directly
        Schema::table('workflow_statuses', function (Blueprint $table) {
            $table->foreignId('workflow_id')->nullable()->after('workspace_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_statuses', function (Blueprint $table) {
            $table->dropForeign(['workflow_id']);
            $table->dropColumn('workflow_id');
        });

        Schema::dropIfExists('workflows');
    }
};
