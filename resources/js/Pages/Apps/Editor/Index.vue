<template>

    <Head>
        <title>Chart Editor</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid p-0">
            <!-- Sticky Header -->
            <div class="card border-0 rounded-3 shadow-border-top-purple p-3">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="fw-bold">Customer <span style="color:red;">*</span></label>
                                <select v-model="form.customer_id
                                    " class="form-select" @change="getBranch" :disabled="selected_cust != null">
                                    <option disabled value>
                                        Choose One
                                    </option>
                                    <option v-for="customer in master_customers" :key="customer" :value="customer.id">
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
                                    <option v-for="branch in filteredChain" :key="branch.id" :value="branch.id">
                                        {{
                                            branch.branch_name
                                        }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 sticky-top d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <label class="fw-bold">Column in a Row</label>
                                <select v-model.number="columns" class="form-control w-auto">
                                    <option v-for="n in [1, 2, 3, 4, 6]" :key="n" :value="n">{{ n }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-3 mx-auto">
                                        <label class="switch me-2">
                                            <input type="checkbox" v-model="stat_box">
                                            <div class="slider round"></div>
                                        </label>
                                    </div>
                                    <div class="col-9">
                                        <span>Stat Box</span>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-link" @click="toggleSection" type="button">
                                <label>{{ sectionVisible ? 'Hide' : 'Show' }}&nbsp</label>
                                <i :class="sectionVisible ? 'fa fa-chevron-up' : 'fa fa-chevron-down'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Chart Inputs -->
                    <div v-show="sectionVisible" class="p-3" style="background: white;">
                        <div class="mb-2">
                            <button class="btn btn-primary" @click="addChart" type="button">New Chart</button>
                        </div>
                        <div v-for="(chart, index) in charts" :key="chart.sequence">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Chart #{{ index + 1 }}</span>
                                    <button class="btn btn-sm btn-danger" @click="removeChart(index)">×</button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label">Data From</label>
                                        <select v-model="chart.dataFrom" class="form-select">
                                            <option v-for="table in tables" :value="table.table_name">{{
                                                table.description }}</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Chart Type</label>
                                        <select v-model="chart.chartType" class="form-select">
                                            <option v-for="table in getChartOptions(chart)" :value="table">
                                                {{ table }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Sequence Number</label>
                                        <input v-model.number="chart.sequence" class="form-control" type="number"
                                            @input="keyUp(index)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between" v-if="charts.length">
                            <hr>
                            <button class="btn btn-success" type="submit"
                                :disabled="!form.branch_id || !form.customer_id">Save</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="dashboard grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <div class="row">
                    <template v-for="chart in charts">
                        <div :class="`col-${12 / columns}`">
                            <component :is="chart.chartType" v-bind="getChartProps(chart)" />
                        </div>
                    </template>
                    <template v-if="stat_box">
                        <div class="col-12">
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
                                    <StatBox label="TOTAL PEMERIKSAAN" value="9,479"
                                        class="col-span-5 text-lg font-bold" />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { onMounted, ref, reactive, onBeforeUnmount } from 'vue';

import LayoutApp from '../../../Layouts/App.vue';

import { Head, Link } from '@inertiajs/inertia-vue3';

import axios from "axios";

import { Inertia } from '@inertiajs/inertia';

import Swal from 'sweetalert2';

import ChartBarGroup from '../../../Components/ChartStatic/ChartBarGroup.vue';
import ChartDonut from '../../../Components/ChartStatic/ChartDonut.vue';
import ChartLine from '../../../Components/ChartStatic/ChartLine.vue';
import PatientTable from '../../../Components/ChartStatic/PatientTable.vue';
import StatBox from '../../../Components/ChartStatic/StatBox.vue';
import ChartBarGroupHorizontal from '../../../Components/ChartStatic/ChartBarGroupHorizontal.vue';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
        ChartBarGroup,
        ChartDonut,
        ChartLine,
        PatientTable,
        StatBox,
        ChartBarGroupHorizontal
    },

    props: {
        tables: Array,
        selected_cust: Number,
        selected_branch: Number,
        master_customers: Array,
        master_customer_branches: Array,
    },

    data: () => ({
        // columns: 2,
        sectionVisible: false,
        // charts: [],
        charts_data: {
            dash_test_group: {
                categories: ['URINALISIS', 'KIMIA KLINIK', 'IMUNOLOGI', 'HEMATOLOGI', 'HDT', 'FAECES', 'FAAL KOAGULASI', 'DRUG MONITORING/NAPZA', 'CAIRAN TUBUH', 'BIOLOGI MOLEKULER / LITBANG', 'ANALISA GAS DARAH'],
                series: [{ name: 'BELUM SELESAI', data: [7, 19, 19, 87, 1, 22, 1, 1, 1, 3, 8] },
                { name: 'SELESAI', data: [21, 60, 60, 148, 8, 37, 0, 0, 0, 2, 0] }]
            },
            dash_tat: {
                categories: ['RUTIN', 'CITO'],
                series: [{ name: 'BELUM SELESAI', data: [203, 72] },
                { name: 'SELESAI', data: [47, 41] }]
            },
            dash_total_kritis: {
                labels: ['BELUM LAPOR', 'SUDAH LAPOR'],
                series: [41, 267]
            },
            dash_visit_clasification: {
                labels: ['RAWAT JALAN', 'IGD', 'RAWAT INAP'],
                series: [175, 108, 41]
            },
            dash_visit_hour: {
                categories: ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09'],
                series: [{ name: 'Total Pasien', data: [16, 18, 6, 9, 8, 3, 39, 79, 106, 79] }]
            },
            dash_nilai_kritis: {
                patients: [
                    { lab_no: '2502040721', patient_name: 'HENDRA WIDJAJA', test_name: 'Hemoglobin (Hema)' },
                    { lab_no: '2502040004', patient_name: 'FAJAR HAFIDZ RAMADAN', test_name: 'PO2 (Analisa Gas Darah)' },
                    { lab_no: '2502040500', patient_name: 'KASANAH', test_name: 'Hemoglobin (Hema)' },
                    { lab_no: '2502040398', patient_name: 'ZAIRA EKA ADIYANTI', test_name: 'Hemoglobin (Hema)' },
                    { lab_no: '2502040649', patient_name: 'WIWIK SITI SUNDARI', test_name: 'Trombosit (Hema)' },
                    { lab_no: '2502040079', patient_name: 'EKA YUNI LISTYARINI', test_name: 'Kalium (K)' },
                    { lab_no: '2502040046', patient_name: 'BUKASAN BUNGKO', test_name: 'BUN' },
                    { lab_no: '2502040084', patient_name: 'SENEN', test_name: 'Klorida (Cl)' },
                    { lab_no: '2502040702', patient_name: 'MUHAMMAD IQBAL AZZAHIR', test_name: 'Kalium (K)' }
                ]
            },
        }
    }),

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
        if (this.selected_cust) {
            this.form.customer_id = this.selected_cust;
        }
    },

    methods: {
        getChartOptions(chart) {
            const type = chart.dataFrom;

            for (let i = 0; i < this.tables.length; i++) {
                const structures = this.tables[i];

                if (structures.table_name === type) {
                    let cloned = { ...structures };

                    if (typeof cloned.display_type === 'string' && cloned.display_type.includes(',')) {
                        cloned.display_type = cloned.display_type.split(',').map(s => s.trim());
                    } else {
                        return [cloned.display_type.trim()];
                    }

                    // ✅ return directly instead of pushing into array
                    return cloned.display_type;
                }
            }

            // fallback: return empty array if not found
            return [];
        },

        getTableDescription(type) {
            return this.tables?.find(d => d.table_name === type)?.description || type;
        },

        getChartProps(chart) {
            const type = chart.dataFrom;
            const data = this.charts_data[chart.dataFrom] || {};
            const title = this.getTableDescription(type);

            if ('categories' in data && 'series' in data) {
                // console.log('categories');
                return {
                    categories: data.categories,
                    series: data.series,
                    title: title
                };
            }

            // Handle Donut/Pie style
            if ('labels' in data && 'series' in data) {
                return {
                    labels: data.labels,
                    series: data.series,
                    title: title
                };
            }

            if ('patients' in data) {
                return {
                    title: title,
                    patients: data.patients
                };
            }

            // Default: return empty
            return {};
        },

        keyUp(index) {
            if (this.charts[index].sequence) {
                const currentSequence = this.charts[index].sequence;
                var affected = 0;
                // const total = this.charts.length + 1;

                // const duplicate = this.charts.find((d, i) => d.sequence === currentSequence && i !== index);
                const duplicateIndex = this.charts.findIndex(
                    (d, i) => d.sequence === currentSequence && i !== index
                );

                if (duplicateIndex !== -1) {
                    // Conflict detected
                    if (index > duplicateIndex) {
                        // Move backward: Shift up other items to fill the gap
                        for (let i = duplicateIndex; i < index; i++) {
                            this.charts[i].sequence += 1;
                        }
                    } else {
                        // Move forward: Shift down other items
                        for (let i = index + 1; i <= duplicateIndex; i++) {
                            this.charts[i].sequence -= 1;
                        }
                    }
                }
                // alert('Key Up on index ' + index + '; Value : ' + this.charts[index].sequence + '; From INDEX number : '+duplicateIndex + '; Affected index : ' + affected);
                this.charts.sort((a, b) => a.sequence - b.sequence);
                this.charts = [...this.charts];
                // console.log(this.charts)
            }
        },

        getBranch() {
            this.form.branch_id = null;
            if (!this.form.customer_id) {
                this.form.customer_id = -1;
            }
        },

        changeBranch() {
            const temp = this.form.branch_id;
            this.form.branch_id = null;
            setTimeout(() => {
                this.form.branch_id = temp;
            }, 100);

            this.getData(this.form.customer_id, temp);
        },

        getData(customer_id, branch_id) {
            axios.get(`/api/dashboard/get_setup/`, {
                params: {
                    cust_id: customer_id,
                    cust_branch: branch_id
                }
            })
                .then(res => {
                    const data = res.data;
                    this.columns = data.data.column_count
                    this.charts = data.details
                    console.log(data.statbox)
                    this.stat_box = data.statbox
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

        toggleSection() {
            this.sectionVisible = !this.sectionVisible
        },

        addChart() {
            if (this.sectionVisible === false) {
                this.sectionVisible = true
            }
            const safeColumns = [1, 2, 3, 4, 6]
            if (!safeColumns.includes(this.columns)) {
                Swal.fire('Invalid Column Count', 'Please use 1, 2, 3, 4, or 6', 'warning')
                return
            }

            this.charts.push({
                dataFrom: '',
                chartType: 'ChartBarGroup',
                sequence: this.charts.length + 1,
            })
        },

        removeChart(index) {
            this.charts.splice(index, 1)

            if (this.charts.length == 0) {
                this.sectionVisible = false
            }
        }
    },

    setup(props) {
        const form = reactive({
            customer_id: '',
            branch_id: ''
        });

        const columns = ref(3);
        const stat_box = ref(false);

        const charts = ref([]);

        const submit = () => {
            const body = {
                customer_id: form.customer_id,
                branch_id: form.branch_id,
                columns: columns.value,
                charts: charts.value,
                stat_box: stat_box.value
            };
            console.log(body)
            Inertia.post('/editor/post', {
                customer_id: form.customer_id,
                branch_id: form.branch_id,
                columns: columns.value,
                charts: charts.value,
                stat_box: stat_box.value,
            }, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Success!',
                        text: 'User saved successfully.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
            });
        }

        return {
            form,
            submit, columns,
            charts, stat_box
        }
    }
}
</script>

<style scoped>
.sticky-top {
    top: 0;
    z-index: 1050;
}
</style>
