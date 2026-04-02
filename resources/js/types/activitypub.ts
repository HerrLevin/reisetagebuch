export interface ActivityPubRemoteInteractions {
    likes: number;
    boosts: number;
    replies: ActivityPubReply[];
}

export interface ActivityPubReply {
    id: string;
    actorUsername: string;
    actorDisplayName: string | null;
    actorAvatar: string | null;
    actorInstance: string;
    content: string | null;
    remoteUrl: string | null;
    createdAt: string;
}

export interface RemotePostLikedData {
    actorUsername: string;
    actorDisplayName: string | null;
    actorAvatar: string | null;
    actorInstance: string;
    postId: string;
    postBody: string | null;
}

export interface RemotePostBoostedData {
    actorUsername: string;
    actorDisplayName: string | null;
    actorAvatar: string | null;
    actorInstance: string;
    postId: string;
    postBody: string | null;
}

export interface RemotePostRepliedData {
    actorUsername: string;
    actorDisplayName: string | null;
    actorAvatar: string | null;
    actorInstance: string;
    postId: string;
    postBody: string | null;
    replyContent: string | null;
}

export interface RemoteUserFollowedData {
    actorUsername: string;
    actorDisplayName: string | null;
    actorAvatar: string | null;
    actorInstance: string;
}
