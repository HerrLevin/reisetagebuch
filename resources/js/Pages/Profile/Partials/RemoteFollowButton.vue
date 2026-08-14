<script setup lang="ts">
import { api } from '@/api';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    actorId: string;
}>();

const followState = defineModel<string | null>('followState', {
    required: true,
});

const following = defineModel<boolean>('following', { default: false });

async function follow() {
    following.value = true;

    try {
        await api.instance.post('/activitypub/follow', {
            actor_id: props.actorId,
        });
        followState.value = 'pending';
    } finally {
        following.value = false;
    }
}

async function unfollow() {
    await api.instance.delete('/activitypub/follow', {
        data: { actor_id: props.actorId },
    });
    followState.value = null;
}
</script>

<template>
    <button
        v-if="!followState"
        class="btn btn-primary btn-sm shrink-0"
        :disabled="following"
        @click="follow"
    >
        {{ t('fediverse.follow') }}
    </button>
    <button
        v-else-if="followState === 'pending'"
        class="btn btn-sm shrink-0"
        disabled
    >
        {{ t('fediverse.pending') }}
    </button>
    <button
        v-else-if="followState === 'accepted'"
        class="btn btn-sm shrink-0"
        @click="unfollow"
    >
        {{ t('fediverse.unfollow') }}
    </button>
    <span
        v-else-if="followState === 'rejected'"
        class="badge badge-error badge-sm shrink-0"
    >
        {{ t('fediverse.rejected') }}
    </span>
</template>
