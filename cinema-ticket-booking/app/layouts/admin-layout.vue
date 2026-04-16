<!-- layouts/admin-layout.vue -->
<template>
  <div class="flex min-h-screen bg-[#0d0000]">
    <!-- Sidebar -->
    <AdminSidebar />

    <!-- Main Content -->
    <main
      class="flex-1 transition-all duration-300"
      :class="isCollapsed ? 'ml-20' : 'ml-64'"
    >
      <div class="p-6">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
// import AdminSidebar from '~/components/admin/AdminSidebar.vue'
import { useAdminStore } from '~/stores/admin'

const adminStore = useAdminStore()
const isCollapsed = computed(() => adminStore.sidebarCollapsed)
</script>