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
        Schema::create('workspace_ticket_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Support Request');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('success_message')->default('Thank you! Your ticket has been submitted successfully.');
            $table->string('submit_button_text')->default('Submit Ticket');
            $table->boolean('is_active')->default(true);

            // Field visibility settings
            $table->boolean('show_name')->default(true);
            $table->boolean('name_required')->default(true);
            $table->boolean('show_email')->default(true);
            $table->boolean('email_required')->default(true);
            $table->boolean('show_phone')->default(false);
            $table->boolean('phone_required')->default(false);
            $table->boolean('show_subject')->default(true);
            $table->boolean('subject_required')->default(true);
            $table->boolean('show_description')->default(true);
            $table->boolean('description_required')->default(true);
            $table->boolean('show_department')->default(false);
            $table->boolean('department_required')->default(false);
            $table->boolean('show_priority')->default(false);
            $table->boolean('priority_required')->default(false);
            $table->boolean('show_attachments')->default(false);

            // Default department/priority if not shown
            $table->foreignId('default_department_id')->nullable()->constrained('workspace_departments')->nullOnDelete();
            $table->foreignId('default_priority_id')->nullable()->constrained('workspace_priorities')->nullOnDelete();

            // Branding
            $table->string('logo_url')->nullable();
            $table->string('primary_color')->default('#3b82f6');
            $table->string('background_color')->default('#f8fafc');

            // Spam protection
            $table->boolean('enable_captcha')->default(false);
            $table->boolean('enable_honeypot')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_ticket_forms');
    }
};
