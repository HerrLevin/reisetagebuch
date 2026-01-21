<script setup lang="ts">
import { api } from '@/api';
import Typeahead, { Suggestion } from '@/Components/Typeahead.vue';
import { Area, StopDto } from '@/types';
import { PropType, ref, watch } from 'vue';
import { debounce } from 'vue-debounce';
import { useI18n } from 'vue-i18n';
import { MotisGeocodeResponseEntry } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    latitude: {
        type: Number,
        default: 0,
    },
    longitude: {
        type: Number,
        default: 0,
    },
    stop: {
        type: Object as PropType<StopDto | null>,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
});

const search = ref('');
const suggestions = ref<Suggestion[]>([]);
const loading = ref(false);

const emit = defineEmits(['select', 'selectIdentifier']);

search.value = props.stop?.name || '';

function fetchSuggestions() {
    if (search.value.length < 3) {
        suggestions.value = [];
        return;
    }
    loading.value = true;

    api.geocode
        .geocode({
            query: search.value,
            latitude: props.latitude,
            longitude: props.longitude,
        })
        .then((response) => {
            if (!response.data) {
                suggestions.value = [];
                return;
            }
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            suggestions.value = response.data.map((item: any) => ({
                label: item.name,
                value: item,
                subLabel: getArea(item.areas || []),
            }));
        })
        .catch((error) => {
            console.error('Error fetching suggestions:', error);
        })
        .finally(() => {
            loading.value = false;
        });
}
const modelChange = debounce(() => fetchSuggestions(), 300);

function getArea(areas: Array<Area>) {
    if (areas.length === 0) {
        return '';
    }

    const defaultArea: undefined | Area = areas.find(
        (area: Area) => area.default,
    );
    const country: undefined | Area = areas.find(
        (area: Area) => area.adminLevel === 2,
    );

    if (defaultArea) {
        return country
            ? `${defaultArea.name}, ${country.name}`
            : defaultArea.name;
    }
}

function getIdentifier(element: Suggestion | undefined) {
    if (element?.value && typeof element.value === 'string') {
        return element.value;
    }
    if (element?.value && typeof element.value === 'object') {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        return (element.value as any).identifier;
    }

    return undefined;
}

function submitTypeahead(element: Suggestion) {
    if (element === undefined) {
        element = suggestions.value[0];
    }
    const identifier = getIdentifier(element);

    if (element?.label) {
        search.value = element.label;
    }

    if (identifier) {
        emit('selectIdentifier', identifier);
    }

    if (element?.value && typeof element.value === 'object') {
        emit('select', element.value as MotisGeocodeResponseEntry);
    }
}

watch(
    () => props.stop,
    (newValue) => {
        search.value = newValue?.name || '';
    },
);
</script>

<template>
    <Typeahead
        v-model="search"
        :placeholder="placeholder || t('transitous_search.search_placeholder')"
        class="input input-bordered w-full"
        name="departure-search"
        :required="false"
        :suggestions="suggestions"
        :loading="loading"
        @submit="submitTypeahead($event)"
        @select="submitTypeahead($event)"
        @focus="modelChange()"
        @update:model-value="modelChange()"
    />
</template>
