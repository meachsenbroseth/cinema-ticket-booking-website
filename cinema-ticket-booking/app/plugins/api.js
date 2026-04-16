export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    headers: {
      Accept: 'application/json',
    },
    onRequest({ options }) {
      const token = useCookie('token')
      if (token.value) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token.value}`,
        }
      }
    },
    onResponseError({ response }) {
      // Don't redirect on every error — only 401
      if (response.status === 401) {
        navigateTo('/auth/login')
      }
      // Don't throw for other errors — let the caller handle it
    },
  })

  return {
    provide: { api },
  }
})