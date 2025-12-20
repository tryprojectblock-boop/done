<?php

declare(strict_types=1);

namespace App\Modules\Admin\Database\Seeders;

use App\Modules\Admin\Models\FunnelTag;
use Illuminate\Database\Seeder;

class FunnelTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            [
                'name' => 'pb_signed_up',
                'display_name' => 'Signed Up',
                'description' => 'User created an account',
                'is_system' => true,
            ],
            [
                'name' => 'pb_workspace_created',
                'display_name' => 'Workspace Created',
                'description' => 'User created their first workspace',
                'is_system' => true,
            ],
            [
                'name' => 'pb_first_task_created',
                'display_name' => 'First Task Created',
                'description' => 'User created their first task',
                'is_system' => true,
            ],
            [
                'name' => 'pb_team_invited',
                'display_name' => 'Team Invited',
                'description' => 'User invited team members to their workspace',
                'is_system' => true,
            ],
            [
                'name' => 'pb_active_user',
                'display_name' => 'Active User',
                'description' => 'User with recent activity in the platform',
                'is_system' => true,
            ],
        ];

        foreach ($tags as $tag) {
            FunnelTag::updateOrCreate(
                ['name' => $tag['name']],
                $tag
            );
        }
    }
}
