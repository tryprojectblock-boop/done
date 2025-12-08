<?php

namespace App\Modules\Admin\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Admin\Models\AdminUser;
use App\Modules\Admin\Enums\AdminRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:create
                            {--name= : The name of the admin user}
                            {--email= : The email of the admin user}
                            {--password= : The password for the admin user}
                            {--role=administrator : The role (administrator or member)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new admin user for the backoffice';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('What is the admin name?');
        $email = $this->option('email') ?? $this->ask('What is the admin email?');
        $password = $this->option('password') ?? $this->secret('What is the password?');
        $role = $this->option('role');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:administrator,member',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        // Create the admin user
        $admin = AdminUser::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => AdminRole::from($role),
            'is_active' => true,
        ]);

        $this->info("Admin user created successfully!");
        $this->table(
            ['ID', 'Name', 'Email', 'Role'],
            [[$admin->id, $admin->name, $admin->email, $admin->role->label()]]
        );

        return self::SUCCESS;
    }
}
