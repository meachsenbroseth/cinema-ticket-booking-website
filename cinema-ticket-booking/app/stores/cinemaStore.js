import { defineStore } from 'pinia'

export const useCinemaStore = defineStore('cinema', {
  state: () => ({
    // Cinema data
    cinemas: [],
    currentCinema: null,
    showtimesByCinema: {},
    dropdownCinemas: [],
    loading: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0
    },

    // ✅ Add these missing filter states
    selectedCinema: 'All Cinemas',
    activeTab: 'Now Showing',
    activeDateIndex: 0,
  }),

  actions: {
    // ✅ Add these missing actions
    setCinema(cinema) {
      this.selectedCinema = cinema
    },

    setActiveTab(tab) {
      this.activeTab = tab
    },

    setActiveDateIndex(index) {
      this.activeDateIndex = index
    },

    clearAllFilters() {
      this.selectedCinema = 'All Cinemas'
      this.activeDateIndex = 0
    },

    loadSavedState() {
      if (process.client) {
        const savedCinema = localStorage.getItem('selectedCinema')
        if (savedCinema) this.selectedCinema = savedCinema
      }
    },

    // ── Cinema API actions ──────────────────────────────

    async fetchCinemas(params = {}) {
      this.loading = true
      try {
        const { $api } = useNuxtApp()
        const response = await $api('/cinemas', { params })
        this.cinemas = response.data
        this.pagination = {
          current_page: response.current_page,
          last_page: response.last_page,
          per_page: response.per_page,
          total: response.total
        }
        return response
      } catch (err) {
        console.error('Failed to fetch cinemas:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchCinema(slug) {
      this.loading = true
      try {
        const { $api } = useNuxtApp()
        const response = await $api(`/cinemas/${slug}`)
        this.currentCinema = response
        return response
      } catch (err) {
        console.error('Failed to fetch cinema:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchCinemaShowtimes(id, date = null) {
      this.loading = true
      try {
        const { $api } = useNuxtApp()
        const params = date ? { date } : {}
        const response = await $api(`/cinemas/${id}/showtimes`, { params })
        this.showtimesByCinema[id] = response.showtimes
        return response
      } catch (err) {
        console.error('Failed to fetch cinema showtimes:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchDropdownCinemas() {
      try {
        const { $api } = useNuxtApp()
        const response = await $api('/cinemas/dropdown')
        this.dropdownCinemas = response
        return response
      } catch (err) {
        console.error('Failed to fetch dropdown cinemas:', err)
        throw err
      }
    },

    async createCinema(data) {
      const { $api } = useNuxtApp()
      const response = await $api('/cinemas', { method: 'POST', body: data })
      await this.fetchCinemas()
      return response
    },

    async updateCinema(id, data) {
      const { $api } = useNuxtApp()
      const response = await $api(`/cinemas/${id}`, { method: 'PUT', body: data })
      await this.fetchCinemas()
      return response
    },

    async deleteCinema(id) {
      const { $api } = useNuxtApp()
      await $api(`/cinemas/${id}`, { method: 'DELETE' })
      await this.fetchCinemas()
    },

    async toggleCinemaActive(id) {
      const { $api } = useNuxtApp()
      const response = await $api(`/cinemas/${id}/toggle`, { method: 'PATCH' })
      await this.fetchCinemas()
      return response
    },
  },
})