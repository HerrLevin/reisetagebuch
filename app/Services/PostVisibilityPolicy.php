<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Visibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centralizes "which posts can $viewer see" so the rule isn't duplicated (and
 * drifting) across every timeline/profile/single-post query. There are two distinct
 * rule sets, kept separate on purpose: listings (timelines, profile feeds) always
 * exclude UNLISTED — it's link-only, never meant to appear in a list — while direct
 * access (a permalink) allows it, since that's the whole point of that visibility level.
 */
class PostVisibilityPolicy
{
    /**
     * @return Visibility[]
     */
    public function listableVisibilities(?User $viewer): array
    {
        return $viewer !== null
            ? [Visibility::PUBLIC, Visibility::ONLY_AUTHENTICATED]
            : [Visibility::PUBLIC];
    }

    /**
     * @return Visibility[]
     */
    public function directAccessVisibilities(?User $viewer): array
    {
        $visibilities = [Visibility::PUBLIC, Visibility::UNLISTED];
        if ($viewer !== null) {
            $visibilities[] = Visibility::ONLY_AUTHENTICATED;
        }

        return $visibilities;
    }

    /**
     * Constrains $query to posts $viewer may see when listing $ownerId's posts
     * (e.g. a profile page) — the owner sees everything, everyone else is limited to
     * listableVisibilities(). Deliberately has no following check: seeing someone's
     * profile at all already implies visiting their page directly.
     */
    public function scopeListableForOwner(Builder $query, string $ownerId, ?User $viewer): Builder
    {
        if ($viewer !== null && $viewer->id === $ownerId) {
            return $query;
        }

        return $query->whereIn('visibility', $this->listableVisibilities($viewer));
    }

    /**
     * Constrains $query to a followed user's posts visible in $viewer's personal
     * following-feed.
     */
    public function scopeListableFromFollowing(Builder $query, User $viewer): Builder
    {
        return $query->whereIn('visibility', $this->listableVisibilities($viewer));
    }

    /**
     * Single-post check for direct/permalink access (e.g. getById()) — the owner can
     * always view their own post regardless of visibility, including PRIVATE.
     */
    public function canViewDirect(Visibility $visibility, string $ownerId, ?User $viewer): bool
    {
        if ($viewer?->id === $ownerId) {
            return true;
        }

        return in_array($visibility, $this->directAccessVisibilities($viewer), true);
    }
}
