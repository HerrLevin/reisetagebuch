<script setup lang="ts">
import { api } from '@/api';
import { getOwnShareText, getShareText } from '@/Services/ApiPostTextService';
import { useUserStore } from '@/stores/user';
import { isApiTransportPost } from '@/types/PostTypes';
import { Link, useForm } from '@inertiajs/vue3';
import {
    ClockPlus,
    Ellipsis,
    PlaneLanding,
    PlaneTakeoff,
    Route,
    Share,
    SquarePen,
    Trash2,
    UserRoundPlus,
} from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { PropType, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { BasePost, TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();

const user = useUserStore();

const props = defineProps({
    post: {
        type: Object as PropType<BasePost>,
        required: true,
    },
});

const emit = defineEmits<{
    (e: 'delete:post', postId: string): void;
    (e: 'update:post', post: BasePost): void;
}>();

const deleteModal = useTemplateRef('deleteModal');
const form = useForm({});
const deleteProcessing = ref(false);

function showTimeButtons(): boolean {
    const post = props.post;
    if (!isApiTransportPost(post)) {
        return false;
    }

    const originDeparture = post.originStop.departureTime
        ? DateTime.fromISO(post.originStop.departureTime)
        : null;
    const originArrival = post.originStop.arrivalTime
        ? DateTime.fromISO(post.originStop.arrivalTime)
        : null;
    const destinationDeparture = post.destinationStop.departureTime
        ? DateTime.fromISO(post.destinationStop.departureTime)
        : null;
    const destinationArrival = post.destinationStop.arrivalTime
        ? DateTime.fromISO(post.destinationStop.arrivalTime)
        : null;

    // Show buttons if current time is 1 hour before or after any of the times
    const now = DateTime.now();
    const oneHourBefore = now.minus({ hours: 1 });
    const oneHourAfter = now.plus({ hours: 1 });
    const times = [
        originDeparture,
        originArrival,
        destinationDeparture,
        destinationArrival,
    ];
    for (const time of times) {
        if (time) {
            if (time >= oneHourBefore && time <= oneHourAfter) {
                return true;
            }
        }
    }
    return false;
}

function departNow(): void {
    const post = props.post;
    if (!isApiTransportPost(post)) {
        return;
    }

    const now = DateTime.now();
    submit(now.toISO(), undefined);
}

function arriveNow(): void {
    const post = props.post;
    if (!isApiTransportPost(post)) {
        return;
    }
    const now = DateTime.now();
    submit(undefined, now.toISO());
}

function submit(
    departure: string | undefined,
    arrival: string | undefined,
): void {
    blur();
    const post = props.post;
    if (!isApiTransportPost(post)) {
        return;
    }
    const data = {
        manualDepartureTime: departure,
        manualArrivalTime: arrival,
    };

    api.posts
        .updateTransportTimes(post.id, data)
        .then((response) => {
            emit('update:post', response.data);
        })
        .catch((error_) => {
            alert(error_.response.data.message);
        });
}

function deletePost() {
    deleteProcessing.value = true;
    api.posts
        .deletePost(props.post.id)
        .then(() => {
            deleteModal.value?.close();
            emit('delete:post', props.post.id);
        })
        .catch(() => {
            // handle error
        })
        .finally(() => {
            deleteProcessing.value = false;
        });
}

const isSameUser = () => {
    return props.post.user.id === user.user?.id;
};

function sharePost(): void {
    blur();

    const shareData = {
        title: t('share.post_title'),
        text: isSameUser()
            ? getOwnShareText(props.post)
            : getShareText(props.post),
        url: route('posts.show', props.post.id),
    };

    if (navigator.canShare && navigator.canShare(shareData)) {
        navigator.share(shareData).then().catch();
    } else {
        // copy the link to the clipboard
        navigator.clipboard
            .writeText(shareData.url)
            .then(() => {
                alert(t('share.post_copied'));
            })
            .catch();
    }
}

function redirectCreatePost(): void {
    const post = props.post as TransportPost;

    const params = {
        tripId: post.trip.foreignId || post.trip.id,
        startId: post.originStop.location.id,
        startTime: post.originStop.departureTime || post.originStop.arrivalTime,
        stopId: post.destinationStop.location.id,
        stopTime:
            post.destinationStop.arrivalTime ||
            post.destinationStop.departureTime,
        stopName: post.destinationStop.name,
        stopMode: post.trip.mode,
        lineName: post.trip.displayName || post.trip.lineName,
    };
    window.location.href = route('posts.create.transport-post', params);
}

function blur() {
    (document.activeElement as HTMLElement)?.blur();
}
</script>

<template>
    <div class="dropdown dropdown-top dropdown-end" @click.prevent>
        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
            <Ellipsis />
        </div>
        <ul
            tabindex="-1"
            class="menu dropdown-content bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
        >
            <li>
                <a @click.prevent="sharePost()">
                    <Share class="size-4" />
                    {{ t('verbs.share') }}
                </a>
            </li>
            <li v-if="!isSameUser() && isApiTransportPost(props.post)">
                <a @click.prevent="redirectCreatePost()">
                    <UserRoundPlus class="size-4" />
                    {{ t('posts.ride_along') }}
                </a>
            </li>
            <template v-if="isSameUser()">
                <li class="mx-0 border-b-1"></li>
                <li>
                    <Link :href="route('posts.edit', post.id)">
                        <SquarePen class="size-4" />
                        {{ t('verbs.edit') }}
                    </Link>
                </li>
                <template v-if="isApiTransportPost(post)">
                    <li>
                        <Link
                            :href="route('posts.edit.transport-times', post.id)"
                        >
                            <ClockPlus class="size-4" />
                            {{ t('posts.edit.change_times') }}
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="
                                route('posts.edit.transport-post', {
                                    postId: post.id,
                                    tripId: post.trip.foreignId,
                                    startId: post.originStop.id,
                                    startTime:
                                        post.originStop.arrivalTime ||
                                        post.originStop.departureTime,
                                })
                            "
                        >
                            <Route class="size-4" />
                            {{ t('posts.edit.change_exit') }}
                        </Link>
                    </li>
                    <template v-if="showTimeButtons()">
                        <li>
                            <a href="#" @click="departNow()">
                                <PlaneTakeoff class="size-5" />
                                {{ t('edit_transport_times.depart_now') }}
                            </a>
                        </li>
                        <li>
                            <a href="#" @click="arriveNow()">
                                <PlaneLanding class="size-5" />
                                {{ t('edit_transport_times.arrive_now') }}
                            </a>
                        </li>
                    </template>
                </template>
                <li class="mx-0 border-b-1"></li>
                <li>
                    <a
                        @click.prevent="
                            blur();
                            deleteModal?.showModal();
                        "
                    >
                        <Trash2 class="size-4" />
                        <span class="text-red-500">
                            {{ t('verbs.delete') }}
                        </span>
                    </a>
                </li>
            </template>
        </ul>
    </div>
    <dialog ref="deleteModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                {{ t('posts.delete.question') }}
            </h3>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn" @click.prevent="deleteModal?.close()">
                        {{ t('verbs.cancel') }}
                    </button>
                </form>

                <button
                    class="btn btn-error"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="deleteProcessing"
                    @click.prevent="deletePost()"
                >
                    {{ t('verbs.delete') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ t('verbs.close') }}</button>
        </form>
    </dialog>
</template>
