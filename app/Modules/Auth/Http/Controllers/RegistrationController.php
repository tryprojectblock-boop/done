<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Contracts\RegistrationServiceInterface;
use App\Modules\Auth\DTOs\CompleteCompanyDTO;
use App\Modules\Auth\DTOs\CompleteProfileDTO;
use App\Modules\Auth\DTOs\RegisterEmailDTO;
use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
use App\Modules\Auth\Exceptions\RegistrationException;
use App\Modules\Auth\Http\Requests\CompleteCompanyRequest;
use App\Modules\Auth\Http\Requests\CompleteInvitationsRequest;
use App\Modules\Auth\Http\Requests\CompleteProfileRequest;
use App\Modules\Auth\Http\Requests\RegisterEmailRequest;
use App\Modules\Auth\Http\Requests\VerifyActivationCodeRequest;
use App\Modules\Auth\Rules\StrongPasswordRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationServiceInterface $registrationService,
    ) {}

    /**
     * Step 1: Register email and send activation code.
     */
    public function registerEmail(RegisterEmailRequest $request): JsonResponse
    {
        try {
            $registration = $this->registrationService->registerEmail(
                new RegisterEmailDTO(
                    email: $request->validated('email'),
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent(),
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Activation code sent to your email.',
                'data' => [
                    'uuid' => $registration->uuid,
                    'email' => $registration->email,
                ],
            ]);
        } catch (RegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify activation code.
     */
    public function verifyCode(VerifyActivationCodeRequest $request): JsonResponse
    {
        try {
            $registration = $this->registrationService->verifyActivationCode(
                $request->validated('email'),
                $request->validated('code'),
            );

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.',
                'data' => [
                    'uuid' => $registration->uuid,
                    'email' => $registration->email,
                    'step' => $registration->registration_step->value,
                ],
            ]);
        } catch (RegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Resend activation code.
     */
    public function resendCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $registration = $this->registrationService->resendActivationCode(
                $request->input('email')
            );

            return response()->json([
                'success' => true,
                'message' => 'A new activation code has been sent to your email.',
                'data' => [
                    'uuid' => $registration->uuid,
                ],
            ]);
        } catch (RegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 2: Complete profile (name, password).
     */
    public function completeProfile(CompleteProfileRequest $request, string $uuid): JsonResponse
    {
        try {
            $registration = $this->registrationService->completeProfile(
                $uuid,
                new CompleteProfileDTO(
                    firstName: $request->validated('first_name'),
                    lastName: $request->validated('last_name'),
                    password: $request->validated('password'),
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile completed successfully.',
                'data' => [
                    'uuid' => $registration->uuid,
                    'step' => $registration->registration_step->value,
                ],
            ]);
        } catch (RegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 3: Complete company information.
     */
    public function completeCompany(CompleteCompanyRequest $request, string $uuid): JsonResponse
    {
        try {
            $registration = $this->registrationService->completeCompany(
                $uuid,
                CompleteCompanyDTO::fromArray($request->validated())
            );

            return response()->json([
                'success' => true,
                'message' => 'Company information saved successfully.',
                'data' => [
                    'uuid' => $registration->uuid,
                    'step' => $registration->registration_step->value,
                ],
            ]);
        } catch (RegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 4: Complete registration with team invitations.
     */
    public function completeRegistration(CompleteInvitationsRequest $request, string $uuid): JsonResponse
    {
        try {
            $user = $this->registrationService->completeRegistration(
                $uuid,
                $request->getValidEmails()
            );

            // Auto-login the user
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully!',
                'data' => [
                    'user' => [
                        'uuid' => $user->uuid,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'redirect_url' => '/dashboard',
                ],
            ]);
        } catch (RegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred completing your registration. Please try again.',
                'debug' => config('app.debug') ? [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Get registration options (company sizes, industries).
     */
    public function getOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'company_sizes' => CompanySize::options(),
                'industry_types' => IndustryType::searchableOptions(),
                'password_requirements' => StrongPasswordRule::requirements(),
            ],
        ]);
    }

    /**
     * Get registration status.
     */
    public function getStatus(string $uuid): JsonResponse
    {
        $registration = $this->registrationService->getPendingRegistration($uuid);

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $registration->uuid,
                'email' => $registration->email,
                'step' => $registration->registration_step->value,
                'step_number' => $registration->registration_step->stepNumber(),
                'email_verified' => $registration->isEmailVerified(),
                'first_name' => $registration->first_name,
                'company_name' => $registration->company_name,
            ],
        ]);
    }
}
