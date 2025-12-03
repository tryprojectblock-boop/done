<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->foreignId('company_id')->nullable()->after('last_name')->constrained()->nullOnDelete();
            $table->string('avatar_path')->nullable()->after('company_id');
            $table->string('timezone')->default('UTC')->after('avatar_path');
            $table->json('settings')->nullable()->after('timezone');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'uuid',
                'first_name',
                'last_name',
                'company_id',
                'avatar_path',
                'timezone',
                'settings',
                'last_login_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
