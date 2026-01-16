<script setup lang="ts">
import { api } from '@/app';
import SelectInput from '@/Components/SelectInput.vue';
import TagsInput from '@/Pages/NewPostDialog/Partials/TagsInput.vue';
import { getVisibilityLabel } from '@/Services/VisibilityMapping';
import { TravelReason, Visibility } from '@/types/enums';
import { computed, PropType, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { BasePost, LocationPost, TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();

type AllPosts = BasePost | TransportPost | LocationPost;

const props = defineProps({
    selectedPosts: {
        type: Array as PropType<AllPosts[]>,
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'updated']);

const existingTags = ref<string[]>([]);
const changeTags = ref(false);
const processing = ref(false);

const form = ref({
    visibility: null as Visibility | null,
    travelReason: null as TravelReason | null,
    tags: null as string[] | null,
});

function mapTags() {
    props.selectedPosts?.forEach((post) => {
        post.hashTags.forEach((tag) => {
            if (!existingTags.value.includes(tag)) {
                existingTags.value.push(tag);
            }
        });
    });
}

const postIds = computed(() => props.selectedPosts.map((p) => p.id));

const visibilityOptions = () => {
    const options: { value: null | Visibility; label: string }[] = [
        { value: null, label: t('posts.mass_edit.no_change') },
    ];
    Object.values(Visibility).forEach((visibility) => {
        options.push({
            value: visibility,
            label: getVisibilityLabel(visibility),
        });
    });
    return options;
};

const travelReasonOptions = () => {
    const options: { value: null | TravelReason; label: string }[] = [
        { value: null, label: t('posts.mass_edit.no_change') },
    ];
    Object.values(TravelReason).forEach((reason) => {
        options.push({
            value: reason,
            label: t(`travel_reason.${reason.toLowerCase()}`),
        });
    });

    return options;
};

const resetForm = () => {
    form.value = {
        visibility: null,
        travelReason: null,
        tags: null,
    };
    existingTags.value = [];
    changeTags.value = false;
};

const submit = () => {
    processing.value = true;
    const tags = changeTags.value ? existingTags.value.slice(0, 5) : null;

    api.posts
        .massEditPosts({
            postIds: postIds.value,
            visibility: form.value.visibility,
            travelReason: form.value.travelReason,
            tags,
        })
        .then(() => {
            emit('updated');
            emit('close');
            resetForm();
        })
        .finally(() => {
            processing.value = false;
        });
};

const cancel = () => {
    resetForm();
    emit('close');
};

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            mapTags();
        } else {
            resetForm();
        }
    },
);
</script>

<template>
    <div
        v-if="show"
        class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black"
        @click.self="cancel"
    >
        <div class="bg-base-100 mx-4 w-full max-w-2xl rounded-lg p-6 shadow-xl">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold">
                    {{ t('posts.mass_edit.title') }}
                </h3>
                <button
                    type="button"
                    class="btn btn-sm btn-circle btn-ghost"
                    @click="cancel"
                >
                    ✕
                </button>
            </div>

            <div class="mb-4 text-sm opacity-70">
                {{
                    t('posts.mass_edit.selected_count', {
                        count: selectedPosts.length,
                    })
                }}
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label class="label">
                            <span class="label-text">
                                {{ t('posts.mass_edit.visibility') }}
                            </span>
                        </label>
                        <SelectInput
                            v-model="form.visibility"
                            :options="visibilityOptions()"
                        />
                    </div>

                    <div>
                        <label class="label">
                            <span class="label-text">
                                {{ t('posts.mass_edit.travel_reason') }}
                            </span>
                        </label>
                        <SelectInput
                            v-model="form.travelReason"
                            :options="travelReasonOptions()"
                        />
                    </div>

                    <div>
                        <div class="form-control mt-2">
                            <label
                                class="label cursor-pointer justify-start gap-2"
                            >
                                <input
                                    v-model="changeTags"
                                    type="checkbox"
                                    class="checkbox checkbox-sm"
                                />
                                <p class="label-text">
                                    {{ t('posts.mass_edit.change_tags') }}
                                </p>
                            </label>
                            <p v-show="changeTags" class="text-warning">
                                {{ t('posts.mass_edit.change_tags_warning') }}
                            </p>
                        </div>
                        <label class="label">
                            <span class="label-text">
                                {{ t('posts.mass_edit.tags') }}
                            </span>
                        </label>
                        <br />
                        <TagsInput
                            :disabled="!changeTags"
                            :tags="existingTags"
                            @update:tags="(newTags) => (existingTags = newTags)"
                        />
                        <div v-show="existingTags.length > 5">
                            <p class="text-error">
                                {{
                                    t('posts.mass_edit.too_many_tags', {
                                        max: 5,
                                    })
                                }}
                            </p>
                            <p class="opacity-60">
                                {{
                                    t('posts.mass_edit.too_many_tags_detail', {
                                        max: 5,
                                    })
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn btn-ghost" @click="cancel">
                        {{ t('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="processing"
                    >
                        {{
                            processing
                                ? t('common.saving')
                                : t('posts.mass_edit.apply')
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
