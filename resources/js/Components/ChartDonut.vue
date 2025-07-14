<template>
    <LoadingComponent v-if="isLoading" class="col-12 text-center" />
    <div v-else>
        <h5 class="text-lg font-semibold mb-2 text-center">{{ title }}</h5>
        <div class="card shadow-sm p-4 text-center chart">
          <apexchart
            type="donut"
            height="250"
            :options="chartOptions"
            :series="datas"
          />
          <div v-if="total" class="text-sm text-gray-500">
            Total: <strong>{{ total }}</strong>
          </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed, reactive, onBeforeUnmount } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import LoadingComponent from './LoadingComponent.vue';

const props = defineProps({
  series: Array,
  categories: Array,
  auth: Object,
  link: String,
});

const isLoading = ref(false);
const timeCount = ref(0);
const title = ref({});
const labels = ref([]);
const datas = ref([]);
const total = ref(0);

onMounted(() => {
    get_monitoring_data();
});

const get_monitoring_data = () => {
  isLoading.value = true;
  timeCount.value = 0;
  axios.get(`/api/dashboard/${props.link}/`,{
    params: {
        cust_id: props.auth.customer_id,
        cust_branch: props.auth.customer_branch
    }
  })
    .then(res => {
        const data = res.data;
        title.value = data.title;
        labels.value = data.labels;
        datas.value = data.data;
        total.value = data.total;
        console.log('Labels:', typeof data.labels, data.labels);

      isLoading.value = false;
    })
    .catch(() => {
      isLoading.value = false;
      Swal.fire({
        icon: 'error',
        title: 'Fetch Failed',
        text: 'Unable to get data.',
        timer: 2000
      });
    });
};

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

const colors = computed(() => generateContrastingRgbColors(datas.length));

const chartOptions = computed(() => ({
  chart: {
    type: 'donut'
  },
  labels: labels.value,
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
