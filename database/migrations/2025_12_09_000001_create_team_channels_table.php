<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_channels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('tag')->nullable();
            $table->text('description')->nullable();
            $table->string('color')->default('primary');
            $table->string('status')->default('active');
            $table->unsignedInteger('members_count')->default(0);
            $table->unsignedInteger('threads_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_channel_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('team_channels')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['channel_id', 'user_id']);
        });

        Schema::create('team_channel_threads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('channel_id')->constrained('team_channels')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamp('last_reply_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_channel_replies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('thread_id')->constrained('team_channel_threads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('team_channel_replies')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_channel_replies');
        Schema::dropIfExists('team_channel_threads');
        Schema::dropIfExists('team_channel_members');
        Schema::dropIfExists('team_channels');
    }
};
