<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_sla_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('workspace_departments')->nullOnDelete();
            $table->string('status')->nullable(); // open, pending, resolved, etc.
            $table->unsignedInteger('resolution_hours')->default(24);
            $table->text('escalation_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['workspace_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_sla_rules');
    }
};
