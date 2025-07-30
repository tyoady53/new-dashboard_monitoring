<template>
  <Head>
    <title>Patient Monitoring - Wynacom Information System</title>
  </Head>
  <main class="c-main">
    <div class="container-fluid" v-if="hasAnyPermission(['dashboard.index'])">
      <div class="fade-in">
        <div class="text-center mb-2">
          <h4>Patient Monitoring {{ table_data.cust_branch }}</h4>
        </div>

        <div class="card border-0 rounded-3 shadow-border-top-purple p-3">
            <template v-if="selected_cust &&selected_branch">
                <h3>{{ generateCustomerText(selected_cust,selected_branch) }}</h3>
            </template>
            <template v-else>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="fw-bold">Customer <span style="color:red;">*</span></label>
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
                            <select v-model="form.branch_id" class="form-select" @change="changeBranch" :disabled="selected_branch != null">
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
                </div>
            </template>
        </div>

        <div class="card border-0 rounded-3 shadow-border-top-purple mt-4" v-if="form.customer_id && form.branch_id">
          <div class="card-body">
            <div class="text-center">
              Last Update : {{ last_update }} <br />
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
                    <template v-for="dashboard in dashboards.details">
                        <template v-if="dashboard.chart_show == 'StatBox'">
                            <div class="col-12">
                                <component :is="dashboard.chart_show" :cust="form.customer_id" :branch="form.branch_id" link="get_stat_box"/>
                            </div>
                        </template>
                        <template v-else>
                            <div :class="`col-${12/dashboards.column_count}`">
                                <component :is="dashboard.chart_show" :cust="form.customer_id" :branch="form.branch_id" :link="dashboard.data_from"/>
                            </div>
                        </template>
                    </template>
                    <!-- {{ dashboards }} -->
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- The Modal Refresh Rate -->
    <div class="modal" id="intervalModal" ref="intervalModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Set Refresh Interval</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <form @submit.prevent="submitInterval">
                        <div class="mb-3">
                            <label class="fw-bold">Refresh Interval ( In Minutes)</label>
                            <input class="form-control" v-model="refreshRate" type="number" min="1" />
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary shadow-sm rounded-sm" type="submit" data-bs-dismiss="modal">
                                Save
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Modal footer -->
            </div>
        </div>
    </div>
    <!-- End of Modal Refresh Rate -->

  </main>
</template>

<script>
import { onMounted, ref, reactive, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import { Inertia } from '@inertiajs/inertia';
import LayoutApp from '../../../Layouts/App.vue';
import ChartBarGroup from '../../../Components/ChartBarGroup.vue';
import ChartDonut from '../../../Components/ChartDonut.vue';
import ChartLine from '../../../Components/ChartLine.vue';
import PatientTable from '../../../Components/PatientTable.vue';
import StatBox from '../../../Components/StatBox.vue';
import ChartBarGroupHorizontal from '../../../Components/ChartBarGroupHorizontal.vue';
import Helper from '../../../Helper/Helper';
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
    data: () => ({
        timeCount: 0,
        data_interval: "",
        refreshRate: 0,
    }),

    computed: {
        filteredChain() {
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

    created() {
        if(this.selected_cust) {
            this.form.customer_id = this.selected_cust;
        }
        if(this.selected_branch) {
            this.form.branch_id = this.selected_branch;
        }
        this.refreshRate =  this.$page.props.auth.user.interval;
        this.get_latest_update();
        this.get_monitoring_data();
        this.data_interval = setInterval(() => {
            this.checkTime();
            this.time = Intl.DateTimeFormat(navigator.language, {
                hour: "numeric",
                minute: "numeric",
                second: "numeric",
                hourCycle: "h23", // 24-hour format
            }).format();
        }, 1000);
    },

    methods: {
        checkTime() {
            if (this.refreshRate > 0) {
                if (Math.floor(Date.now() / 1000) % 60 == 0) {
                    this.timeCount += 1;
                }
                if (this.timeCount == this.refreshRate) {
                    this.get_latest_update();
                    this.get_monitoring_data();
                }
            }
        },

        getBranch() {
            this.form.branch_id = null;
            if (!this.form.customer_id) {
                this.form.customer_id = -1;
            }
        },

        generateCustomerText(customerId,branch_id) {
            const customer = this.master_customers.find(item => item.id === customerId);
            const customerName = customer ? customer.customer_name : 'Customer Not found';
            const branch = this.master_customer_branches.find(item => item.id === branch_id);
            const branchName = branch ? branch.branch_name : 'Customer Not found';

            let returnText = customerName + ' - ' + branchName;

            return returnText;
        },

        changeBranch() {
            const temp =  this.form.branch_id;
            this.form.branch_id = null;
            setTimeout(() => {
                this.form.branch_id = temp;
                this.get_latest_update();
                this.get_monitoring_data();
            }, 100);
        },

        get_latest_update() {
            this.last_update = '';
            axios.get(`/api/dashboard/get_last_update/`,{
                params: {
                    cust_id: this.form.customer_id,
                    cust_branch: this.form.branch_id
                }
            })
                .then(res => {
                    const data = res.data;
                    this.last_update = data;
                })
                .catch(() => {
                    if(this.branch_id) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Fetch Failed',
                            text: 'Unable to get data.',
                            timer: 2000
                        });
                    }
            });
        },

        get_monitoring_data() {
            this.dashboards = [];
            this.timeCount = 0;
            axios.get(`/get_dashboard`,{
                params: {
                    cust_id: this.form.customer_id,
                    cust_branch: this.form.branch_id
                }
            })
                .then(res => {
                    const data = res.data.data;
                    this.dashboards = data;
                })
                .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Failed',
                    text: 'Unable to get data.',
                    timer: 2000
                });
            });
        },
    },

    setup(props) {
        const dashboards = ref([]);
        const table_data = ref([]);
        const last_update = ref('');
        const time = ref('');
        const timeCount = ref(0);
        const isLoading = ref(false);
        const form = reactive({
            customer_id : '',
            branch_id : ''
        })

        const formatCompat = (dates) => {
        if (!dates) return '-';
        const d = new Date(dates);
        return `${d.getDate().toString().padStart(2, '0')}-${d.toLocaleString('default', { month: 'short' })}-${d.getFullYear()}/${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
        };

        const refreshRate = ref();
        const submitInterval = () => {
            Helper.setInterval(refreshRate.value);
            Inertia.post('/user/update_interval', {
                refresh_rate: refreshRate.value,
            }, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Success!',
                        text: 'User saved successfully.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    });
                },onError: () => {
                    Swal.fire('Error!', 'Failed to save interval.', 'error');
                }
            });
        }

        return {
            submitInterval,
            refreshRate,
            table_data,
            last_update,
            time,
            isLoading,
            formatCompat,
            form,
            dashboards,
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
