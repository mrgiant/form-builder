<template>
  <div class="grid grid-cols-12 gap-5">
    <div
      v-for="(chart, index) in charts"
      :key="index"
      class="col-span-12 md:col-span-6 relative overflow-hidden bg-white border border-gray-200 rounded-lg shadow-xs dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-center flex-1 px-4 py-3 mb-3 space-x-4">
        <h5>
          <span class="text-gray-500">
            <i class="fa-solid fa-chart-pie mr-1"></i>
            {{ chart.question_label }}
          </span>
        </h5>
      </div>
      <pie-chart :chart-data="toChartData(chart)" />
    </div>

    <div v-if="charts.length === 0" class="col-span-12 text-center py-10 text-gray-400 dark:text-gray-500">
      No chartable responses yet.
    </div>
  </div>
</template>

<script>
import PieChart from '@/components/Charts/Pie.vue';

export default {
  components: { PieChart },

  props: ['data'],

  data() {
    return {
      charts: [],
    };
  },

  mounted() {
    this.setvalue();
  },

  methods: {
    setvalue() {
      this.charts = JSON.parse(this.data);
    },

    toChartData(chart) {
      return {
        series: chart.data || [],
        options: {
          labels: chart.labels || [],
          legend: { position: 'bottom' },
        },
      };
    },
  },
};
</script>
