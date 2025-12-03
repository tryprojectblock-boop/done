<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowStatus;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Seeder;

class WorkflowTemplateSeeder extends Seeder
{
    /**
     * Built-in workflow templates.
     */
    public const TEMPLATES = [
        'basic_task_tracking' => [
            'name' => 'Basic Task Tracking',
            'description' => 'Simple workflow for general task management with basic statuses.',
            'is_default' => true,
            'statuses' => [
                ['name' => 'Open', 'color' => 'blue', 'is_active' => true, 'type' => 'open'],
                ['name' => 'In Progress', 'color' => 'amber', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Ready for Review', 'color' => 'purple', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Closed', 'color' => 'slate', 'is_active' => false, 'type' => 'closed'],
            ],
        ],
        'bug_tracking' => [
            'name' => 'Bug Tracking',
            'description' => 'Comprehensive workflow for tracking bugs and issues through their lifecycle.',
            'is_default' => false,
            'statuses' => [
                ['name' => 'Open', 'color' => 'blue', 'is_active' => true, 'type' => 'open'],
                ['name' => 'In Progress', 'color' => 'amber', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Missing Information', 'color' => 'orange', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Not Reproducible', 'color' => 'gray', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Not a Bug', 'color' => 'slate', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Duplicate Bug', 'color' => 'rose', 'is_active' => true, 'type' => 'active'],
                ['name' => 'On Hold', 'color' => 'yellow', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Pushed Back', 'color' => 'pink', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Fixed', 'color' => 'green', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Ready for Retest', 'color' => 'cyan', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Fix Not Confirmed', 'color' => 'red', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Ready for Next Release', 'color' => 'emerald', 'is_active' => true, 'type' => 'active'],
                ['name' => 'Closed', 'color' => 'indigo', 'is_active' => false, 'type' => 'closed'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder creates workflow templates for all existing workspaces
        // For new workspaces, use the static method createForWorkspace()

        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            $this->createWorkflowsForWorkspace($workspace);
        }
    }

    /**
     * Create built-in workflows for a specific workspace.
     */
    public function createWorkflowsForWorkspace(Workspace $workspace, ?int $createdBy = null): void
    {
        foreach (self::TEMPLATES as $key => $template) {
            // Check if workflow already exists
            $exists = Workflow::where('workspace_id', $workspace->id)
                ->where('name', $template['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            // Create the workflow
            $workflow = Workflow::create([
                'workspace_id' => $workspace->id,
                'name' => $template['name'],
                'description' => $template['description'],
                'is_default' => $template['is_default'],
                'is_archived' => false,
                'created_by' => $createdBy,
            ]);

            // Create statuses
            foreach ($template['statuses'] as $index => $statusData) {
                WorkflowStatus::create([
                    'workflow_id' => $workflow->id,
                    'workspace_id' => $workspace->id,
                    'name' => $statusData['name'],
                    'color' => $statusData['color'],
                    'is_active' => $statusData['is_active'],
                    'type' => $statusData['type'],
                    'sort_order' => $index,
                    'created_by' => $createdBy,
                ]);
            }
        }
    }

    /**
     * Static method to create built-in workflows for a workspace.
     * Use this when creating a new workspace.
     */
    public static function createForWorkspace(Workspace $workspace, ?int $createdBy = null): void
    {
        $seeder = new self();
        $seeder->createWorkflowsForWorkspace($workspace, $createdBy);
    }
}
