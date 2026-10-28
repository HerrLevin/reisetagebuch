<?php

namespace App\Http\Controllers\Api;

use App\Enums\Scopes\ReadScope;
use App\Enums\Scopes\WriteScope;
use App\Http\Controllers\Backend\AuthBackend;
use App\Models\User;
use App\Repositories\InviteRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Token;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    private AuthBackend $backend;

    private InviteRepository $inviteRepository;

    public function __construct(AuthBackend $backend, InviteRepository $inviteRepository)
    {
        parent::__construct();
        $this->backend = $backend;
        $this->inviteRepository = $inviteRepository;
    }

    #[OA\Get(
        path: '/auth/user',
        operationId: 'getAuthenticatedUser',
        description: 'Get the currently authenticated user',
        summary: 'Get authenticated user',
        security: [['passport' => [ReadScope::Accounts->value]]],
        tags: ['Authentication'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: '#/components/schemas/AuthenticatedUserDto'))]
    )]
    public function user(Request $request)
    {
        $user = $this->backend->getAuthenticatedUser($this->auth);

        return response()->json($user);
    }

    #[OA\Post(
        path: '/auth/logout',
        operationId: 'logout',
        description: 'Logout the authenticated user by revoking their access token',
        summary: 'User logout',
        security: [['passport' => []]],
        tags: ['Authentication'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS)]
    )]
    public function logout(Request $request): JsonResponse
    {
        /** @var Token $token */
        $token = $this->auth->user()->token();
        $token->revoke();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    #[OA\Post(
        path: '/auth/forgot-password',
        operationId: 'forgotPassword',
        description: 'Send a password reset link to the user\'s email address',
        summary: 'Forgot password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'mail@example.com'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS),
            new OA\Response(response: 422, description: Controller::OA_DESC_VALIDATION_ERROR),
        ]
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    #[OA\Post(
        path: '/auth/reset-password',
        operationId: 'resetPassword',
        description: 'Reset the user\'s password using the provided token and new password',
        summary: 'Reset password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'RESET_TOKEN_123'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'mail@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'new_secret'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'new_secret'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS),
            new OA\Response(response: 422, description: Controller::OA_DESC_VALIDATION_ERROR),
        ]
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    #[OA\Put(
        path: '/auth/password',
        operationId: 'updatePassword',
        description: 'Update the authenticated user\'s password by providing the current password and a new password',
        summary: 'Update password',
        security: [['passport' => [WriteScope::Security->value]]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'current_secret'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'new_secret'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'new_secret'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS),
            new OA\Response(response: 422, description: Controller::OA_DESC_VALIDATION_ERROR),
        ]
    )]
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Rules\Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['status' => 'Password updated.']);
    }

    #[OA\Post(
        path: '/auth/email/verify/{id}/{hash}',
        operationId: 'verifyEmail',
        description: 'Verify the user\'s email address using the provided verification link parameters',
        summary: 'Verify email',
        security: [['passport' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS),
            new OA\Response(response: 403, description: 'Invalid verification link'),
            new OA\Response(response: 422, description: Controller::OA_DESC_VALIDATION_ERROR),
        ]
    )]
    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['status' => 'Email already verified.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['status' => 'Email verified successfully.']);
    }

    #[OA\Post(
        path: '/auth/email/resend',
        operationId: 'resendVerificationEmail',
        description: 'Resend the email verification link to the authenticated user if their email address is not yet verified',
        summary: 'Resend email verification',
        security: [['passport' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS),
            new OA\Response(response: 403, description: 'Email already verified'),
        ]
    )]
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => 'Email already verified.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'Verification link sent.']);
    }

    #[OA\Get(
        path: '/auth/invite/{code}',
        operationId: 'validateInvite',
        description: 'Validate an invite code and return information about the invite if it is valid',
        summary: 'Validate invite code',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'valid', type: 'boolean', description: 'Indicates whether the invite code is valid'),
                    new OA\Property(property: 'invitedBy', type: 'string', nullable: true, description: 'The name of the user who created the invite, or null if the inviter user no longer exists'),
                ]
            )),
        ]
    )]
    public function validateInvite(string $code): JsonResponse
    {
        $invite = $this->inviteRepository->getAvailableInviteById($code);

        if (! $invite) {
            return response()->json([
                'valid' => false,
            ]);
        }

        return response()->json([
            'valid' => true,
            'invitedBy' => $invite->user->name ?? null,
        ]);
    }
}
