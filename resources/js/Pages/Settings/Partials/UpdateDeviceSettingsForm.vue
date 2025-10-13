<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

defineProps<{
    status?: string;
}>();

const trackLocation = ref(null as boolean | null);

function getTrackLocation() {
    trackLocation.value = document.cookie.includes('rtb_allow_history');
}

function setTrackLocation(track: boolean) {
    const maxAge = 60 * 60 * 24 * 365; // 1 year

    if (track) {
        document.cookie = 'rtb_allow_history=true; path=/; max-age=' + maxAge;
        document.cookie = 'rtb_disallow_history=; path=/; max-age=0';
    } else {
        document.cookie =
            'rtb_disallow_history=true; path=/; max-age=' + maxAge;
        document.cookie = 'rtb_allow_history=; path=/; max-age=0';
    }
}

onMounted(() => {
    getTrackLocation();
});

watch(trackLocation, (newValue) => {
    if (newValue !== null) {
        setTrackLocation(newValue);
    }
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Device Settings</h2>

            <p class="mt-1 text-sm opacity-65">
                These settings are per-device and will be different on each of
                your devices.
            </p>
        </header>

        <form class="mt-6 space-y-6">
            <div>
                <label>
                    <input
                        v-model="trackLocation"
                        name="trackHistory"
                        type="checkbox"
                        class="toggle"
                    />
                    Enable location history tracking
                </label>
            </div>
        </form>
    </section>
</template>
