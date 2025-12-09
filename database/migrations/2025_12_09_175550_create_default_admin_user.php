<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default admin user if not exists
        $exists = DB::table('admin_users')
            ->where('email', 'rohitcphilip@gmail.com')
            ->exists();

        if (!$exists) {
            DB::table('admin_users')->insert([
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Admin',
                'email' => 'rohitcphilip@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'administrator',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('admin_users')
            ->where('email', 'rohitcphilip@gmail.com')
            ->delete();
    }
};
