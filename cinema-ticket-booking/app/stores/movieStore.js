import { defineStore } from 'pinia'

export const useMovieStore = defineStore('movie', {
  state: () => ({
    movies: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchMovies(params = {}) {
      this.loading = true
      this.error = null
      try {
        const { $api } = useNuxtApp()
        const response = await $api('/movies', { params })
        this.movies = response.data
        return response
      } catch (err) {
        this.error = err
        console.error('Failed to fetch movies:', err)
        throw err
      } finally {
        this.loading = false
      }
    }
  }
})