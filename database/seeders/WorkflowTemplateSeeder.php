<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowStatus;
use App\Modules\Auth\Models\Company;
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
            'type' => 'classic',
            'is_default' => true,
            'statuses' => [
                ['name' => 'Open', 'color' => 'blue', 'is_active' => true, 'type' => 'open', 'responsibility' => 'creator'],
                ['name' => 'In Progress', 'color' => 'amber', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Ready for Review', 'color' => 'purple', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Closed', 'color' => 'slate', 'is_active' => false, 'type' => 'closed', 'responsibility' => 'assignee'],
            ],
        ],
        'bug_tracking' => [
            'name' => 'Bug Tracking',
            'description' => 'Comprehensive workflow for tracking bugs and issues through their lifecycle.',
            'type' => 'classic',
            'is_default' => true,
            'statuses' => [
                ['name' => 'Open', 'color' => 'blue', 'is_active' => true, 'type' => 'open', 'responsibility' => 'creator'],
                ['name' => 'In Progress', 'color' => 'amber', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Missing Information', 'color' => 'orange', 'is_active' => true, 'type' => 'active', 'responsibility' => 'creator'],
                ['name' => 'Not Reproducible', 'color' => 'gray', 'is_active' => true, 'type' => 'active', 'responsibility' => 'creator'],
                ['name' => 'Not a Bug', 'color' => 'slate', 'is_active' => true, 'type' => 'active', 'responsibility' => 'creator'],
                ['name' => 'Duplicate Bug', 'color' => 'rose', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'On Hold', 'color' => 'yellow', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Pushed Back', 'color' => 'pink', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Fixed', 'color' => 'green', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Ready for Retest', 'color' => 'cyan', 'is_active' => true, 'type' => 'active', 'responsibility' => 'creator'],
                ['name' => 'Fix Not Confirmed', 'color' => 'red', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Ready for Next Release', 'color' => 'emerald', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Closed', 'color' => 'indigo', 'is_active' => false, 'type' => 'closed', 'responsibility' => 'assignee'],
            ],
        ],
        'inbox_support' => [
            'name' => 'Support Ticket Workflow',
            'description' => 'Workflow for managing support tickets and customer inquiries.',
            'type' => 'inbox',
            'is_default' => true,
            'statuses' => [
                ['name' => 'Unassigned', 'color' => 'slate', 'is_active' => true, 'is_default' => true, 'type' => 'open', 'responsibility' => 'creator'],
                ['name' => 'Open', 'color' => 'blue', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'In Progress', 'color' => 'amber', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Waiting on Customer', 'color' => 'orange', 'is_active' => true, 'type' => 'active', 'responsibility' => 'creator'],
                ['name' => 'On Hold', 'color' => 'yellow', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Resolved', 'color' => 'green', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Closed', 'color' => 'indigo', 'is_active' => false, 'type' => 'closed', 'responsibility' => 'assignee'],
            ],
        ],
        'product_development' => [
            'name' => 'Product Development',
            'description' => 'Agile workflow for product teams with backlog and sprint management.',
            'type' => 'product',
            'is_default' => true,
            'statuses' => [
                ['name' => 'Backlog', 'color' => 'slate', 'is_active' => true, 'is_default' => true, 'type' => 'open', 'responsibility' => 'creator'],
                ['name' => 'Ready for Development', 'color' => 'blue', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'In Development', 'color' => 'amber', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Code Review', 'color' => 'purple', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'QA Testing', 'color' => 'cyan', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Ready for Release', 'color' => 'emerald', 'is_active' => true, 'type' => 'active', 'responsibility' => 'assignee'],
                ['name' => 'Released', 'color' => 'green', 'is_active' => false, 'type' => 'closed', 'responsibility' => 'assignee'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create built-in workflows for all existing companies
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->createWorkflowsForCompany($company);
        }
    }

    /**
     * Create built-in workflows for a specific company.
     */
    public function createWorkflowsForCompany(Company $company, ?int $createdBy = null): void
    {
        foreach (self::TEMPLATES as $key => $template) {
            // Check if workflow already exists for this company
            $exists = Workflow::where('company_id', $company->id)
                ->where('name', $template['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            // Create the workflow (not tied to a specific workspace)
            $workflow = Workflow::create([
                'company_id' => $company->id,
                'workspace_id' => null, // Built-in workflows are company-wide
                'name' => $template['name'],
                'description' => $template['description'],
                'type' => $template['type'] ?? 'classic',
                'is_default' => $template['is_default'],
                'is_archived' => false,
                'created_by' => $createdBy,
            ]);

            // Create statuses
            foreach ($template['statuses'] as $index => $statusData) {
                WorkflowStatus::create([
                    'workflow_id' => $workflow->id,
                    'workspace_id' => null,
                    'name' => $statusData['name'],
                    'color' => $statusData['color'],
                    'is_active' => $statusData['is_active'],
                    'is_default' => $statusData['is_default'] ?? ($index === 0),
                    'type' => $statusData['type'],
                    'responsibility' => $statusData['responsibility'] ?? 'assignee',
                    'sort_order' => $index,
                    'created_by' => $createdBy,
                ]);
            }
        }
    }

    /**
     * Static method to create built-in workflows for a company.
     * Use this when creating a new company (during registration).
     */
    public static function createForCompany(Company $company, ?int $createdBy = null): void
    {
        $seeder = new self();
        $seeder->createWorkflowsForCompany($company, $createdBy);
    }
}
