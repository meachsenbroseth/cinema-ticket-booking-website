import { defineStore } from 'pinia'

export const useShowtimeStore = defineStore('showtime', {
  state: () => ({
    // For movie detail page — showtimes grouped by date → cinema
    movieShowtimes: {},

    // For admin — flat list
    showtimes: [],

    // Single showtime with seat map
    currentShowtime: null,

    loading: false,
    error: null,
  }),

  getters: {
    // Get available dates from movieShowtimes
    availableDates: (state) => {
      return Object.keys(state.movieShowtimes).sort()
    },

    // Get showtimes for a specific date
    getShowtimesByDate: (state) => (date) => {
      return state.movieShowtimes[date] ?? []
    },
  },

  actions: {
    // ── Fetch showtimes for a movie (used on movie detail page) ──
    // async fetchMovieShowtimes(slug, cinemaId = null) {
    //   this.loading = true
    //   this.error = null
    //   this.movieShowtimes = {}

    //   try {
    //     const { $api } = useNuxtApp()
    //     const params = cinemaId ? { cinema_id: cinemaId } : {}

    //     // ✅ Use slug-based endpoint
    //     const response = await $api(`/showtimes/movie/${slug}`, { params })

    //     const raw = response.showtimes ?? {}

    //     const grouped = {}

    //     Object.entries(raw).forEach(([date, cinemas]) => {
    //       grouped[date] = cinemas.map((group) => ({
    //         id: group.cinema.id,
    //         cinema: group.cinema.name,
    //         address: group.cinema.address,
    //         format: group.format,
    //         price: group.price_standard,
    //         price_vip: group.price_vip,
    //         hall: group.hall,
    //         times: (group.times ?? []).map(t => t.show_time),
    //         showtimeIds: (group.times ?? []).map(t => ({
    //           id: t.id,
    //           time: t.show_time,
    //         })),
    //       }))
    //     })

    //     this.movieShowtimes = grouped
    //     return grouped

    //   } catch (err) {
    //     this.error = err
    //     console.error('Failed to fetch showtimes:', err)
    //   } finally {
    //     this.loading = false
    //   }
    // },
    async fetchMovieShowtimes(slug, cinemaId = null) {
      this.loading = true
      this.error = null
      this.movieShowtimes = {}

      try {
        const { $api } = useNuxtApp()
        const params = cinemaId ? { cinema_id: cinemaId } : {}
        const response = await $api(`/showtimes/movie/${slug}`, { params })
        const raw = response.showtimes ?? {}

        const grouped = {}

        Object.entries(raw).forEach(([date, cinemas]) => {
          grouped[date] = cinemas.map((group) => ({
            id: group.cinema.id,
            cinema: group.cinema.name,
            address: group.cinema.address,
            format: group.format,
            price: group.price_standard,
            price_vip: group.price_vip,
            hall: group.hall,
            // ✅ Keep times as array of objects {id, time}
            times: (group.times ?? []).map(t => ({
              id: t.id,
              time: t.show_time, // already formatted "7:00 PM" from Laravel Carbon
            })),
          }))
        })

        this.movieShowtimes = grouped
        return grouped

      } catch (err) {
        this.error = err
        console.error('Failed to fetch showtimes:', err)
      } finally {
        this.loading = false
      }
    },

    // ── Fetch single showtime with seat availability ──
    async fetchShowtime(id) {
      this.loading = true
      this.error = null

      try {
        const { $api } = useNuxtApp()
        const response = await $api(`/showtimes/${id}`)
        this.currentShowtime = response
        return response
      } catch (err) {
        this.error = err
        console.error('Failed to fetch showtime:', err)
      } finally {
        this.loading = false
      }
    },

    // ── Admin: fetch all showtimes ──
    async fetchShowtimes(params = {}) {
      this.loading = true
      this.error = null

      try {
        const { $api } = useNuxtApp()
        const response = await $api('/showtimes', { params })
        this.showtimes = response.data ?? []
        return response
      } catch (err) {
        this.error = err
        console.error('Failed to fetch showtimes:', err)
      } finally {
        this.loading = false
      }
    },

    // ── Admin: create showtime ──
    async createShowtime(data) {
      try {
        const { $api } = useNuxtApp()
        const response = await $api('/showtimes', {
          method: 'POST',
          body: data,
        })
        return response
      } catch (err) {
        console.error('Failed to create showtime:', err)
        throw err
      }
    },

    // ── Admin: update showtime ──
    async updateShowtime(id, data) {
      try {
        const { $api } = useNuxtApp()
        const response = await $api(`/showtimes/${id}`, {
          method: 'PUT',
          body: data,
        })
        return response
      } catch (err) {
        console.error('Failed to update showtime:', err)
        throw err
      }
    },

    // ── Admin: delete showtime ──
    async deleteShowtime(id) {
      try {
        const { $api } = useNuxtApp()
        await $api(`/showtimes/${id}`, { method: 'DELETE' })
        this.showtimes = this.showtimes.filter(s => s.id !== id)
      } catch (err) {
        console.error('Failed to delete showtime:', err)
        throw err
      }
    },

    // ── Reset ──
    reset() {
      this.movieShowtimes = {}
      this.currentShowtime = null
      this.error = null
    },
  },
})