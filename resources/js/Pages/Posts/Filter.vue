<script setup lang="ts">
import InfiniteScroller from '@/Components/InfiniteScroller.vue';
import Post from '@/Components/Post/Post.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getTravelReasonLabel } from '@/Services/TravelReasonMapping';
import { getVisibilityLabel } from '@/Services/VisibilityMapping';
import { TravelReason, Visibility } from '@/types/enums';
import { AllPosts } from '@/types/PostTypes';
import { Head, router } from '@inertiajs/vue3';
import { PropType, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    posts: {
        type: Array as PropType<AllPosts[]>,
        default: () => [],
    },
    filters: {
        type: Object as PropType<{
            dateFrom: string | null;
            dateTo: string | null;
            visibility: Visibility[];
            travelReason: TravelReason[];
            tags: string[];
        }>,
        required: true,
    },
    availableTags: {
        type: Array as PropType<string[]>,
        default: () => [],
    },
});

const filterForm = reactive({
    dateFrom: props.filters.dateFrom || '',
    dateTo: props.filters.dateTo || '',
    visibility: props.filters.visibility || [],
    travelReason: props.filters.travelReason || [],
    tags: props.filters.tags || [],
});

const applyFilters = () => {
    router.get(
        route('posts.filter'),
        {
            dateFrom: filterForm.dateFrom || null,
            dateTo: filterForm.dateTo || null,
            visibility:
                filterForm.visibility.length > 0 ? filterForm.visibility : null,
            travelReason:
                filterForm.travelReason.length > 0
                    ? filterForm.travelReason
                    : null,
            tags: filterForm.tags.length > 0 ? filterForm.tags : null,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    filterForm.dateFrom = '';
    filterForm.dateTo = '';
    filterForm.visibility = [];
    filterForm.travelReason = [];
    filterForm.tags = [];
    applyFilters();
};

const toggleVisibility = (value: Visibility) => {
    const index = filterForm.visibility.indexOf(value);
    if (index > -1) {
        filterForm.visibility.splice(index, 1);
    } else {
        filterForm.visibility.push(value);
    }
};

const toggleTravelReason = (value: TravelReason) => {
    const index = filterForm.travelReason.indexOf(value);
    if (index > -1) {
        filterForm.travelReason.splice(index, 1);
    } else {
        filterForm.travelReason.push(value);
    }
};

const toggleTag = (tag: string) => {
    const index = filterForm.tags.indexOf(tag);
    if (index > -1) {
        filterForm.tags.splice(index, 1);
    } else {
        filterForm.tags.push(tag);
    }
};

const hasActiveFilters = () => {
    return (
        filterForm.dateFrom ||
        filterForm.dateTo ||
        filterForm.visibility.length > 0 ||
        filterForm.travelReason.length > 0 ||
        filterForm.tags.length > 0
    );
};

// automatically apply filters when form changes. Debounce to avoid too many requests.
let debounceTimer: number | null = null;
watch(
    () => ({ ...filterForm }),
    () => {
        if (debounceTimer !== null) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = window.setTimeout(() => {
            applyFilters();
        }, 500);
    },
    { deep: true },
);
</script>

<template>
    <Head :title="t('posts.filter.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('posts.filter.title') }}
            </h2>
        </template>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="card-title text-base">
                                {{ t('posts.filter.filters') }}
                            </h3>
                            <button
                                v-if="hasActiveFilters()"
                                class="btn btn-sm btn-ghost"
                                @click="clearFilters"
                            >
                                {{ t('posts.filter.clear_all') }}
                            </button>
                        </div>

                        <div class="space-y-6">
                            <!-- Date Range Filter -->
                            <div>
                                <label class="label">
                                    <span class="label-text font-semibold">
                                        {{ t('posts.filter.date_range') }}
                                    </span>
                                </label>
                                <div class="space-y-2">
                                    <div>
                                        <label
                                            class="label label-text text-xs"
                                            >{{ t('posts.filter.from') }}</label
                                        >
                                        <input
                                            v-model="filterForm.dateFrom"
                                            type="date"
                                            class="input input-bordered input-sm w-full"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="label label-text text-xs"
                                            >{{ t('posts.filter.to') }}</label
                                        >
                                        <input
                                            v-model="filterForm.dateTo"
                                            type="date"
                                            class="input input-bordered input-sm w-full"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Visibility Filter -->
                            <div>
                                <label class="label">
                                    <span class="label-text font-semibold">
                                        {{ t('posts.filter.visibility') }}
                                    </span>
                                </label>
                                <div class="space-y-1">
                                    <label
                                        v-for="option in Visibility"
                                        :key="option"
                                        class="label cursor-pointer justify-start gap-2"
                                    >
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-sm"
                                            :checked="
                                                filterForm.visibility.includes(
                                                    option,
                                                )
                                            "
                                            @change="toggleVisibility(option)"
                                        />
                                        <span class="label-text">
                                            {{ getVisibilityLabel(option) }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Travel Reason Filter -->
                            <div>
                                <label class="label">
                                    <span class="label-text font-semibold">
                                        {{ t('posts.filter.travel_reason') }}
                                    </span>
                                </label>
                                <div class="space-y-1">
                                    <label
                                        v-for="option in TravelReason"
                                        :key="option"
                                        class="label cursor-pointer justify-start gap-2"
                                    >
                                        <input
                                            type="checkbox"
                                            class="checkbox checkbox-sm"
                                            :checked="
                                                filterForm.travelReason.includes(
                                                    option,
                                                )
                                            "
                                            @change="toggleTravelReason(option)"
                                        />
                                        <span class="label-text">
                                            {{ getTravelReasonLabel(option) }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Tags Filter -->
                            <div v-if="availableTags.length > 0">
                                <label class="label">
                                    <span class="label-text font-semibold">
                                        {{ t('posts.filter.tags') }}
                                    </span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="tag in availableTags"
                                        :key="tag"
                                        class="btn btn-sm"
                                        :class="
                                            filterForm.tags.includes(tag)
                                                ? 'btn-primary'
                                                : 'btn-outline'
                                        "
                                        @click="toggleTag(tag)"
                                    >
                                        {{ tag }}
                                    </button>
                                </div>
                            </div>

                            <button
                                class="btn btn-primary btn-block"
                                @click="applyFilters"
                            >
                                {{ t('posts.filter.apply_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts List -->
            <div class="lg:col-span-3">
                <div class="card bg-base-100 shadow-md">
                    <ul class="list">
                        <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                            <span v-if="hasActiveFilters()">
                                {{ t('posts.filter.filtered_results') }}
                            </span>
                            <span v-else>
                                {{ t('posts.filter.all_posts') }}
                            </span>
                        </li>
                        <li
                            v-for="post in posts"
                            :key="post.id"
                            class="list-row hover-list-entry cursor-pointer"
                            @click="
                                $inertia.visit(route('posts.show', post.id))
                            "
                        >
                            <Post :post="post"></Post>
                        </li>
                        <li v-if="posts.length === 0" class="p-8 text-center">
                            <div class="text-base-content/60">
                                {{ t('posts.filter.no_results') }}
                            </div>
                        </li>
                        <InfiniteScroller :only="['posts']" />
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hover-list-entry {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.hover-list-entry::after {
    border-color: var(--color-base-300);
}
.hover-list-entry:hover {
    background-color: var(--color-base-200);
}
</style>
