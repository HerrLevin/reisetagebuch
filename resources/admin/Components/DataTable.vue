<script setup lang="ts" generic="T extends Record<string, unknown>">
import { sortRows, type SortDirection } from '@admin/Services/sortRows';
import Loading from '@shared/Components/Loading.vue';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
}

const props = defineProps<{
    columns: Column[];
    rows: T[];
    /** Field that uniquely identifies a row, used as the v-for key. */
    rowKey: string;
    loading?: boolean;
}>();

// Custom cell content via a `#cell-<key>` slot, which gets { row, value }.
defineSlots<
    Record<`cell-${string}`, (props: { row: T; value: unknown }) => unknown>
>();

const { t } = useI18n();
const sortKey = ref<string | null>(null);
const sortDirection = ref<SortDirection>('asc');

function toggleSort(key: string) {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDirection.value = 'asc';
    }
}

const sortedRows = computed(() =>
    sortKey.value
        ? sortRows(props.rows, sortKey.value, sortDirection.value)
        : props.rows,
);

function ariaSort(key: string) {
    if (sortKey.value !== key) return 'none';
    return sortDirection.value === 'asc' ? 'ascending' : 'descending';
}
</script>

<template>
    <div class="bg-base-100 rounded-box overflow-x-auto shadow">
        <table class="table">
            <thead>
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :aria-sort="
                            column.sortable ? ariaSort(column.key) : undefined
                        "
                    >
                        <button
                            v-if="column.sortable"
                            type="button"
                            class="flex items-center gap-1"
                            :aria-label="
                                t('admin.table.sort_by', {
                                    column: column.label,
                                })
                            "
                            @click="toggleSort(column.key)"
                        >
                            {{ column.label }}
                            <ChevronUp
                                v-if="
                                    sortKey === column.key &&
                                    sortDirection === 'asc'
                                "
                                class="size-4"
                            />
                            <ChevronDown
                                v-else-if="sortKey === column.key"
                                class="size-4"
                            />
                        </button>
                        <template v-else>{{ column.label }}</template>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td :colspan="columns.length" class="text-center">
                        <Loading />
                    </td>
                </tr>
                <tr v-else-if="!rows.length">
                    <td
                        :colspan="columns.length"
                        class="text-center opacity-65"
                    >
                        {{ t('admin.table.empty') }}
                    </td>
                </tr>
                <template v-else>
                    <tr
                        v-for="row in sortedRows"
                        :key="String(row[rowKey])"
                        class="hover:bg-base-200"
                    >
                        <td v-for="column in columns" :key="column.key">
                            <slot
                                :name="`cell-${column.key}`"
                                :row="row"
                                :value="row[column.key]"
                            >
                                {{ row[column.key] }}
                            </slot>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>
