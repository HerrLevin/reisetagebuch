<script setup lang="ts">
import LoadingButton from '@shared/Components/LoadingButton.vue';
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
    <LoadingButton
        v-if="!followState"
        class="btn btn-primary btn-sm shrink-0"
        :loading="following"
        :action="follow"
    >
        {{ t('fediverse.follow') }}
    </LoadingButton>
    <button
        v-else-if="followState === 'pending'"
        class="btn btn-sm shrink-0"
        disabled
    >
        {{ t('fediverse.pending') }}
    </button>
    <LoadingButton
        v-else-if="followState === 'accepted'"
        class="btn btn-sm shrink-0"
        :action="unfollow"
    >
        {{ t('fediverse.unfollow') }}
    </LoadingButton>
    <span
        v-else-if="followState === 'rejected'"
        class="badge badge-error badge-sm shrink-0"
    >
        {{ t('fediverse.rejected') }}
    </span>
</template>
