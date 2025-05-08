<template>
    <div :class="{ 'mb-4': show }">
        <div
            v-if="$page.props?.flash?.success && show"
            role="alert"
            class="alert alert-success"
        >
            <CheckCircle class="h-6 w-6 shrink-0 stroke-current" />
            <span>
                {{ $page.props?.flash?.success }}
            </span>
            <div>
                <button
                    type="button"
                    class="btn btn-ghost btn-sm btn-circle"
                    @click="show = false"
                >
                    <XMark class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <div
            v-if="
                ($page.props?.flash?.error ||
                    Object.keys($page.props?.errors).length > 0) &&
                show
            "
            role="alert"
            class="alert alert-error"
        >
            <XCircle class="h-6 w-6 shrink-0 stroke-current" />
            <div
                v-if="$page.props?.flash?.error"
                class="py-4 text-sm font-medium text-white"
            >
                {{ $page.props.flash.error }}
            </div>
            <div v-else class="py-4 text-sm font-medium text-white">
                <span v-if="Object.keys($page.props?.errors).length === 1">
                    There is a form error.
                </span>
                <span v-else>
                    There are {{ Object.keys($page.props?.errors).length }} form
                    errors.
                </span>
            </div>
            <div>
                <button
                    type="button"
                    class="btn btn-ghost btn-sm btn-circle"
                    @click="show = false"
                >
                    <XMark class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import CheckCircle from '@/Icons/CheckCircle.vue';
import XCircle from '@/Icons/XCircle.vue';
import XMark from '@/Icons/XMark.vue';

export default {
    components: { XCircle, XMark, CheckCircle },
    data() {
        return {
            show: false,
        };
    },
    watch: {
        '$page.props?.flash': {
            handler() {
                this.show = true;
            },
            deep: true,
        },
    },
};
</script>
