<script setup lang="ts">
import Typeahead, { Suggestion } from '@/Components/Typeahead.vue';
import { LocationIdentifier } from '@/types';
import { AutocompleteResponse } from '@/types/motis';
import axios from 'axios';
import { ref } from 'vue';
import { debounce } from 'vue-debounce';
import { useI18n } from 'vue-i18n';

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
    locationName: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: null,
    },
});

const search = ref('');
const suggestions = ref<Suggestion[]>([]);

const emit = defineEmits(['select', 'selectIdentifier']);

search.value = props.locationName || '';

function fetchSuggestions() {
    if (search.value.length < 3) {
        suggestions.value = [];
        return;
    }

    const url = route('posts.create.geocode');
    axios
        .get(url, {
            params: {
                query: search.value,
                provider: 'airport',
                latitude: props.latitude,
                longitude: props.longitude,
            },
        })
        .then((response) => {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            suggestions.value = response.data.map((item: any) => ({
                label: item.name,
                value: item,
                subLabel: getAirportIdentifier(item.identifiers),
            }));
        })
        .catch((error) => {
            console.error('Error fetching suggestions:', error);
        });
}
const modelChange = debounce(() => fetchSuggestions(), 300);

function getAirportIdentifier(areas: Array<LocationIdentifier>): null | string {
    // find icao and iata codes
    const icao = areas.find((a) => a.type === 'icao');
    const iata = areas.find((a) => a.type === 'iata');

    if (icao && iata) {
        return `${iata.identifier} (${icao.identifier})`;
    }
    if (icao) {
        return icao.identifier;
    }
    if (iata) {
        return iata.identifier;
    }

    const gps = areas.find((a) => a.type === 'gps');
    if (gps) {
        return `GPS: ${gps.identifier}`;
    }

    const local = areas.find((a) => a.type === 'local');
    if (local) {
        return `Local: ${local.identifier}`;
    }

    return null;
}

function getIdentifier(element: Suggestion | undefined): string | undefined {
    if (element?.value && typeof element.value === 'string') {
        return element.value;
    }
    if (element?.value && typeof element.value === 'object') {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        return (element.value as any).id;
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
        emit('select', element.value as AutocompleteResponse);
    }
}
</script>

<template>
    <Typeahead
        v-model="search"
        class="input input-bordered w-full"
        name="departure-search"
        :required="false"
        :placeholder="placeholder || t('new_route.airport_search.placeholder')"
        :suggestions="suggestions"
        @submit="submitTypeahead($event)"
        @select="submitTypeahead($event)"
        @focus="modelChange()"
        @update:model-value="modelChange()"
    />
</template>
