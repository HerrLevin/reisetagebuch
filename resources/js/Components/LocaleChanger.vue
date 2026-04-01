<script setup lang="ts">
import { useLocaleSore } from '@/stores/locale';
import { Globe } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const locale = useLocaleSore();
locale.syncLocale();

function getLocaleName(locale: string): string {
    return (
        new Intl.DisplayNames([locale], { type: 'language' }).of(locale) ??
        locale
    );
}

const selectedLocale = ref<string>(locale.locale ?? locale.getFallbackLocale());

function trimSelectedLocale() {
    selectedLocale.value = selectedLocale.value.substring(0, 2);
}

function setLocale() {
    locale.setLocale(selectedLocale.value);
}

onMounted(() => {
    locale.syncLocale();
    trimSelectedLocale();
});
</script>

<template>
    <div class="locale-changer">
        <label class="select select-ghost">
            <Globe class="me-2 inline size-5" />
            <select v-model="selectedLocale" class="grow" @change="setLocale()">
                <option
                    v-for="localeIdentifier in $i18n.availableLocales"
                    :key="`locale-${localeIdentifier}`"
                    :value="localeIdentifier"
                >
                    {{ getLocaleName(localeIdentifier) }}
                </option>
            </select>
        </label>
    </div>
</template>
