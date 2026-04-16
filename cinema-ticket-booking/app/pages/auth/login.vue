<script setup>
definePageMeta({ layout: 'auth-layout', middleware: 'guest' })

import { useAuthStore } from '~/stores/authStore'

const authStore = useAuthStore()

const email       = ref('')
const password    = ref('')
const showPassword = ref(false)
const rememberMe  = ref(false)

const handleLogin = async () => {
  try {
    await authStore.login({
      email:    email.value,
      password: password.value,
    })
  } catch (err) {
    // error shown via authStore.error
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#0d0000] flex items-center justify-center px-4 relative overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-red-900/20 rounded-full blur-[120px] pointer-events-none" />
    <div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-red-950/30 rounded-full blur-[100px] pointer-events-none" />

    <div class="w-full max-w-md relative z-10">
      <div class="bg-white/[0.04] backdrop-blur-md rounded-2xl border border-white/10 p-8">

        <div class="mb-7">
          <h2 class="text-white text-xl font-bold">Welcome back</h2>
          <p class="text-white/40 text-sm mt-1">Sign in to continue to Legend Cinema</p>
        </div>

        <!-- Error Message -->
        <div v-if="authStore.error"
          class="mb-5 p-3 bg-red-500/15 border border-red-500/30 rounded-xl flex items-center gap-2 text-red-400 text-sm">
          <Icon name="lucide:alert-circle" class="w-4 h-4 shrink-0" />
          {{ authStore.error }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">

          <!-- Email -->
          <div class="space-y-1.5">
            <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Email</label>
            <div class="relative group">
              <Icon name="lucide:mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 group-focus-within:text-red-400 transition-colors" />
              <input
                type="email"
                v-model="email"
                placeholder="you@example.com"
                required
                class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all"
              />
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-1.5">
            <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Password</label>
            <div class="relative group">
              <Icon name="lucide:lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 group-focus-within:text-red-400 transition-colors" />
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                placeholder="••••••••"
                required
                class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-11 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all"
              />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/60 transition-colors">
                <Icon :name="showPassword ? 'lucide:eye-off' : 'lucide:eye'" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Remember + Forgot -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2.5 cursor-pointer group">
              <div @click="rememberMe = !rememberMe"
                class="w-4 h-4 rounded border flex items-center justify-center transition-all shrink-0"
                :class="rememberMe ? 'bg-red-600 border-red-600' : 'border-white/20 bg-white/5'">
                <Icon v-if="rememberMe" name="lucide:check" class="w-3 h-3 text-white" />
              </div>
              <span class="text-white/40 text-sm select-none">Remember me</span>
            </label>
            <nuxt-link to="/forgot-password" class="text-sm text-red-500/80 hover:text-red-400 transition-colors">
              Forgot password?
            </nuxt-link>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="authStore.loading"
            class="w-full bg-red-600 hover:bg-red-500 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-xl transition-all mt-2 flex items-center justify-center gap-2 group"
          >
            <Icon v-if="authStore.loading" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
            <span>{{ authStore.loading ? 'Signing in...' : 'Sign In' }}</span>
            <Icon v-if="!authStore.loading" name="lucide:arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
          </button>

        </form>

        <!-- Divider -->
        <div class="flex items-center gap-3 my-6">
          <div class="flex-1 h-px bg-white/8" />
          <span class="text-white/25 text-xs tracking-widest uppercase">or continue with</span>
          <div class="flex-1 h-px bg-white/8" />
        </div>

        <!-- Social -->
        <div class="grid grid-cols-2 gap-3">
          <button type="button"
            class="flex items-center justify-center gap-2.5 bg-white/5 hover:bg-white/8 border border-white/10 hover:border-white/20 rounded-xl py-2.5 transition-all group">
            <Icon name="lucide:facebook" class="w-4 h-4 text-white/40 group-hover:text-white/70 transition-colors" />
            <span class="text-white/50 text-sm group-hover:text-white/80 transition-colors">Facebook</span>
          </button>
          <button type="button"
            class="flex items-center justify-center gap-2.5 bg-white/5 hover:bg-white/8 border border-white/10 hover:border-white/20 rounded-xl py-2.5 transition-all group">
            <Icon name="lucide:globe" class="w-4 h-4 text-white/40 group-hover:text-white/70 transition-colors" />
            <span class="text-white/50 text-sm group-hover:text-white/80 transition-colors">Google</span>
          </button>
        </div>

        <p class="text-center text-white/30 text-sm mt-6">
          Don't have an account?
          <nuxt-link to="/auth/register" class="text-red-500 hover:text-red-400 font-medium transition-colors ml-1">
            Create one
          </nuxt-link>
        </p>

      </div>

      <p class="text-center text-white/15 text-xs mt-6">
        By signing in you agree to our
        <a href="#" class="underline hover:text-white/30 transition-colors">Terms</a>
        and
        <a href="#" class="underline hover:text-white/30 transition-colors">Privacy Policy</a>
      </p>
    </div>
  </div>
</template>