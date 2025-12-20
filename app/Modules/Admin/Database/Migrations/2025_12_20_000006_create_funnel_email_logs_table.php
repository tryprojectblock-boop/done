<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_step_id')->constrained()->onDelete('cascade');
            $table->string('to_email');
            $table->string('subject');
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->integer('click_count')->default(0);
            $table->json('clicked_links')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['funnel_id', 'status']);
            $table->index(['user_id', 'funnel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_email_logs');
    }
};
