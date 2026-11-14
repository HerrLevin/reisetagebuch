<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\AdminStatisticsDto;
use App\Http\Controllers\Controller;
use App\Models\ActivityPubFollower;
use App\Models\ActivityPubPost;
use App\Models\ActivityPubServer;
use App\Models\Invite;
use App\Models\Post;
use App\Models\User;

class AdminStatisticsBackend extends Controller
{
    public function show(): AdminStatisticsDto
    {
        $monthAgo = now()->subMonth();

        return new AdminStatisticsDto(
            users: User::count(),
            activeUsersMonth: User::where('updated_at', '>=', $monthAgo)->count(),
            posts: Post::count(),
            postsMonth: Post::where('created_at', '>=', $monthAgo)->count(),
            invites: Invite::count(),
            federatedServers: ActivityPubServer::count(),
            federatedServersMonth: ActivityPubServer::where('last_seen_at', '>=', $monthAgo)->count(),
            remoteFollowers: ActivityPubFollower::count(),
            remotePosts: ActivityPubPost::count(),
            remotePostsMonth: ActivityPubPost::where('created_at', '>=', $monthAgo)->count(),
        );
    }
}
