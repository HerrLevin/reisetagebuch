<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HashTag;
use App\Repositories\HashTagRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HashTagController extends Controller
{
    private HashTagRepository $hashTagRepository;

    public function __construct(HashTagRepository $hashTagRepository)
    {
        $this->hashTagRepository = $hashTagRepository;
    }

    public function index(Request $request): Collection
    {
        return $this->hashTagRepository->getHashTagsForUser($request->user());
    }

    public function store(Request $request): HashTag
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'relevance' => 'sometimes|integer|min:0',
        ]);

        return $this->hashTagRepository->findOrCreateHashTag(
            $request->user(),
            $validated['value'],
            $validated['relevance'] ?? 0
        );
    }

    /**
     * @throws AuthorizationException
     */
    public function update(string $id, Request $request): HashTag
    {
        $validated = $request->validate([
            'relevance' => 'required|integer|min:0',
        ]);

        $hashTag = $this->hashTagRepository->getHashTagById($id);

        if (! $hashTag) {
            abort(404);
        }

        if ($hashTag->user_id !== $request->user()->id) {
            throw new AuthorizationException;
        }

        return $this->hashTagRepository->updateHashTagRelevance($hashTag, $validated['relevance']);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(string $id, Request $request): void
    {
        $hashTag = $this->hashTagRepository->getHashTagById($id);

        if (! $hashTag) {
            abort(404);
        }

        if ($hashTag->user_id !== $request->user()->id) {
            throw new AuthorizationException;
        }

        $this->hashTagRepository->deleteHashTag($hashTag);
    }
}
