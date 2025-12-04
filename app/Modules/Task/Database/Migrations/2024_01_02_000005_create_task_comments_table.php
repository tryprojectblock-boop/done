<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Comment content (supports markdown/rich text)
            $table->text('content');

            // For reply threads (optional)
            $table->foreignId('parent_id')->nullable()->constrained('task_comments')->cascadeOnDelete();

            // Edit tracking
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_edited')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
        });

        // Comment attachments (files attached to comments)
        Schema::create('task_comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('task_comments')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comment_attachments');
        Schema::dropIfExists('task_comments');
    }
};
