<template>
  <VueApexCharts type="pie" :options="apexOptions" :series="chartData.series" height="320" />
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({ chartData: { type: Object, required: true } });

// Track the host's dark-mode class on <html> (self-contained; no host composable).
const isDark = ref(document.documentElement.classList.contains('dark'));
let observer = null;

onMounted(() => {
    observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => { observer?.disconnect(); });

const apexOptions = computed(() => ({
    ...(props.chartData.options || {}),
    chart: { type: 'pie', background: 'transparent', ...(props.chartData.options?.chart || {}) },
    theme: { mode: isDark.value ? 'dark' : 'light' },
    labels: props.chartData.options?.labels || [],
    legend: {
        labels: { colors: isDark.value ? '#e5e7eb' : '#374151' },
        ...(props.chartData.options?.legend || {}),
    },
}));
</script>
