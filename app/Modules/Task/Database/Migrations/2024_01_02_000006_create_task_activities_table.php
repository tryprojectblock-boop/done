<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Activity type (created, status_changed, assignee_changed, etc.)
            $table->string('type');

            // JSON fields to store old and new values
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();

            // Human-readable description
            $table->string('description')->nullable();

            $table->timestamps();

            $table->index(['task_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activities');
    }
};
