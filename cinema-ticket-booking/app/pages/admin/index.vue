<template>
  <div>
    <h1 class="text-2xl font-bold text-white">Dashboard</h1>
    <p class="text-white/50 mt-2">Welcome to the admin panel.</p>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin-layout',
  middleware: 'admin'
})
</script>