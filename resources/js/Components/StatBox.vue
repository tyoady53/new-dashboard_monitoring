
<template>
    <div class="row">
        <LoadingComponent v-if="isLoading" class="col-12 text-center" />
        <div class="col-2" v-for="data in datas" v-else>
            <div class="stat-box">
                <div class="stat-value">{{ formatNumber(data.value) }}</div>
              <div class="stat-label">{{ data.label }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, reactive, onBeforeUnmount } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import LoadingComponent from './LoadingComponent.vue';

const props = defineProps({
  label: String,
  value: Number,
  cust: Number,
  branch: Number,
});

const isLoading = ref(false);
const timeCount = ref(0);
const table_data = ref({});
const last_update = ref(null);

onMounted(() => {
    get_monitoring_data();
});

function formatNumber(num) {
    return Number(num).toLocaleString('id-ID', {
        // style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });
}

const labels = ref({});
const datas = ref({});

const get_monitoring_data = () => {
  isLoading.value = true;
  timeCount.value = 0;
  axios.get('/api/dashboard/get_statbox/',{
    params: {
        cust_id: props.cust,
        cust_branch: props.branch
    }
  })
    .then(res => {
        const data = res.data;
        labels.value = data.labels;
        datas.value = data.data;

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
</script>


<style scoped>
.stat-box {
  border-radius: 10px;
  padding: 10px;
  background: #f3f4f6;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.stat-label {
  font-size: 12px;
  color: #6b7280;
}
.stat-value {
  font-size: 20px;
  font-weight: bold;
  color: #111827;
}
</style>
