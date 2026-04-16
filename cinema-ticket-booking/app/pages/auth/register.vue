<script setup>
definePageMeta({ layout: 'auth-layout', middleware: 'guest' })

import { useAuthStore } from '~/stores/authStore'

const authStore = useAuthStore()
const showPassword = ref(false)

const form = reactive({
  firstName:       '',
  lastName:        '',
  phone:           '',
  email:           '',
  password:        '',
  confirmPassword: '',
  agreed:          false,
})

const passwordsMatch = computed(() =>
  form.password === form.confirmPassword
)

const passwordStrength = computed(() => {
  const p = form.password
  if (!p) return 0
  let score = 0
  if (p.length >= 8) score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  return score
})

const strengthColor = computed(() => {
  if (passwordStrength.value <= 1) return 'bg-red-500'
  if (passwordStrength.value === 2) return 'bg-orange-400'
  if (passwordStrength.value === 3) return 'bg-yellow-400'
  return 'bg-green-400'
})

const strengthTextColor = computed(() => {
  if (passwordStrength.value <= 1) return 'text-red-400'
  if (passwordStrength.value === 2) return 'text-orange-400'
  if (passwordStrength.value === 3) return 'text-yellow-400'
  return 'text-green-400'
})

const strengthLabel = computed(() => {
  if (!form.password) return ''
  return ['', 'Weak', 'Fair', 'Good', 'Strong'][passwordStrength.value]
})

const handleRegister = async () => {
  if (!passwordsMatch.value || !form.agreed) return

  try {
    await authStore.register({
      name:                  `${form.firstName} ${form.lastName}`.trim(),
      email:                 form.email,
      phone:                 form.phone ? `+855${form.phone}` : null,
      password:              form.password,
      password_confirmation: form.confirmPassword,
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

    <div class="w-full max-w-md relative z-10 py-10">
      <div class="bg-white/[0.04] backdrop-blur-md rounded-2xl border border-white/10 p-8">

        <div class="mb-7">
          <h2 class="text-white text-xl font-bold">Create account</h2>
          <p class="text-white/40 text-sm mt-1">Join Legend Cinema and start booking today</p>
        </div>

        <!-- Error Message -->
        <div v-if="authStore.error"
          class="mb-5 p-3 bg-red-500/15 border border-red-500/30 rounded-xl flex items-center gap-2 text-red-400 text-sm">
          <Icon name="lucide:alert-circle" class="w-4 h-4 shrink-0" />
          {{ authStore.error }}
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">

          <!-- Name row -->
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="text-white/50 text-xs uppercase tracking-widest font-medium">First name</label>
              <div class="relative group">
                <Icon name="lucide:user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 group-focus-within:text-red-400 transition-colors" />
                <input type="text" v-model="form.firstName" placeholder="John" required
                  class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Last name</label>
              <input type="text" v-model="form.lastName" placeholder="Doe" required
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
            </div>
          </div>

          <!-- Phone -->
          <div class="space-y-1.5">
            <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Phone number</label>
            <div class="relative group">
              <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                <span class="text-sm">🇰🇭</span>
                <span class="text-white/30 text-sm">+855</span>
              </div>
              <input type="tel" v-model="form.phone" placeholder="12 345 678"
                class="w-full bg-white/5 border border-white/10 rounded-xl pl-20 pr-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
            </div>
          </div>

          <!-- Email -->
          <div class="space-y-1.5">
            <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Email</label>
            <div class="relative group">
              <Icon name="lucide:mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 group-focus-within:text-red-400 transition-colors" />
              <input type="email" v-model="form.email" placeholder="you@example.com" required
                class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-1.5">
            <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Password</label>
            <div class="relative group">
              <Icon name="lucide:lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 group-focus-within:text-red-400 transition-colors" />
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password"
                placeholder="Min. 8 characters" required
                class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-11 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/60 transition-colors">
                <Icon :name="showPassword ? 'lucide:eye-off' : 'lucide:eye'" class="w-4 h-4" />
              </button>
            </div>
            <div class="flex gap-1 mt-2">
              <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full transition-all duration-300"
                :class="i <= passwordStrength ? strengthColor : 'bg-white/10'" />
            </div>
            <p v-if="form.password" class="text-xs mt-1" :class="strengthTextColor">{{ strengthLabel }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-1.5">
            <label class="text-white/50 text-xs uppercase tracking-widest font-medium">Confirm password</label>
            <div class="relative group">
              <Icon name="lucide:lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 group-focus-within:text-red-400 transition-colors" />
              <input type="password" v-model="form.confirmPassword" placeholder="Repeat your password" required
                class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-10 py-3 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all"
                :class="form.confirmPassword && !passwordsMatch ? 'border-red-500/50' : ''" />
              <Icon v-if="form.confirmPassword"
                :name="passwordsMatch ? 'lucide:check-circle' : 'lucide:x-circle'"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4"
                :class="passwordsMatch ? 'text-green-400' : 'text-red-400'" />
            </div>
            <p v-if="form.confirmPassword && !passwordsMatch" class="text-red-400 text-xs">
              Passwords do not match
            </p>
          </div>

          <!-- Terms -->
          <div class="pt-1">
            <label class="flex items-start gap-2.5 cursor-pointer">
              <div @click="form.agreed = !form.agreed"
                class="w-4 h-4 rounded border flex items-center justify-center transition-all shrink-0 mt-0.5"
                :class="form.agreed ? 'bg-red-600 border-red-600' : 'border-white/20 bg-white/5'">
                <Icon v-if="form.agreed" name="lucide:check" class="w-3 h-3 text-white" />
              </div>
              <span class="text-white/40 text-sm leading-relaxed">
                I agree to the
                <a href="#" class="text-red-500 hover:text-red-400 transition-colors">Terms of Service</a>
                and
                <a href="#" class="text-red-500 hover:text-red-400 transition-colors">Privacy Policy</a>
              </span>
            </label>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="!form.agreed || !passwordsMatch || authStore.loading"
            class="w-full bg-red-600 hover:bg-red-500 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-xl transition-all flex items-center justify-center gap-2 group mt-2"
          >
            <Icon v-if="authStore.loading" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
            <span>{{ authStore.loading ? 'Creating account...' : 'Create Account' }}</span>
            <Icon v-if="!authStore.loading" name="lucide:arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
          </button>

        </form>

        <p class="text-center text-white/30 text-sm mt-6">
          Already have an account?
          <nuxt-link to="/auth/login" class="text-red-500 hover:text-red-400 font-medium transition-colors ml-1">
            Sign in
          </nuxt-link>
        </p>

      </div>

      <p class="text-center text-white/15 text-xs mt-6">
        By creating an account you agree to our
        <a href="#" class="underline hover:text-white/30 transition-colors">Terms</a>
        and
        <a href="#" class="underline hover:text-white/30 transition-colors">Privacy Policy</a>
      </p>
    </div>
  </div>
</template>