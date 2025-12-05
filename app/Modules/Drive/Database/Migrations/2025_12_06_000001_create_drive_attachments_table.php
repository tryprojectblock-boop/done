<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drive_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('name'); // User-provided file name
            $table->text('description')->nullable();
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'created_at']);
            $table->index(['uploaded_by']);
        });

        // Tags for drive attachments
        Schema::create('drive_attachment_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#3b82f6');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        // Pivot table for attachment tags
        Schema::create('drive_attachment_tag', function (Blueprint $table) {
            $table->foreignId('drive_attachment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drive_attachment_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['drive_attachment_id', 'drive_attachment_tag_id']);
        });

        // Shared access for team members
        Schema::create('drive_attachment_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_attachment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['drive_attachment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_attachment_shares');
        Schema::dropIfExists('drive_attachment_tag');
        Schema::dropIfExists('drive_attachment_tags');
        Schema::dropIfExists('drive_attachments');
    }
};
