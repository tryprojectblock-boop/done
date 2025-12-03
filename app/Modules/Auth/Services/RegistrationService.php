<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Contracts\RegistrationServiceInterface;
use App\Modules\Auth\DTOs\CompleteCompanyDTO;
use App\Modules\Auth\DTOs\CompleteProfileDTO;
use App\Modules\Auth\DTOs\RegisterEmailDTO;
use App\Modules\Auth\Enums\RegistrationStep;
use App\Modules\Auth\Events\RegistrationCompleted;
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Auth\Exceptions\RegistrationException;
use App\Modules\Auth\Mail\ActivationCodeMail;
use App\Modules\Auth\Models\Company;
use App\Modules\Auth\Models\PendingRegistration;
use App\Modules\Workspace\DTOs\CreateWorkspaceDTO;
use App\Modules\Workspace\DTOs\InviteMemberDTO;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Enums\WorkspaceType;
use App\Modules\Workspace\Services\WorkspaceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class RegistrationService implements RegistrationServiceInterface
{
    public function __construct(
        private readonly WorkspaceService $workspaceService,
    ) {}

    public function registerEmail(RegisterEmailDTO $dto): PendingRegistration
    {
        $email = strtolower(trim($dto->email));

        // Check if user already exists
        if ($this->isEmailRegistered($email)) {
            throw RegistrationException::emailAlreadyRegistered($email);
        }

        // Check for existing pending registration
        $existing = PendingRegistration::findByEmail($email);

        if ($existing) {
            // Regenerate code if expired, otherwise return existing
            if (! $existing->isActivationCodeValid()) {
                $existing->regenerateActivationCode();
            }
            $this->sendActivationEmail($existing);
            return $existing;
        }

        // Create new pending registration
        $registration = PendingRegistration::create([
            'email' => $email,
            'ip_address' => $dto->ipAddress,
            'user_agent' => $dto->userAgent,
        ]);

        $this->sendActivationEmail($registration);

        return $registration;
    }

    public function verifyActivationCode(string $email, string $code): PendingRegistration
    {
        $registration = PendingRegistration::findByActivationCode($email, $code);

        if (! $registration) {
            throw RegistrationException::invalidActivationCode();
        }

        if (! $registration->isActivationCodeValid()) {
            throw RegistrationException::activationCodeExpired();
        }

        if (! $registration->isEmailVerified()) {
            $registration->verifyEmail();
        }

        return $registration;
    }

    public function resendActivationCode(string $email): PendingRegistration
    {
        $registration = PendingRegistration::findByEmail($email);

        if (! $registration) {
            throw RegistrationException::registrationNotFound();
        }

        $registration->regenerateActivationCode();
        $this->sendActivationEmail($registration);

        return $registration;
    }

    public function completeProfile(string $registrationUuid, CompleteProfileDTO $dto): PendingRegistration
    {
        $registration = $this->getPendingRegistration($registrationUuid);

        if (! $registration) {
            throw RegistrationException::registrationNotFound();
        }

        if (! $registration->isEmailVerified()) {
            throw RegistrationException::emailNotVerified();
        }

        $registration->completeProfileStep([
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'password' => $dto->password,
        ]);

        return $registration;
    }

    public function completeCompany(string $registrationUuid, CompleteCompanyDTO $dto): PendingRegistration
    {
        $registration = $this->getPendingRegistration($registrationUuid);

        if (! $registration) {
            throw RegistrationException::registrationNotFound();
        }

        if ($registration->registration_step->stepNumber() < RegistrationStep::PROFILE_COMPLETED->stepNumber()) {
            throw RegistrationException::invalidStep('Please complete your profile first.');
        }

        $registration->completeCompanyStep([
            'company_name' => $dto->companyName,
            'company_size' => $dto->companySize->value,
            'website_protocol' => $dto->websiteProtocol,
            'website_url' => $dto->websiteUrl,
            'industry_type' => $dto->industryType->value,
        ]);

        return $registration;
    }

    public function completeRegistration(string $registrationUuid, array $invitedEmails = []): User
    {
        $registration = $this->getPendingRegistration($registrationUuid);

        if (! $registration) {
            throw RegistrationException::registrationNotFound();
        }

        if ($registration->registration_step->stepNumber() < RegistrationStep::COMPANY_COMPLETED->stepNumber()) {
            throw RegistrationException::invalidStep('Please complete company information first.');
        }

        return DB::transaction(function () use ($registration, $invitedEmails) {
            // Store invited emails
            if (! empty($invitedEmails)) {
                $registration->completeInvitationsStep($invitedEmails);
            }

            // Create user (as owner of the company)
            $user = User::create([
                'name' => $registration->first_name . ' ' . $registration->last_name,
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'email' => $registration->email,
                'password' => $registration->password, // Already hashed in PendingRegistration
                'email_verified_at' => $registration->email_verified_at,
                'role' => User::ROLE_OWNER,
                'status' => User::STATUS_ACTIVE,
                'last_login_at' => now(),
            ]);

            // Create company
            $company = Company::create([
                'name' => $registration->company_name,
                'size' => $registration->company_size,
                'industry_type' => $registration->industry_type,
                'website_url' => $registration->getFullWebsiteUrl(),
                'owner_id' => $user->id,
            ]);

            // Update user with company
            $user->update(['company_id' => $company->id]);

            // Create default workspace
            $workspace = $this->workspaceService->create(
                CreateWorkspaceDTO::fromArray([
                    'name' => $registration->company_name,
                    'type' => WorkspaceType::CLASSIC->value,
                    'owner_id' => $user->id,
                ])
            );

            // Send invitations
            $validEmails = array_filter($invitedEmails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
            foreach ($validEmails as $email) {
                if (strtolower($email) !== strtolower($registration->email)) {
                    try {
                        $this->workspaceService->invite(
                            InviteMemberDTO::fromArray([
                                'workspace_id' => $workspace->id,
                                'email' => $email,
                                'role' => WorkspaceRole::MEMBER->value,
                                'invited_by' => $user->id,
                            ])
                        );
                    } catch (\Throwable $e) {
                        // Log but don't fail registration
                        report($e);
                    }
                }
            }

            // Mark registration as completed
            $registration->markAsCompleted();

            // Fire events
            event(new UserRegistered($user));
            event(new RegistrationCompleted($user, $company, $workspace));

            return $user;
        });
    }

    public function getPendingRegistration(string $uuid): ?PendingRegistration
    {
        return PendingRegistration::where('uuid', $uuid)
            ->pending()
            ->first();
    }

    public function isEmailRegistered(string $email): bool
    {
        return User::where('email', strtolower($email))->exists();
    }

    protected function sendActivationEmail(PendingRegistration $registration): void
    {
        Mail::to($registration->email)->send(new ActivationCodeMail($registration));
    }
}
