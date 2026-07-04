<template>
    <span v-if="status && status !== 'not_required'"
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium" :class="badgeClass">
        <i :class="icon"></i> {{ label }}
    </span>
    <span v-else class="text-xs text-gray-400">—</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    field: { type: String, default: '' },
    route_url: { type: String, default: '' },
    row: { type: [Array, Object], default: () => ({}) },
    xprops: { type: Object, default: () => ({}) },
    tdProps: { type: Object, default: () => ({}) },
});

const status = computed(() => props.row?.approval_status);

const label = computed(() => ({
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Rejected',
    terminated: 'Terminated',
}[status.value] || status.value));

const badgeClass = computed(() => ({
    pending: 'bg-blue-100 text-blue-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    terminated: 'bg-rose-100 text-rose-700',
}[status.value] || 'bg-gray-100 text-gray-600'));

const icon = computed(() => ({
    pending: 'fa-solid fa-hourglass-half',
    approved: 'fa-solid fa-circle-check',
    rejected: 'fa-solid fa-circle-xmark',
    terminated: 'fa-solid fa-circle-stop',
}[status.value] || 'fa-solid fa-minus'));
</script>
