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
        Schema::create('client_crm', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->enum('type', ['external_consultant', 'client'])->default('client');
            $table->boolean('client_portal_access')->default(false);
            $table->json('tags')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('avatar_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });

        // Pivot table for client-workspace relationship
        Schema::create('client_crm_workspace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_crm_id')->constrained('client_crm')->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['client_crm_id', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_crm_workspace');
        Schema::dropIfExists('client_crm');
    }
};
