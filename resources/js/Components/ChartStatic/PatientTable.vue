<template>
  <div class="card shadow p-3">
    <h5>{{ title }}</h5>
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

<script setup>
defineProps({
  title: String,
  patients: Array,
});
</script>
