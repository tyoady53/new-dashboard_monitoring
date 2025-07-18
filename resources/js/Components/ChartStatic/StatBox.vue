<template>
  <div class="stat-box">
    <div class="stat-label">{{ label }}</div>
    <div class="stat-value">{{ value }}</div>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: [String, Number],
});
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
