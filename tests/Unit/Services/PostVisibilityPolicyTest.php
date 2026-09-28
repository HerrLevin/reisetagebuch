<?php

namespace Tests\Unit\Services;

use App\Enums\Visibility;
use App\Models\User;
use App\Services\PostVisibilityPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostVisibilityPolicyTest extends TestCase
{
    private function policy(): PostVisibilityPolicy
    {
        return new PostVisibilityPolicy;
    }

    private function user(string $id): User
    {
        $user = new User;
        $user->id = $id;

        return $user;
    }

    public function test_listable_visibilities_for_anonymous_viewer(): void
    {
        $this->assertSame([Visibility::PUBLIC], $this->policy()->listableVisibilities(null));
    }

    public function test_listable_visibilities_for_authenticated_viewer(): void
    {
        $this->assertSame(
            [Visibility::PUBLIC, Visibility::ONLY_AUTHENTICATED],
            $this->policy()->listableVisibilities($this->user('1'))
        );
    }

    public function test_direct_access_visibilities_for_anonymous_viewer(): void
    {
        $this->assertSame([Visibility::PUBLIC, Visibility::UNLISTED], $this->policy()->directAccessVisibilities(null));
    }

    public function test_direct_access_visibilities_for_authenticated_viewer(): void
    {
        $this->assertSame(
            [Visibility::PUBLIC, Visibility::UNLISTED, Visibility::ONLY_AUTHENTICATED],
            $this->policy()->directAccessVisibilities($this->user('1'))
        );
    }

    public static function canViewDirectMatrix(): array
    {
        return [
            'public, no viewer' => [Visibility::PUBLIC, null, false, true],
            'public, non-owner viewer' => [Visibility::PUBLIC, '2', false, true],
            'public, owner' => [Visibility::PUBLIC, '1', true, true],
            'unlisted, no viewer' => [Visibility::UNLISTED, null, false, true],
            'unlisted, non-owner viewer' => [Visibility::UNLISTED, '2', false, true],
            'unlisted, owner' => [Visibility::UNLISTED, '1', true, true],
            'only_authenticated, no viewer' => [Visibility::ONLY_AUTHENTICATED, null, false, false],
            'only_authenticated, non-owner viewer' => [Visibility::ONLY_AUTHENTICATED, '2', false, true],
            'only_authenticated, owner' => [Visibility::ONLY_AUTHENTICATED, '1', true, true],
            'private, no viewer' => [Visibility::PRIVATE, null, false, false],
            'private, non-owner viewer' => [Visibility::PRIVATE, '2', false, false],
            'private, owner' => [Visibility::PRIVATE, '1', true, true],
        ];
    }

    #[DataProvider('canViewDirectMatrix')]
    public function test_can_view_direct(Visibility $visibility, ?string $viewerId, bool $isOwner, bool $expected): void
    {
        $viewer = $viewerId === null ? null : $this->user($viewerId);

        $this->assertSame($expected, $this->policy()->canViewDirect($visibility, '1', $viewer));
    }
}
