<template>
  <div class="card shadow-sm p-4 text-center">
    <h5 class="text-lg font-semibold mb-4">{{ title }}</h5>
    <apexchart
      type="donut"
      height="300"
      :options="chartOptions"
      :series="series"
    />
    <div v-if="total" class="mt-4 text-sm text-gray-500">
      Total: <strong>{{ total }}</strong>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  labels: Array,
  series: Array,
  total: [Number, String]
});

const chartOptions = computed(() => ({
  chart: {
    type: 'donut'
  },
  labels: props.labels,
  dataLabels: {
    enabled: true
  },
  legend: {
    position: 'bottom'
  },
  colors: ['#ff7675', '#1dd1a1', '#54a0ff'] // optional: adjust for 2-3 values
}));
</script>
