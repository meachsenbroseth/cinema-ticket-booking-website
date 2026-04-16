import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user:    null,
    token:   null,
    loading: false,
    error:   null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin:    (state) => state.user?.role === 'admin',
    isUser:     (state) => state.user?.role === 'user',
  },

  actions: {
    // ── Login ────────────────────────────────────────────
    async login(credentials) {
      this.loading = true
      this.error   = null

      try {
        const { $api } = useNuxtApp()
        const response = await $api('/login', {
          method: 'POST',
          body:   credentials,
        })

        this.token = response.token
        this.user  = response.user

        // Persist token in cookie
        const cookie = useCookie('token', {
          maxAge:   60 * 60 * 24 * 7, // 7 days
          sameSite: 'lax',
        })
        cookie.value = response.token

        // Redirect based on role
        if (this.isAdmin) {
          await navigateTo('/admin')
        } else {
          await navigateTo('/')
        }

        return response

      } catch (err) {
        this.error = err?.data?.message || 'Invalid credentials'
        throw err
      } finally {
        this.loading = false
      }
    },

    // ── Register ─────────────────────────────────────────
    async register(data) {
      this.loading = true
      this.error   = null

      try {
        const { $api } = useNuxtApp()
        const response = await $api('/register', {
          method: 'POST',
          body:   data,
        })

        this.token = response.token
        this.user  = response.user

        const cookie = useCookie('token', {
          maxAge:   60 * 60 * 24 * 7,
          sameSite: 'lax',
        })
        cookie.value = response.token

        // New users always go home
        await navigateTo('/')

        return response

      } catch (err) {
        this.error = err?.data?.message || 'Registration failed'
        throw err
      } finally {
        this.loading = false
      }
    },

    // ── Logout ───────────────────────────────────────────
    async logout() {
      try {
        const { $api } = useNuxtApp()
        await $api('/logout', { method: 'POST' })
      } catch (err) {
        console.error('Logout error:', err)
      } finally {
        this.user  = null
        this.token = null

        // Clear cookie
        const cookie = useCookie('token')
        cookie.value = null

        await navigateTo('/')
      }
    },

    // ── Fetch current user ───────────────────────────────
    async fetchUser() {
      if (!this.token) return

      try {
        const { $api } = useNuxtApp()
        const response = await $api('/user/profile')
        this.user = response
      } catch (err) {
        // Token expired or invalid — clear session
        if (err?.status === 401) {
          this.clearSession()
        }
      }
    },

    // ── Load token from cookie on app init ───────────────
    async init() {
      const cookie = useCookie('token')
      if (cookie.value && !this.token) {
        this.token = cookie.value
        await this.fetchUser()
      }
    },

    // ── Clear session ────────────────────────────────────
    clearSession() {
      this.user  = null
      this.token = null
      const cookie = useCookie('token')
      cookie.value = null
    },
  },
})