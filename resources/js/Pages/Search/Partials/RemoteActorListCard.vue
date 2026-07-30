<script setup lang="ts">
import { PropType } from 'vue';
import { RemoteFollowDto, RemoteFollowerDto } from '../../../../types/Api.gen';

defineProps({
    actor: {
        type: Object as PropType<RemoteFollowDto | RemoteFollowerDto>,
        required: true,
    },
});

function instanceOf(actorId: string): string {
    try {
        return new URL(actorId).hostname;
    } catch {
        return actorId;
    }
}
</script>

<template>
    <div class="avatar">
        <div class="bg-primary size-10 rounded-xl">
            <img
                v-if="actor.iconUrl"
                :src="actor.iconUrl"
                class="rounded-xl object-cover"
                alt=""
            />
        </div>
    </div>
    <div class="list-col-grow ms-4">
        <a
            :href="actor.profileUrl ?? undefined"
            target="_blank"
            rel="noopener noreferrer"
        >
            <span class="font-bold hover:underline">
                {{ actor.displayName || actor.preferredUsername }}
            </span>
        </a>
        <p class="opacity-60">
            @{{ actor.preferredUsername }}@{{ instanceOf(actor.actorId) }}
        </p>
    </div>
</template>
