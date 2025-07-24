<template>
    <h5 class="text-lg font-semibold mb-2">{{ title }}</h5>
    <div class="card shadow-sm p-4 chart">
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
      horizontal: false,
      columnWidth: '50%',
    }
  },
  dataLabels: {
    enabled: true
  },
  xaxis: {
    categories: props.categories,
  },
  legend: {
    position: 'top',
  },
  colors: ['#f39c12', '#3498db']
}));
</script>
