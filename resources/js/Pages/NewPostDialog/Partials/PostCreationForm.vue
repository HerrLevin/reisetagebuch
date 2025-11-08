<script setup lang="ts">
import TagsInput from '@/Pages/NewPostDialog/Partials/TagsInput.vue';
import {
    getTravelReasonDescription,
    getTravelReasonIcon,
    getTravelReasonLabel,
} from '@/Services/TravelReasonMapping';
import {
    getVisibilityDescription,
    getVisibilityIcon,
    getVisibilityLabel,
} from '@/Services/VisibilityMapping';
import { TravelReason, Visibility } from '@/types/enums';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const model = defineModel({ default: '', type: String });

const props = defineProps({
    emoji: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    defaultVisibility: {
        type: String as () => Visibility | null,
        default: null,
        required: false,
    },
    subtitle: {
        type: String || null,
        default: null,
    },
    confirmButtonText: {
        type: String,
        default: null,
    },
    tags: {
        type: Array as () => string[],
        default: () => [],
    },
    travelReason: {
        type: String as () => TravelReason,
        default: null,
    },
    showTravelReason: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    'cancel',
    'selectVisibility',
    'selectTravelReason',
    'update:modelValue',
    'update:tags',
]);
const selectedVisibility = ref(props.defaultVisibility || Visibility.PUBLIC);
const selectedTravelReason = ref(props.travelReason || TravelReason.LEISURE);

if (!props.defaultVisibility) {
    recallStoredVisibility();
}

if (!props.travelReason) {
    recallStoredTravelReason();
}

function recallStoredVisibility() {
    const visibility = localStorage.getItem('recentVisibility');
    if (
        visibility &&
        Object.values(Visibility).includes(visibility as Visibility)
    ) {
        selectVisibility(visibility as Visibility);
    }
}

function recallStoredTravelReason() {
    const travelReason = localStorage.getItem('recentTravelReason');
    if (
        travelReason &&
        Object.values(TravelReason).includes(travelReason as TravelReason)
    ) {
        selectTravelReason(travelReason as TravelReason);
    }
}

function selectVisibility(visibility: Visibility) {
    localStorage.setItem('recentVisibility', visibility);
    selectedVisibility.value = visibility;
    blur();
    emit('selectVisibility', visibility);
}

function selectTravelReason(travelReason: TravelReason) {
    localStorage.setItem('recentTravelReason', travelReason);
    selectedTravelReason.value = travelReason;
    blur();
    emit('selectTravelReason', travelReason);
}

function blur() {
    (document.activeElement as HTMLElement)?.blur();
}
function updateTags(tags: string[]) {
    emit('update:tags', tags);
}
</script>

<template>
    <div
        class="flex w-full items-center gap-4 p-8"
        :class="{ 'pb-0': !!subtitle }"
    >
        <div class="text-3xl">{{ emoji }}</div>
        <div class="text-xl">{{ name }}</div>
    </div>
    <div v-if="subtitle" class="px-8 pb-8 text-sm opacity-70">
        {{ subtitle }}
    </div>
    <div class="bg-base-200 w-full">
        <div class="dropdown py-2 ps-4">
            <div
                tabindex="0"
                role="button"
                class="btn btn-outline btn-primary btn-sm"
            >
                <component
                    :is="getVisibilityIcon(selectedVisibility)"
                    class="inline size-4"
                />
                <span class="ml-2">
                    {{ getVisibilityLabel(selectedVisibility) }}
                </span>
            </div>
            <ul
                tabindex="-1"
                class="menu dropdown-content bg-base-100 rounded-box z-1 w-72 p-2 shadow-sm"
            >
                <template v-for="option in Visibility" :key="option.value">
                    <li class="min-w-full">
                        <a
                            :class="{
                                'bg-primary': option == selectedVisibility,
                                'text-neutral-content':
                                    option == selectedVisibility,
                            }"
                            @click.prevent="selectVisibility(option)"
                        >
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col">
                                    <component
                                        :is="getVisibilityIcon(option)"
                                        class="inline size-4"
                                    />
                                </div>
                                <div class="col-span-11">
                                    <div class="font-bold">
                                        {{ getVisibilityLabel(option) }}
                                    </div>
                                    <div class="text-sm opacity-70">
                                        {{ getVisibilityDescription(option) }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                </template>
            </ul>
        </div>
        <div
            v-show="showTravelReason"
            class="dropdown dropdown-center py-2 ps-2"
        >
            <div
                tabindex="0"
                role="button"
                class="btn btn-outline btn-primary btn-sm"
            >
                <component
                    :is="getTravelReasonIcon(selectedTravelReason)"
                    v-if="getTravelReasonIcon(selectedTravelReason)"
                    class="inline size-4"
                />
                <span class="ml-2">
                    {{ getTravelReasonLabel(selectedTravelReason) }}
                </span>
            </div>
            <ul
                tabindex="-1"
                class="menu dropdown-content bg-base-100 rounded-box z-1 w-72 p-2 shadow-sm"
            >
                <template v-for="option in TravelReason" :key="option.value">
                    <li class="min-w-full">
                        <a
                            :class="{
                                'bg-primary': option === selectedTravelReason,
                                'text-neutral-content':
                                    option === selectedTravelReason,
                            }"
                            @click.prevent="selectTravelReason(option)"
                        >
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col">
                                    <component
                                        :is="getTravelReasonIcon(option)"
                                        v-if="getTravelReasonIcon(option)"
                                        class="inline size-4"
                                    />
                                </div>
                                <div class="col-span-11">
                                    <div class="font-bold">
                                        {{ getTravelReasonLabel(option) }}
                                    </div>
                                    <div class="text-sm opacity-70">
                                        {{ getTravelReasonDescription(option) }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                </template>
            </ul>
        </div>
        <textarea
            v-model="model"
            class="textarea textarea-ghost transparent-input w-full"
            :placeholder="t('new_post.body_placeholder')"
        ></textarea>
        <TagsInput :tags="tags" @update:tags="updateTags"></TagsInput>
    </div>
    <div class="flex w-full justify-end gap-4 p-8"></div>
    <div class="flex w-full justify-end gap-4 px-8 py-4">
        <button class="btn btn-secondary" @click.prevent="$emit('cancel')">
            {{ t('verbs.cancel') }}
        </button>
        <button class="btn btn-primary" type="submit">
            {{ confirmButtonText || t('verbs.confirm') }}
        </button>
    </div>
</template>
