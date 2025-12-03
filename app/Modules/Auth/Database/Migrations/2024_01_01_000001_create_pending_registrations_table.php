<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('email')->index();
            $table->string('activation_code', 6);
            $table->timestamp('activation_code_expires_at');
            $table->timestamp('email_verified_at')->nullable();

            // Step 2: Profile
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('password')->nullable();

            // Step 3: Company
            $table->string('company_name')->nullable();
            $table->string('company_size')->nullable();
            $table->string('website_protocol', 10)->default('https');
            $table->string('website_url')->nullable();
            $table->string('industry_type')->nullable();

            // Step 4: Invitations
            $table->json('invited_emails')->nullable();

            // Tracking
            $table->string('registration_step')->default('email_submitted');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'activation_code']);
            $table->index('registration_step');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
