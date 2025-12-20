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
        Schema::create('standup_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('standup_templates')->cascadeOnDelete();
            $table->date('standup_date');
            $table->json('responses'); // Array of {question_id, question, type, answer}
            $table->string('mood')->nullable(); // great, good, okay, concerned, struggling
            $table->boolean('has_blockers')->default(false);
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id', 'standup_date']);
            $table->index(['workspace_id', 'standup_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standup_entries');
    }
};
