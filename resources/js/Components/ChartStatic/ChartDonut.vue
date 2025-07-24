<template>
    <h5 class="text-lg font-semibold mb-2 text-center">{{ title }}</h5>
    <div class="card shadow-sm p-4 text-center chart">
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

function hsvToRgb(h, s, v) {
  let f = (n, k = (n + h / 60) % 6) =>
    v - v * s * Math.max(Math.min(k, 4 - k, 1), 0);
  const r = Math.round(f(5) * 255);
  const g = Math.round(f(3) * 255);
  const b = Math.round(f(1) * 255);
  return `rgb(${r}, ${g}, ${b})`;
}

function generateContrastingRgbColors(n) {
  const colors = [];
  for (let i = 0; i < n; i++) {
    const hue = i * 53;  // evenly spaced hues
    colors.push(hsvToRgb(hue, 0.65, 0.9)); // vivid RGB color
  }
  return colors;
}

const colors = computed(() => generateContrastingRgbColors(props.series.length));

const chartOptions = computed(() => ({
  chart: {
    type: 'donut'
  },
  labels: props.labels,
  dataLabels: {
    enabled: true,
    formatter: function (val, opts) {
        const series = opts?.w?.config?.series;
        const index = opts?.seriesIndex;

        if (Array.isArray(series) && typeof index === 'number') {
            return series[index];
        }

        return ''; // fallback if anything is wrong
    },
    style: {
      fontSize: '14px'
    }
  },
  legend: {
    position: 'bottom'
  },
  plotOptions: {
    pie: {
      donut: {
        size: '50%'
      }
    }
  },
  colors: colors.value // optional: adjust for 2-3 values
}));
</script>
