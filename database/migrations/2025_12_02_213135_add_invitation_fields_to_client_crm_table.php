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
        Schema::table('client_crm', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->string('invitation_token', 64)->nullable()->after('status');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
            $table->timestamp('invited_at')->nullable()->after('invitation_expires_at');
            $table->timestamp('accepted_at')->nullable()->after('invited_at');
            $table->timestamp('last_login_at')->nullable()->after('accepted_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->string('timezone')->default('UTC')->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_crm', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'invitation_token',
                'invitation_expires_at',
                'invited_at',
                'accepted_at',
                'last_login_at',
                'last_login_ip',
                'timezone',
            ]);
        });
    }
};
