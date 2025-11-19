<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    metaInfos: Record<string, string | string[]>;
}>();

const metaInfoEntries = computed(() => {
    return Object.entries(props.metaInfos).map(([key, value]) => ({
        key,
        value,
        label: getMetaInfoLabel(key),
        displayValue: getMetaInfoDisplayValue(key, value),
    }));
});

const getMetaInfoLabel = (key: string): string => {
    // Extract the label from the key (e.g., "rtb:travel_reason" -> "Travel Reason")
    const keyPart = key.split(':')[1] || key;
    return keyPart
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getMetaInfoDisplayValue = (
    key: string,
    value: string | string[],
): string => {
    if (Array.isArray(value)) {
        return value.join(', ');
    }
    // Handle travel reason enum values
    if (key === 'rtb:travel_reason') {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }
    return value;
};
</script>

<template>
    <div
        v-if="metaInfoEntries.length > 0"
        class="card bg-base-100 min-w-full shadow-md"
    >
        <div class="card-body">
            <h3 class="card-title text-lg">{{ t('posts.meta_info.title') }}</h3>
            <div class="space-y-2">
                <div
                    v-for="entry in metaInfoEntries"
                    :key="entry.key"
                    class="flex items-center justify-between"
                >
                    <span class="text-base-content/70 font-medium">
                        {{ entry.label }}
                    </span>
                    <span class="text-base-content">
                        {{ entry.displayValue }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
