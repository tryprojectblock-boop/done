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
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique identifier for the task
            $table->string('display_name'); // Human-readable name
            $table->text('description')->nullable();
            $table->string('command'); // Artisan command to run
            $table->string('frequency')->default('daily'); // daily, weekly, monthly, hourly
            $table->string('time')->default('02:00'); // Time to run (for daily/weekly)
            $table->unsignedInteger('day_of_week')->nullable(); // 0-6 for weekly (0 = Sunday)
            $table->unsignedInteger('day_of_month')->nullable(); // 1-31 for monthly
            $table->json('options')->nullable(); // Command options (e.g., --days=30)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable(); // success, failed
            $table->text('last_run_output')->nullable();
            $table->unsignedInteger('last_run_duration')->nullable(); // in seconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
