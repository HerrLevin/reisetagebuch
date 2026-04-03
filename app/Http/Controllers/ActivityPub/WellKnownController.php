<?php

namespace App\Http\Controllers\ActivityPub;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WellKnownController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function nodeInfo(): JsonResponse
    {
        $links = ['links' => [['rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.0', 'href' => url('/nodeinfo/2.0')]]];

        return response()->json($links);
    }

    public function webfinger(Request $request): JsonResponse
    {
        // remove url from resource
        $url = config('app.url');
        // get base url without http(s)://
        $baseUrl = preg_replace('#^https?://#', '', $url);
        $resource = $request->query('resource');

        if (! $resource || ! str_starts_with($resource, 'acct:')) {
            return response()->json(['error' => 'Invalid resource'], 400);
        }

        return $this->acctUrl($resource, $baseUrl);
    }

    private function acctUrl(string $resource, string $baseUrl)
    {
        $username = str_replace('acct:', '', $resource);
        $elements = explode('@', $username);
        $username = $this->getUsername($elements);
        $domain = $this->getDomain($elements);

        if (count($elements) > 3 || ! $username || ! $domain) {
            abort(400, 'Invalid acct format');
        }
        if ($domain !== $baseUrl) {
            abort(400, 'Invalid domain');
        }

        $user = $this->userRepository->getUserByUsername($username);

        $links = [
            [
                'rel' => 'self',
                'type' => 'application/activity+json',
                'href' => route('ap.actor', ['username' => $user->username]),
            ],
            [
                'rel' => 'http://webfinger.net/rel/profile-page',
                'type' => 'text/html',
                'href' => url('/@'.$user->username),
            ],
        ];

        if ($user->avatar) {
            $links[] = [
                'rel' => 'http://webfinger.net/rel/avatar',
                'type' => 'image/jpeg',
                'href' => $user->avatar,
            ];
        }

        return response()->json(data: [
            'subject' => $resource,
            'links' => $links,
        ], options: JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/jrd+json');
    }

    private function getUsername(array $elements): ?string
    {
        if (count($elements) === 3 && empty($elements[0])) {
            return $elements[1];
        }
        if (count($elements) === 2) {
            return $elements[0];
        }

        return null;
    }

    private function getDomain(array $elements): ?string
    {
        if (count($elements) === 3 && empty($elements[0])) {
            return $elements[2];
        }
        if (count($elements) === 2) {
            return $elements[1];
        }

        return null;
    }
}
