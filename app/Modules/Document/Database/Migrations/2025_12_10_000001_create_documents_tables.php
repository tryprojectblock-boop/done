<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main documents table
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_edited_at')->nullable();
            $table->unsignedInteger('version_count')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'created_at']);
            $table->index(['workspace_id']);
            $table->index(['created_by']);
            $table->index(['last_edited_at']);
        });

        // Document collaborators pivot table
        Schema::create('document_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['editor', 'reader'])->default('reader');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        // Document comments (margin comments on text selections)
        Schema::create('document_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Text selection tracking (character offsets in the document)
            $table->unsignedInteger('selection_start')->nullable();
            $table->unsignedInteger('selection_end')->nullable();
            $table->text('selection_text')->nullable();
            $table->string('selection_id', 50)->nullable()->index();

            $table->text('content');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id', 'is_resolved']);
            $table->index(['document_id', 'selection_start']);
        });

        // Comment replies
        Schema::create('document_comment_replies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('comment_id')->constrained('document_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['comment_id', 'created_at']);
        });

        // Document versions for history
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('content');
            $table->unsignedInteger('version_number');
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'version_number']);
            $table->index(['document_id', 'created_at']);
            $table->unique(['document_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('document_comment_replies');
        Schema::dropIfExists('document_comments');
        Schema::dropIfExists('document_collaborators');
        Schema::dropIfExists('documents');
    }
};
