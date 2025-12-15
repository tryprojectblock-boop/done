<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_sla_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('priority_id')->constrained('workspace_priorities')->cascadeOnDelete();
            $table->unsignedInteger('first_reply_days')->default(0);
            $table->unsignedInteger('first_reply_hours')->default(1);
            $table->unsignedInteger('first_reply_minutes')->default(0);
            $table->unsignedInteger('next_reply_days')->default(0);
            $table->unsignedInteger('next_reply_hours')->default(4);
            $table->unsignedInteger('next_reply_minutes')->default(0);
            $table->unsignedInteger('resolution_days')->default(1);
            $table->unsignedInteger('resolution_hours')->default(0);
            $table->unsignedInteger('resolution_minutes')->default(0);
            $table->timestamps();

            $table->unique(['workspace_id', 'priority_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_sla_settings');
    }
};
