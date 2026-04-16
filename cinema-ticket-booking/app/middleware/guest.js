// middleware/guest.js
export default defineNuxtRouteMiddleware(() => {
  const authStore = useAuthStore()

  if (authStore.isLoggedIn) {
    if (authStore.isAdmin) {
      return navigateTo('/admin')
    }
    return navigateTo('/')
  }
})