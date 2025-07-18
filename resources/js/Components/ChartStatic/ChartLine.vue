<template>
  <div class="card shadow-sm p-4">
    <h5 class="text-lg font-semibold mb-4 text-center">{{ title }}</h5>
    <apexchart
      type="line"
      height="300"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  series: Array,
  categories: Array
});

const chartOptions = computed(() => ({
  chart: {
    type: 'line',
    zoom: {
      enabled: false
    }
  },
  dataLabels: {
    enabled: true
  },
  stroke: {
    curve: 'smooth'
  },
  xaxis: {
    categories: props.categories
  },
  colors: ['#10b981'], // tailwind green
  markers: {
    size: 4
  },
  legend: {
    position: 'top'
  }
}));
</script>
