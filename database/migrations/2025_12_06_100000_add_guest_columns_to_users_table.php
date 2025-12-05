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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_guest')) {
                $table->boolean('is_guest')->default(false)->after('status');
            }
            if (!Schema::hasColumn('users', 'guest_company_name')) {
                $table->string('guest_company_name')->nullable()->after('is_guest');
            }
            if (!Schema::hasColumn('users', 'guest_position')) {
                $table->string('guest_position')->nullable()->after('guest_company_name');
            }
            if (!Schema::hasColumn('users', 'guest_phone')) {
                $table->string('guest_phone')->nullable()->after('guest_position');
            }
            if (!Schema::hasColumn('users', 'guest_notes')) {
                $table->text('guest_notes')->nullable()->after('guest_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_guest',
                'guest_company_name',
                'guest_position',
                'guest_phone',
                'guest_notes',
            ]);
        });
    }
};
