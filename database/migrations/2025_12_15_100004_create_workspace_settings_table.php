<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table stores simple key-value settings for inbox workspaces
        // Complex entities like departments, priorities have their own tables
        Schema::create('workspace_inbox_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('inbound_email')->nullable();
            $table->string('inbound_email_prefix')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('hour_format', 2)->default('12'); // 12 or 24
            $table->string('date_format', 20)->default('MM/DD/YYYY');
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table->unique('workspace_id');
            $table->unique('inbound_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_inbox_settings');
    }
};
