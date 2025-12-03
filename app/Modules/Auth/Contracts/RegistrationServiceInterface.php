<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Models\User;
use App\Modules\Auth\DTOs\CompleteCompanyDTO;
use App\Modules\Auth\DTOs\CompleteProfileDTO;
use App\Modules\Auth\DTOs\RegisterEmailDTO;
use App\Modules\Auth\Models\PendingRegistration;

interface RegistrationServiceInterface
{
    /**
     * Step 1: Register email and send activation code.
     */
    public function registerEmail(RegisterEmailDTO $dto): PendingRegistration;

    /**
     * Verify activation code.
     */
    public function verifyActivationCode(string $email, string $code): PendingRegistration;

    /**
     * Resend activation code.
     */
    public function resendActivationCode(string $email): PendingRegistration;

    /**
     * Step 2: Complete profile (name, password).
     */
    public function completeProfile(string $registrationUuid, CompleteProfileDTO $dto): PendingRegistration;

    /**
     * Step 3: Complete company information.
     */
    public function completeCompany(string $registrationUuid, CompleteCompanyDTO $dto): PendingRegistration;

    /**
     * Step 4: Add team invitations and complete registration.
     */
    public function completeRegistration(string $registrationUuid, array $invitedEmails = []): User;

    /**
     * Get pending registration by UUID.
     */
    public function getPendingRegistration(string $uuid): ?PendingRegistration;

    /**
     * Check if email is already registered.
     */
    public function isEmailRegistered(string $email): bool;
}
