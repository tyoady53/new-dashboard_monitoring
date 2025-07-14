<template>
    <LoadingComponent v-if="isLoading" class="col-12 text-center" />
    <div v-else>
        <h5 class="text-lg font-semibold mb-2 text-center">{{ title }}</h5>
        <div class="card shadow p-3 chart">
          <div style="height: 99%; overflow-y: auto;">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Lab No</th>
                    <th>Patient Name</th>
                    <th>Test</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(patient, i) in datas" :key="i">
                    <td>{{ patient.lab_no }}</td>
                    <td>{{ patient.patient_name }}</td>
                    <td>{{ patient.test_name }}</td>
                  </tr>
                </tbody>
              </table>
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
  auth: Object,
  link: String,
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
        currency: 'IDR',
        minimumFractionDigits: 0
    });
}

const title = ref({});
const datas = ref({});

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
