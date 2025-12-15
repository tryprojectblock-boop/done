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
        Schema::create('workspace_email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('type', 50); // ticket_created, ticket_assigned, ticket_resolved, ticket_reply, sla_warning, sla_breach
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['workspace_id', 'type', 'is_default']);
            $table->index(['workspace_id', 'type', 'is_active']);
        });

        // Also add the configured_at field to inbox settings
        Schema::table('workspace_inbox_settings', function (Blueprint $table) {
            $table->timestamp('email_templates_configured_at')->nullable()->after('idle_rules_configured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_inbox_settings', function (Blueprint $table) {
            $table->dropColumn('email_templates_configured_at');
        });

        Schema::dropIfExists('workspace_email_templates');
    }
};
