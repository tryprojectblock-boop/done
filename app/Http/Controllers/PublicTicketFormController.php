<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceTicketForm;
use App\Modules\Workspace\Models\WorkspaceTicketFormField;
use App\Services\InboxEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicTicketFormController extends Controller
{
    /**
     * Display the public ticket form.
     */
    public function show(string $slug): View
    {
        $form = WorkspaceTicketForm::where('slug', $slug)
            ->with(['workspace.departments', 'workspace.priorities', 'workspace.workflow', 'defaultDepartment', 'defaultPriority', 'fields'])
            ->firstOrFail();

        // Check if form is active
        if (!$form->is_active) {
            return view('public.ticket-form-inactive', [
                'form' => $form,
            ]);
        }

        return view('public.ticket-form', [
            'form' => $form,
            'workspace' => $form->workspace,
            'departments' => $form->workspace->departments->where('is_public', true),
            'priorities' => $form->workspace->priorities,
            'customFields' => $form->fields,
        ]);
    }

    /**
     * Handle ticket form submission.
     */
    public function submit(Request $request, string $slug)
    {
        $form = WorkspaceTicketForm::where('slug', $slug)
            ->with(['workspace.workflow', 'defaultDepartment', 'defaultPriority', 'fields'])
            ->firstOrFail();

        // Check if form is active
        if (!$form->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This form is currently not accepting submissions.',
            ], 403);
        }

        // Check honeypot if enabled
        if ($form->enable_honeypot && $request->filled('website_url')) {
            Log::info('Honeypot triggered on ticket form', [
                'form_id' => $form->id,
                'ip' => $request->ip(),
            ]);
            return $this->fakeSuccessResponse($form);
        }

        // Check rate limiting
        if ($form->enable_rate_limiting) {
            $rateLimitKey = 'ticket_form_' . $form->id . '_' . $request->ip();
            $submissions = Cache::get($rateLimitKey, 0);

            if ($submissions >= ($form->rate_limit_per_hour ?? 10)) {
                Log::info('Rate limit exceeded on ticket form', [
                    'form_id' => $form->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Too many submissions. Please try again later.',
                ], 429);
            }
        }

        // Check blocked IPs
        if (!empty($form->blocked_ips)) {
            $blockedIps = array_filter(array_map('trim', explode("\n", $form->blocked_ips)));
            if (in_array($request->ip(), $blockedIps)) {
                Log::info('Blocked IP on ticket form', [
                    'form_id' => $form->id,
                    'ip' => $request->ip(),
                ]);
                return $this->fakeSuccessResponse($form);
            }
        }

        // Build validation rules based on form configuration
        $rules = [];
        $messages = [];

        if ($form->show_name) {
            $rules['name'] = $form->name_required ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'];
            $messages['name.required'] = 'Please enter your name.';
        }

        if ($form->show_email) {
            $rules['email'] = $form->email_required ? ['required', 'email', 'max:255'] : ['nullable', 'email', 'max:255'];
            $messages['email.required'] = 'Please enter your email address.';
            $messages['email.email'] = 'Please enter a valid email address.';
        }

        if ($form->show_phone) {
            $rules['phone'] = $form->phone_required ? ['required', 'string', 'max:50'] : ['nullable', 'string', 'max:50'];
            $messages['phone.required'] = 'Please enter your phone number.';
        }

        if ($form->show_subject) {
            $rules['subject'] = $form->subject_required ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'];
            $messages['subject.required'] = 'Please enter a subject.';
        }

        if ($form->show_description) {
            $rules['description'] = $form->description_required ? ['required', 'string', 'max:10000'] : ['nullable', 'string', 'max:10000'];
            $messages['description.required'] = 'Please enter a description.';
        }

        if ($form->show_department) {
            $rules['department_id'] = $form->department_required
                ? ['required', 'exists:workspace_departments,id']
                : ['nullable', 'exists:workspace_departments,id'];
            $messages['department_id.required'] = 'Please select a department.';
        }

        if ($form->show_priority) {
            $rules['priority_id'] = $form->priority_required
                ? ['required', 'exists:workspace_priorities,id']
                : ['nullable', 'exists:workspace_priorities,id'];
            $messages['priority_id.required'] = 'Please select a priority.';
        }

        // Add validation rules for custom fields
        foreach ($form->fields as $field) {
            $fieldRules = [];
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:255';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240'; // 10MB
                    break;
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:10000';
            }

            $rules['custom_' . $field->id] = $fieldRules;
            if ($field->is_required) {
                $messages['custom_' . $field->id . '.required'] = 'Please fill in ' . $field->label . '.';
            }
        }

        $validated = $request->validate($rules, $messages);

        // Check blocked emails/domains after validation
        if (!empty($validated['email'])) {
            $email = strtolower($validated['email']);
            $emailDomain = substr(strrchr($email, '@'), 1);

            // Check blocked emails
            if (!empty($form->blocked_emails)) {
                $blockedEmails = array_filter(array_map('trim', array_map('strtolower', explode("\n", $form->blocked_emails))));
                if (in_array($email, $blockedEmails)) {
                    Log::info('Blocked email on ticket form', ['form_id' => $form->id, 'email' => $email]);
                    return $this->fakeSuccessResponse($form);
                }
            }

            // Check blocked domains
            if (!empty($form->blocked_domains)) {
                $blockedDomains = array_filter(array_map('trim', array_map('strtolower', explode("\n", $form->blocked_domains))));
                if (in_array($emailDomain, $blockedDomains)) {
                    Log::info('Blocked domain on ticket form', ['form_id' => $form->id, 'domain' => $emailDomain]);
                    return $this->fakeSuccessResponse($form);
                }
            }

            // Check disposable emails
            if ($form->block_disposable_emails && $this->isDisposableEmail($emailDomain)) {
                Log::info('Disposable email on ticket form', ['form_id' => $form->id, 'email' => $email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Please use a permanent email address, not a disposable one.',
                ], 422);
            }
        }

        // Get workspace
        $workspace = $form->workspace;

        // Get the first (open) status from workflow
        $openStatus = null;
        if ($workspace->workflow_id) {
            $openStatus = \App\Models\WorkflowStatus::where('workflow_id', $workspace->workflow_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();
        }

        // Determine department and priority (use form values or defaults)
        $departmentId = isset($validated['department_id']) ? (int) $validated['department_id'] : ($form->default_department_id ? (int) $form->default_department_id : null);
        $priorityId = isset($validated['priority_id']) ? (int) $validated['priority_id'] : ($form->default_priority_id ? (int) $form->default_priority_id : null);

        // Generate a unique client token for tracking
        $clientToken = Str::random(32);

        // Build task title
        $title = $validated['subject'] ?? 'Support Request';
        if (empty($validated['subject']) && !empty($validated['name'])) {
            $title = 'Support Request from ' . $validated['name'];
        }

        // Build description with contact info
        $description = $validated['description'] ?? '';
        $contactInfo = [];
        if (!empty($validated['name'])) {
            $contactInfo[] = '**Name:** ' . $validated['name'];
        }
        if (!empty($validated['email'])) {
            $contactInfo[] = '**Email:** ' . $validated['email'];
        }
        if (!empty($validated['phone'])) {
            $contactInfo[] = '**Phone:** ' . $validated['phone'];
        }
        if (!empty($contactInfo)) {
            $description = implode("\n", $contactInfo) . "\n\n---\n\n" . $description;
        }

        // Process custom fields
        $customFieldsData = [];
        foreach ($form->fields as $field) {
            $fieldKey = 'custom_' . $field->id;
            if (isset($validated[$fieldKey])) {
                $customFieldsData[] = [
                    'field_id' => $field->id,
                    'label' => $field->label,
                    'name' => $field->name,
                    'type' => $field->type,
                    'value' => $validated[$fieldKey],
                ];
            }
        }

        // Create the ticket (task)
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'title' => $title,
            'description' => $description ?: null,
            'status_id' => $openStatus?->id,
            'department_id' => $departmentId,
            'workspace_priority_id' => $priorityId,
            'source' => 'web_form',
            'source_email' => $validated['email'] ?? null,
            'custom_fields' => !empty($customFieldsData) ? $customFieldsData : null,
            'client_token' => $clientToken,
            'created_by' => null, // Public submission - no user
        ]);

        // If email is provided, try to find or create guest user and link to ticket
        if (!empty($validated['email'])) {
            $this->linkGuestToTicket($task, $validated, $workspace);
        }

        // Apply ticket rules if department is set
        if ($departmentId) {
            $this->applyTicketRules($task, $workspace, $departmentId);
        }

        // Send ticket opened email to customer
        if (!empty($validated['email'])) {
            $emailService = app(InboxEmailService::class);
            $emailService->sendTicketOpenedEmail($task);
        }

        // Update rate limit counter
        if ($form->enable_rate_limiting) {
            $rateLimitKey = 'ticket_form_' . $form->id . '_' . $request->ip();
            Cache::put($rateLimitKey, Cache::get($rateLimitKey, 0) + 1, now()->addHour());
        }

        Log::info('Public ticket form submission', [
            'form_id' => $form->id,
            'task_id' => $task->id,
            'task_number' => $task->task_number,
            'email' => $validated['email'] ?? 'none',
        ]);

        // Return response based on confirmation type
        return response()->json([
            'success' => true,
            'message' => $form->success_message,
            'ticket_number' => $task->task_number,
            'confirmation' => [
                'type' => $form->confirmation_type ?? 'inline',
                'headline' => $form->confirmation_headline ?? 'Thank You!',
                'message' => $form->confirmation_message ?? 'Your ticket has been submitted successfully.',
                'redirect_url' => $form->redirect_url,
            ],
        ]);
    }

    /**
     * Link or create guest user for the ticket.
     */
    private function linkGuestToTicket(Task $task, array $validated, Workspace $workspace): void
    {
        $email = strtolower($validated['email']);

        // Find existing user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Create new guest user
            $invitationToken = Str::random(64);

            $user = User::create([
                'email' => $email,
                'name' => $validated['name'] ?? explode('@', $email)[0],
                'first_name' => $validated['name'] ?? explode('@', $email)[0],
                'password' => Hash::make(Str::random(32)),
                'role' => User::ROLE_GUEST,
                'status' => User::STATUS_INVITED,
                'is_guest' => true,
                'invitation_token' => $invitationToken,
                'invitation_expires_at' => now()->addDays(30),
            ]);
        }

        // Add user as guest to workspace if not already
        if (!$workspace->hasGuest($user) && !$workspace->hasMember($user)) {
            $workspace->addGuest($user);
        }

        // Update task with creator
        $task->update(['created_by' => $user->id]);
    }

    /**
     * Apply ticket assignment rules based on department.
     */
    private function applyTicketRules(Task $task, Workspace $workspace, int|null $departmentId): void
    {
        if (!$departmentId) {
            return;
        }

        // Find ticket rule for this department
        $rule = $workspace->ticketRules()
            ->where('department_id', $departmentId)
            ->first();

        if ($rule && $rule->assigned_user_id) {
            $task->update(['assignee_id' => $rule->assigned_user_id]);
        }
    }

    /**
     * Return a fake success response for blocked submissions.
     * This prevents spammers from knowing they've been blocked.
     */
    private function fakeSuccessResponse(WorkspaceTicketForm $form): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $form->success_message,
            'ticket_number' => 'TKT-' . rand(10000, 99999),
            'confirmation' => [
                'type' => $form->confirmation_type ?? 'inline',
                'headline' => $form->confirmation_headline ?? 'Thank You!',
                'message' => $form->confirmation_message ?? 'Your ticket has been submitted successfully.',
                'redirect_url' => $form->redirect_url,
            ],
        ]);
    }

    /**
     * Check if an email domain is a known disposable email provider.
     */
    private function isDisposableEmail(string $domain): bool
    {
        $disposableDomains = [
            'tempmail.com', 'temp-mail.org', 'guerrillamail.com', 'guerrillamail.org',
            'mailinator.com', '10minutemail.com', 'throwaway.email', 'fakeinbox.com',
            'trashmail.com', 'maildrop.cc', 'dispostable.com', 'tempail.com',
            'sharklasers.com', 'guerrillamail.info', 'grr.la', 'spam4.me',
            'discard.email', 'discardmail.com', 'mailnesia.com', 'tmpmail.org',
            'tmpmail.net', 'getnada.com', 'yopmail.com', 'yopmail.fr',
            'emailondeck.com', 'mohmal.com', 'tempinbox.com', 'tempr.email',
            'throwawaymail.com', 'temp.email', 'burnermail.io', 'mailsac.com',
            'mintemail.com', 'mailcatch.com', 'inboxalias.com', 'incognitomail.org',
            'spamgourmet.com', 'mytrashmail.com', 'spambox.us', 'nwytg.net',
        ];

        return in_array(strtolower($domain), $disposableDomains);
    }
}
