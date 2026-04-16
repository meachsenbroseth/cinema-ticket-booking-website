// stores/admin.js
import { defineStore } from 'pinia'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    sidebarCollapsed: false,
  }),
  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },
    setSidebarCollapsed(value) {
      this.sidebarCollapsed = value
    },
  },
})