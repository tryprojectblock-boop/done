<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Task identification
            $table->string('task_number', 20)->index(); // e.g., PROJ-123
            $table->string('title');
            $table->text('description')->nullable();

            // Task type and priority
            $table->string('type')->default('task');
            $table->string('priority')->default('medium');

            // Status (references workflow_statuses)
            $table->foreignId('status_id')->constrained('workflow_statuses')->restrictOnDelete();

            // Assignments
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Dates
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            // Subtask linking
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->text('parent_link_notes')->nullable();

            // Estimated and actual time (in minutes)
            $table->unsignedInteger('estimated_time')->nullable();
            $table->unsignedInteger('actual_time')->default(0);

            // Position for ordering within workspace
            $table->unsignedInteger('position')->default(0);

            // Soft delete and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['workspace_id', 'status_id']);
            $table->index(['workspace_id', 'assignee_id']);
            $table->index(['workspace_id', 'created_by']);
            $table->index(['workspace_id', 'due_date']);
            $table->index(['workspace_id', 'type']);
            $table->index(['workspace_id', 'priority']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
