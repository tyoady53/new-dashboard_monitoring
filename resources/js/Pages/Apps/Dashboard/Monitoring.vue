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

        <div class="card border-0 rounded-3 shadow-border-top-purple p-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="fw-bold">Customer <span
                                style="color:red;">*</span></label>
                        <select v-model="form.customer_id
                            " class="form-select" @change="getBranch" :disabled="selected_cust != null">
                            <option disabled value>
                                Choose One
                            </option>
                            <option v-for="customer in master_customers" :key="customer"
                                :value="customer.id">
                                {{
                                    customer.customer_name
                                }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="fw-bold">Branch <span style="color:red;">*</span></label>
                        <select v-model="form.branch_id" class="form-select" @change="changeBranch">
                            <option disabled value>
                                Choose One
                            </option>
                            <option v-for="branch in filteredChain" :key="branch.id"
                                :value="branch.id">
                                {{
                                    branch.branch_name
                                }}
                            </option>
                        </select>
                    </div>
                </div>
                <!-- <div class="col-md-4" v-if="form.customer_id && form.branch_id">
                    <label class="fw-bold">Search Data</label>
                    <button button type="submit" class="btn btn-md btn-primary border-0 shadow w-100" @click="changeBranch">
                        <i class="fa fa-filter"></i> Search
                    </button>
                </div> -->
            </div>
        </div>

        <div class="card border-0 rounded-3 shadow-border-top-purple mt-4" v-if="form.customer_id && form.branch_id">
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
                        <ChartBarGroupHorizontal :cust="form.customer_id" :branch="form.branch_id" link="get_stat_test_group"/>
                    </div>
                    <div class="col-4">
                        <ChartDonut :cust="form.customer_id" :branch="form.branch_id" link="get_stat_nilai_kritis" />
                    </div>
                    <div class="col-4">
                        <ChartDonut :cust="form.customer_id" :branch="form.branch_id" link="get_stat_asal_pasien" />
                    </div>
                    <div class="col-4">
                        <ChartLine :cust="form.customer_id" :branch="form.branch_id" link="get_kunj_perjam" />
                    </div>
                    <div class="col-4">
                        <PatientTable :cust="form.customer_id" :branch="form.branch_id" link="get_nilai_ktitis"/>
                    </div>
                    <div class="col-4">
                        <ChartBarGroup :cust="form.customer_id" :branch="form.branch_id" link="get_monitoring_tat" />
                    </div>
                </div>

                <StatBox :cust="form.customer_id" :branch="form.branch_id"/>
                <!-- <div class="row">
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
                </div> -->
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
import ChartBarGroupHorizontal from '../../../Components/ChartBarGroupHorizontal.vue';

export default {
  layout: LayoutApp,

  components: {
    Head,
    ChartBarGroup,
    ChartDonut,
    ChartLine,
    PatientTable,
    StatBox,
    ChartBarGroupHorizontal
  },

  props: {
    auth: Object,
    permissions: Array,
    selected_cust: Number,
    selected_branch: Number,
    master_customers: Array,
    master_customer_branches: Array,
  },

  computed: {
    filteredChain() {
        // this.form.branch_id.html += 'required';
        let filteredsubChains = [];
        for (let i = 0; i < this.master_customer_branches.length; i++) {
            let structures = this.master_customer_branches[i];
            if (structures.customer_id == this.form.customer_id) {
                filteredsubChains.push(structures);
            }
        }
        return filteredsubChains;
    },
  },

  mounted() {
    if(this.selected_cust) {
        this.form.customer_id = this.selected_cust;
    }
  },

  methods: {
    getBranch() {
        this.form.branch_id = null;
        if (!this.form.customer_id) {
            this.form.customer_id = -1;
        }
    },

    changeBranch() {
        const temp =  this.form.branch_id;
        this.form.branch_id = null;
        setTimeout(() => {
            this.form.branch_id = temp;
        }, 100);
    },
  },

  setup(props) {
    const table_data = ref([]);
    const last_update = ref('');
    const time = ref('');
    const timeCount = ref(0);
    const refreshRate = ref(0);
    const isLoading = ref(false);
    const form = reactive({
        customer_id : '',
        branch_id : ''
    })

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

    return {
      table_data,
      last_update,
      time,
      isLoading,
      formatCompat,
      form,
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
