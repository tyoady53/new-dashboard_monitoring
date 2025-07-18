<template>
  <div class="card shadow-sm p-4">
    <h5 class="text-lg font-semibold mb-2">{{ title }}</h5>
    <apexchart
      type="bar"
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
  categories: Array,
});

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    stacked: false,
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '90%', // optional, better for horizontal layout
    }
  },
  dataLabels: {
    enabled: true,
    offsetX: 20, // ✅ slight spacing from the bar end
    style: {
      fontSize: '12px',
      colors: ['#000']
    },
    dropShadow: {
        enabled: true,
        top: 0,
        left: 0,
        blur: 2,
        color: '#fff',     // outline color
        opacity: 3
    }
  },
  xaxis: {
    categories: props.categories, // ✅ move categories to y-axis
  },
  legend: {
    position: 'top',
  },
  colors: ['#f39c12', '#3498db']
}));
</script>
