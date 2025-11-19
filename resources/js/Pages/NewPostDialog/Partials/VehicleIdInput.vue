<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    modelValue: string[];
}>();

const emit = defineEmits(['update:modelValue']);

const localVehicleIds = ref([...props.modelValue]);

if (localVehicleIds.value.length === 0) {
    localVehicleIds.value.push('');
}

watch(
    () => props.modelValue,
    (newVal) => {
        if (JSON.stringify(newVal) !== JSON.stringify(localVehicleIds.value)) {
            localVehicleIds.value = [...newVal];
            if (localVehicleIds.value.length === 0) {
                localVehicleIds.value.push('');
            }
        }
    },
    { deep: true },
);

function updateValue() {
    emit('update:modelValue', localVehicleIds.value);
}

function handleInput(index: number, event: Event) {
    const input = event.target as HTMLInputElement;
    const value = input.value;

    if (value.includes('+') || value.includes(',')) {
        const parts = value
            .split(/[+,]/)
            .map((s) => s.trim())
            .filter((s) => s.length > 0);

        // Replace the current index with the parts
        if (parts.length > 0) {
            localVehicleIds.value.splice(index, 1, ...parts);
        } else {
            localVehicleIds.value.splice(index, 1); // Remove if empty? Or keep empty string?
            // If user types just separators, clear the field?
            // Let's assume user meant to clear it or split it.
            // But if I remove it, and it was the only one...
        }

        if (localVehicleIds.value.length === 0) {
            localVehicleIds.value.push('');
        }

        updateValue();
    } else {
        localVehicleIds.value[index] = value;
        updateValue();
    }
}

function addField() {
    localVehicleIds.value.push('');
    updateValue();
}

function removeField(index: number) {
    localVehicleIds.value.splice(index, 1);
    if (localVehicleIds.value.length === 0) {
        localVehicleIds.value.push('');
    }
    updateValue();
}
</script>

<template>
    <div class="form-control w-full pb-2">
        <label class="label px-0">
            <span class="label-text font-bold">{{
                t('posts.meta_info.vehicle_id')
            }}</span>
        </label>
        <div class="space-y-2">
            <div
                v-for="(id, index) in localVehicleIds"
                :key="index"
                class="flex gap-2"
            >
                <input
                    type="text"
                    :value="id"
                    class="input input-bordered w-full"
                    :placeholder="t('posts.meta_info.vehicle_id_placeholder')"
                    @input="handleInput(index, $event)"
                />
                <button
                    v-if="localVehicleIds.length > 1"
                    class="btn btn-square btn-outline btn-error"
                    @click.prevent="removeField(index)"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 12H4"
                        />
                    </svg>
                </button>
                <button
                    v-if="index === localVehicleIds.length - 1"
                    class="btn btn-square btn-outline btn-primary"
                    @click.prevent="addField"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
