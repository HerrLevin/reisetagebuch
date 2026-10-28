<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\ClientRepository;
use Throwable;

/**
 * OAuth 2.0 Dynamic Client Registration (RFC 7591), so apps can register with
 * any Reisetagebuch server. Only public clients using the authorization code
 * flow (with PKCE) are issued; registration is open, so every client gets
 * a consent screen.
 */
class ClientRegistrationController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(Request $request, ClientRepository $clients): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'client_uri' => 'required|url:https|max:2048',
            'policy_uri' => 'required|url:https|max:2048',
            'tos_uri' => 'required|url:https|max:2048',
            'logo_uri' => 'nullable|url:https|max:2048',
            'contacts' => 'nullable|array|max:5',
            'contacts.*' => 'required|email|max:255',
            'redirect_uris' => 'required|array|min:1|max:5',
            'redirect_uris.*' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail) {
                if (! is_string($value) || ! self::isAllowedRedirectUri($value)) {
                    $fail("$value is not an allowed redirect URI.");
                }
            }],
            'grant_types' => 'sometimes|array',
            'grant_types.*' => 'in:authorization_code,refresh_token',
            'response_types' => 'sometimes|array',
            'response_types.*' => 'in:code',
            'token_endpoint_auth_method' => 'sometimes|in:none',
        ]);

        if ($validator->fails()) {
            $redirectError = collect($validator->errors()->keys())->contains(fn ($key) => str_starts_with($key, 'redirect_uris'));

            return response()->json([
                'error' => $redirectError ? 'invalid_redirect_uri' : 'invalid_client_metadata',
                'error_description' => $validator->errors()->first(),
            ], 400);
        }

        $metadata = $request->only('client_uri', 'policy_uri', 'tos_uri', 'logo_uri', 'contacts');

        $client = DB::transaction(function () use ($clients, $request, $metadata) {
            $client = $clients->createAuthorizationCodeGrantClient(
                $request->input('client_name'),
                $request->input('redirect_uris'),
                confidential: false,
            );
            $client->forceFill([...$metadata, 'contacts' => isset($metadata['contacts']) ? json_encode($metadata['contacts']) : null])->save();

            return $client;
        });

        return response()->json([
            'client_id' => $client->getKey(),
            'client_id_issued_at' => $client->created_at->getTimestamp(),
            'client_name' => $client->name,
            ...$metadata,
            'redirect_uris' => $client->redirect_uris,
            'grant_types' => $client->grant_types,
            'response_types' => ['code'],
            'token_endpoint_auth_method' => 'none',
        ], 201);
    }

    /**
     * RFC 8252: https, loopback http, or a private-use scheme in reverse-domain form.
     */
    private static function isAllowedRedirectUri(string $uri): bool
    {
        $parts = parse_url($uri);
        if ($parts === false || ! isset($parts['scheme']) || isset($parts['fragment'])) {
            return false;
        }

        return match (strtolower($parts['scheme'])) {
            'https' => isset($parts['host']),
            'http' => in_array($parts['host'] ?? '', ['127.0.0.1', '[::1]'], true),
            default => str_contains($parts['scheme'], '.'),
        };
    }
}
