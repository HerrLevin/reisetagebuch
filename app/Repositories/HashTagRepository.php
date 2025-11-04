<?php

namespace App\Repositories;

use App\Models\HashTag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class HashTagRepository
{
    public function findOrCreateHashTag(User|string $user, string $value): HashTag
    {
        if (is_string($user)) {
            $user = User::findOrFail($user);
        }

        return HashTag::firstOrCreate(
            ['user_id' => $user->id, 'value' => $value],
            ['user_id' => $user->id, 'value' => $value]
        );
    }

    public function syncHashTagsByValue(Post $post, array $tagValues): void
    {
        $hashTags = collect();
        foreach ($tagValues as $tagValue) {
            $hashTag = $this->findOrCreateHashTag($post->user_id, $tagValue);
            $hashTag->relevance += 1;
            $hashTag->save();
            $hashTags->push($hashTag);
        }

        $post->hashTags()->sync($hashTags->pluck('id')->toArray());
    }

    public function updateHashTagRelevance(HashTag $hashTag, int $relevance): HashTag
    {
        $hashTag->update(['relevance' => $relevance]);

        return $hashTag->fresh();
    }

    public function getHashTagsForUser(User $user): Collection
    {
        return HashTag::where('user_id', $user->id)->get();
    }

    public function getHashTagById(string $id): ?HashTag
    {
        return HashTag::find($id);
    }

    public function deleteHashTag(HashTag $hashTag): void
    {
        $hashTag->delete();
    }
}
