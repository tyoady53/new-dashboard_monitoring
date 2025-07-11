<template>
  <Head>
    <title>Patient Monitoring - Wynacom Information System</title>
  </Head>
  <main class="c-main">
    <div class="container-fluid" v-if="hasAnyPermission(['dashboard.index'])">
      <div class="fade-in">
        <div class="text-center">
          <h4>Patient Monitoring {{ table_data.cust_branch }}</h4>
        </div>

        <div class="card border-0 rounded-3 shadow-border-top-purple mt-4">
          <div class="card-body">
            <div class="text-center">
              Last Update : {{ formatCompat(last_update) }} <br />
              Time : {{ time }}
            </div>

            <template v-if="isLoading">
              <div class="page-loader">
                <div class="loading-spinner">
                  <div class="spinner-border"></div>
                </div>
                <h3>Loading Data</h3>
              </div>
            </template>

            <template v-else>
              <div class="dashboard grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <div class="row">
                    <div class="col-4">
                        <ChartBarGroup title="STATISTIK TEST GROUP" :series="testGroupSeries" :categories="testGroupCategories" />
                    </div>
                    <div class="col-4">
                        <ChartDonut title="STATISTIK NILAI KRITIS" :labels="['BELUM LAPOR', 'SUDAH LAPOR']" :series="[41, 267]" total="308" />
                    </div>
                    <div class="col-4">
                        <ChartDonut title="STATISTIK ASAL PASIEN" :labels="['RAWAT JALAN', 'IGD', 'RAWAT INAP']" :series="[175, 108, 41]" total="363" />
                    </div>
                    <div class="col-4">
                        <ChartLine title="STATISTIK KUNJUNGAN PERJAM" :series="visitHourlySeries" :categories="visitHourlyCategories" />
                    </div>
                    <div class="col-4">
                        <PatientTable title="PASIEN NILAI KRITIS" :patients="criticalPatients" />
                    </div>
                    <div class="col-4">
                        <ChartBarGroup title="MONITORING TAT" :series="tatSeries" :categories="['RUTIN', 'CITO']" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-2">
                        <StatBox label="KUNJUNGAN" value="363" />
                    </div>
                    <div class="col-2">
                        <StatBox label="SAMPEL BELUM AMBIL" value="173" />
                    </div>
                    <div class="col-2">
                        <StatBox label="SAMPEL TERIMA" value="451" />
                    </div>
                    <div class="col-2">
                        <StatBox label="PEMERIKSAAN BELUM SELESAI" value="5,134" />
                    </div>
                    <div class="col-2">
                        <StatBox label="PEMERIKSAAN SELESAI" value="4,345" />
                    </div>
                    <div class="col-2">
                        <StatBox label="TOTAL PEMERIKSAAN" value="9,479" class="col-span-5 text-lg font-bold" />
                    </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { onMounted, ref, reactive, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

import LayoutApp from '../../../Layouts/App.vue';
import ChartBarGroup from '../../../Components/ChartBarGroup.vue';
import ChartDonut from '../../../Components/ChartDonut.vue';
import ChartLine from '../../../Components/ChartLine.vue';
import PatientTable from '../../../Components/PatientTable.vue';
import StatBox from '../../../Components/StatBox.vue';

export default {
  layout: LayoutApp,

  components: {
    Head,
    ChartBarGroup,
    ChartDonut,
    ChartLine,
    PatientTable,
    StatBox,
  },

  props: {
    auth: Object,
    permissions: Array,
  },

  setup(props) {
    const table_data = ref([]);
    const last_update = ref('');
    const time = ref('');
    const timeCount = ref(0);
    const refreshRate = ref(0);
    const isLoading = ref(false);

    const interval = setInterval(() => {
      checkTime();
      time.value = new Date().toLocaleTimeString([], { hour12: false });
    }, 1000);

    onBeforeUnmount(() => clearInterval(interval));

    const formatCompat = (dates) => {
      if (!dates) return '-';
      const d = new Date(dates);
      return `${d.getDate().toString().padStart(2, '0')}-${d.toLocaleString('default', { month: 'short' })}-${d.getFullYear()}/${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
    };

    const get_monitoring_data = () => {
      isLoading.value = true;
      timeCount.value = 0;

      axios.get(`/api/dashboard/get_data/${props.auth.user.email}`)
        .then(res => {
          table_data.value = res.data.data;
          last_update.value = res.data.data.last_update;
          isLoading.value = false;
        })
        .catch(() => {
          isLoading.value = false;
          Swal.fire({ icon: 'error', title: 'Fetch Failed', text: 'Unable to get data.', timer: 2000 });
        });
    };

    const checkTime = () => {
      if (refreshRate.value > 0) {
        if (Math.floor(Date.now() / 1000) % 60 === 0) timeCount.value++;
        if (timeCount.value === refreshRate.value) get_monitoring_data();
      }
    };

    onMounted(() => {
    //   get_monitoring_data();
    });

    const testGroupCategories = ['URINALISIS', 'KIMIA KLINIK', 'IMUNOLOGI', 'HEMATOLOGI', 'HDT', 'FAECES', 'FAAL KOAGULASI', 'DRUG MONITORING/NAPZA', 'CAIRAN TUBUH', 'BIOLOGI MOLEKULER / LITBANG', 'ANALISA GAS DARAH'];
    const testGroupSeries = [
      { name: 'BELUM SELESAI', data: [7, 19, 19, 87, 1, 22, 1, 1, 1, 3, 8] },
      { name: 'SELESAI', data: [21, 60, 60, 148, 8, 37, 0, 0, 0, 2, 0] }
    ];

    const tatSeries = [
      { name: 'BELUM SELESAI', data: [203, 72] },
      { name: 'SELESAI', data: [47, 41] }
    ];

    const visitHourlyCategories = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09'];
    const visitHourlySeries = [
      { name: 'Total Pasien', data: [16, 18, 6, 9, 8, 3, 39, 79, 106, 79] }
    ];

    const criticalPatients = [
      { lab_no: '2502040721', patient_name: 'HENDRA WIDJAJA', test_name: 'Hemoglobin (Hema)' },
      { lab_no: '2502040004', patient_name: 'FAJAR HAFIDZ RAMADAN', test_name: 'PO2 (Analisa Gas Darah)' },
      { lab_no: '2502040500', patient_name: 'KASANAH', test_name: 'Hemoglobin (Hema)' },
      { lab_no: '2502040398', patient_name: 'ZAIRA EKA ADIYANTI', test_name: 'Hemoglobin (Hema)' },
      { lab_no: '2502040649', patient_name: 'WIWIK SITI SUNDARI', test_name: 'Trombosit (Hema)' },
      { lab_no: '2502040079', patient_name: 'EKA YUNI LISTYARINI', test_name: 'Kalium (K)' },
      { lab_no: '2502040046', patient_name: 'BUKASAN BUNGKO', test_name: 'BUN' },
      { lab_no: '2502040084', patient_name: 'SENEN', test_name: 'Klorida (Cl)' },
      { lab_no: '2502040702', patient_name: 'MUHAMMAD IQBAL AZZAHIR', test_name: 'Kalium (K)' }
    ];

    return {
      table_data,
      last_update,
      time,
      isLoading,
      formatCompat,
      testGroupCategories,
      testGroupSeries,
      tatSeries,
      visitHourlyCategories,
      visitHourlySeries,
      criticalPatients,
    };
  },
};
</script>

<style>
h4{
    margin-bottom: 0;
}

h3{
    margin-bottom: 0;
}

/* .bgred {
    background: #ff0000;
    background-color: #ff0000;
}

.bgyellow {
    background: #ffff00;
}

.bggreen {
    background: #00ff73;
}

.bgblue {
    background: #00b0f0;
} */

.regno_font {
    font-size: 32px;
    margin-bottom: 0;
}

.myTicket:hover {
    background-color: blue;
    color: white;
}

.count {
    position: relative;
    top: -30px;
    color: white;
    font-size: 20px;
}

.count2 {
    position: relative;
    top: -20px;
    padding: 10px 50px 10px 50px;
    border-radius: 10%;
    color: white;
    background-color: black;
    font-size: 40px;
}

.myTicket:hover .count {
    background-color: white;
    color: black;
}

.myTicket:hover .count2 {
    background-color: blue;
    color: white;
}
</style>
