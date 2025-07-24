<template>
    <h5 class="text-lg font-semibold mb-2 text-center">{{ title }}</h5>
    <div class="card shadow p-3 chart">
        <div style="height: 99%; overflow-y: auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                    <th>Lab No</th>
                    <th>Patient Name</th>
                    <th>Test</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(patient, i) in patients" :key="i">
                    <td>{{ patient.lab_no }}</td>
                    <td>{{ patient.patient_name }}</td>
                    <td>{{ patient.test_name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
  </div>
</template>
<style>
thead {
  position: sticky;
  top: 0;
  background: black;
  z-index: 5;
  text-align: center;
  color: white;
}
</style>
<script setup>
defineProps({
  title: String,
  patients: Array,
});
</script>
