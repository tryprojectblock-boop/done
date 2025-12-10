<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id', 'sort_order']);
        });

        // Update document_comments to link to pages (optional)
        Schema::table('document_comments', function (Blueprint $table) {
            $table->foreignId('document_page_id')->nullable()->after('document_id')->constrained('document_pages')->cascadeOnDelete();
        });

        // Update document_versions to support page versions
        Schema::table('document_versions', function (Blueprint $table) {
            $table->foreignId('document_page_id')->nullable()->after('document_id')->constrained('document_pages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropForeign(['document_page_id']);
            $table->dropColumn('document_page_id');
        });

        Schema::table('document_comments', function (Blueprint $table) {
            $table->dropForeign(['document_page_id']);
            $table->dropColumn('document_page_id');
        });

        Schema::dropIfExists('document_pages');
    }
};
