<template>
  <div class="min-h-screen bg-[#0d0000] py-10 px-4">
    <div class="max-w-5xl mx-auto">

      <!-- Header -->
      <div class="mb-8">
        <p class="text-red-500 text-xs uppercase tracking-widest font-semibold mb-1">Account</p>
        <h1 class="text-3xl font-black text-white">My Profile</h1>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 items-start">

        <!-- Sidebar -->
        <div class="bg-white/[0.03] border border-white/10 rounded-2xl overflow-hidden top-24">

          <!-- Avatar block -->
          <div class="flex flex-col items-center text-center px-6 py-8 border-b border-white/10">
            <div class="relative mb-4">
              <div
                class="w-[72px] h-[72px] rounded-full bg-red-900/40 border-2 border-red-500/30 flex items-center justify-center text-white text-2xl font-black overflow-hidden">
                <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover" />
                <span v-else>{{ user.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <button @click="triggerAvatarUpload"
                class="absolute -bottom-1 -right-1 w-6 h-6 bg-red-600 hover:bg-red-500 rounded-full flex items-center justify-center transition-colors">
                <Icon name="lucide:camera" class="w-3 h-3 text-white" />
              </button>
              <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="handleAvatarUpload" />
            </div>
            <p class="text-white font-bold text-base leading-tight">{{ user.name }}</p>
            <p class="text-white/40 text-xs mt-0.5">{{ user.email }}</p>
            <p class="text-white/20 text-[10px] uppercase tracking-widest mt-2">Member since {{ user.memberSince }}</p>
          </div>

          <!-- Nav -->
          <div class="p-3">
            <button v-for="item in navItems" :key="item.key" @click="activeSection = item.key"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all mb-0.5"
              :class="activeSection === item.key
                ? 'bg-red-600/15 text-red-400'
                : 'text-white/50 hover:text-white hover:bg-white/5'">
              <Icon :name="item.icon" class="w-4 h-4 shrink-0" />
              {{ item.label }}
              <span v-if="item.key === 'orders'"
                class="ml-auto text-[10px] bg-white/10 text-white/40 px-1.5 py-0.5 rounded-full">
                {{ orders.length }}
              </span>
            </button>
          </div>

          <!-- Sign out -->
          <div class="px-3 pb-3 border-t border-white/5 pt-3">
            <button
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-white/30 hover:text-red-400 hover:bg-red-500/5 transition-all">
              <Icon name="lucide:log-out" class="w-4 h-4" />
              Sign out
            </button>
          </div>

        </div>

        <!-- Main Content -->
        <div>

          <!-- Profile -->
          <div v-if="activeSection === 'profile'" class="bg-white/[0.03] border border-white/10 rounded-2xl p-6">
            <h2 class="text-white font-bold text-base mb-5">Profile Information</h2>
            <form @submit.prevent="saveProfile" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="space-y-1.5">
                  <label class="text-white/40 text-[11px] uppercase tracking-widest">Full Name</label>
                  <div class="relative group">
                    <Icon name="lucide:user"
                      class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20 group-focus-within:text-red-400 transition-colors" />
                    <input v-model="profileForm.name" type="text" required
                      class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
                  </div>
                </div>

                <div class="space-y-1.5">
                  <label class="text-white/40 text-[11px] uppercase tracking-widest">Email</label>
                  <div class="relative group">
                    <Icon name="lucide:mail"
                      class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20 group-focus-within:text-red-400 transition-colors" />
                    <input v-model="profileForm.email" type="email" required
                      class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
                  </div>
                </div>

                <div class="space-y-1.5">
                  <label class="text-white/40 text-[11px] uppercase tracking-widest">Phone</label>
                  <div class="relative group">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm leading-none">🇰🇭</span>
                    <input v-model="profileForm.phone" type="tel" placeholder="+855 12 345 678"
                      class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-white text-sm placeholder-white/20 focus:outline-none focus:border-red-500/50 transition-all" />
                  </div>
                </div>

                <div class="space-y-1.5">
                  <label class="text-white/40 text-[11px] uppercase tracking-widest">Date of Birth</label>
                  <div class="relative group">
                    <Icon name="lucide:calendar"
                      class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20 group-focus-within:text-red-400 transition-colors" />
                    <input v-model="profileForm.dob" type="date"
                      class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-white text-sm focus:outline-none focus:border-red-500/50 transition-all" />
                  </div>
                </div>

              </div>

              <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-500 active:scale-[0.98] text-white text-sm font-semibold rounded-xl transition-all">
                Save Changes
                <Icon name="lucide:check" class="w-4 h-4" />
              </button>
            </form>
          </div>

          <!-- Orders -->
          <div v-if="activeSection === 'orders'">
            <div v-if="orders.length" class="space-y-3">
              <div v-for="order in orders" :key="order.id"
                class="bg-white/[0.03] border border-white/10 hover:border-white/20 rounded-2xl overflow-hidden transition-all">
                <div class="flex items-center gap-4 p-4">
                  <div class="w-10 h-14 rounded-lg overflow-hidden shrink-0 bg-white/5">
                    <img :src="order.poster" :alt="order.movieTitle" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-white font-semibold text-sm truncate">{{ order.movieTitle }}</p>
                    <div class="flex items-center gap-3 mt-1 text-white/40 text-xs flex-wrap">
                      <span class="flex items-center gap-1">
                        <Icon name="lucide:map-pin" class="w-3 h-3 text-red-500" />
                        {{ order.cinemaName }}
                      </span>
                      <span class="flex items-center gap-1">
                        <Icon name="lucide:calendar" class="w-3 h-3" />
                        {{ formatDate(order.showDate) }} · {{ order.showTime }}
                      </span>
                    </div>
                    <div class="flex gap-1 mt-1.5 flex-wrap">
                      <span v-for="seat in order.seats" :key="seat"
                        class="px-1.5 py-0.5 bg-red-500/15 border border-red-500/20 rounded text-red-400 text-[10px] font-semibold">
                        {{ seat }}
                      </span>
                    </div>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-red-400 font-black">${{ order.totalAmount.toFixed(2) }}</p>
                    <span class="text-[10px] px-2 py-0.5 rounded-full mt-1 inline-block"
                      :class="order.status === 'confirmed' ? 'bg-green-500/15 text-green-400' : 'bg-white/10 text-white/30'">
                      {{ order.status }}
                    </span>
                  </div>
                </div>
                <div class="border-t border-dashed border-white/10 px-4 py-2 flex items-center justify-between">
                  <span class="text-white/20 text-[10px]">Order #{{ order.id }}</span>
                  <NuxtLink to="/account/tickets"
                    class="text-white/30 hover:text-red-400 text-[10px] transition-colors flex items-center gap-1">
                    View ticket
                    <Icon name="lucide:chevron-right" class="w-3 h-3" />
                  </NuxtLink>
                </div>
              </div>
            </div>
            <div v-else
              class="flex flex-col items-center justify-center py-20 bg-white/[0.03] border border-white/10 rounded-2xl text-center">
              <Icon name="lucide:shopping-bag" class="w-10 h-10 text-white/10 mb-3" />
              <p class="text-white/30 text-sm">No orders yet</p>
              <NuxtLink to="/" class="mt-3 text-red-500 hover:text-red-400 text-xs">Browse Movies</NuxtLink>
            </div>
          </div>

          <!-- Security -->
          <div v-if="activeSection === 'security'" class="bg-white/[0.03] border border-white/10 rounded-2xl p-6">
            <h2 class="text-white font-bold text-base mb-5">Change Password</h2>
            <form @submit.prevent="changePassword" class="space-y-4 max-w-md">

              <div class="space-y-1.5">
                <label class="text-white/40 text-[11px] uppercase tracking-widest">Current Password</label>
                <div class="relative group">
                  <Icon name="lucide:lock"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20 group-focus-within:text-red-400 transition-colors" />
                  <input v-model="passwordForm.currentPassword" type="password" required
                    class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-white text-sm focus:outline-none focus:border-red-500/50 transition-all" />
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="text-white/40 text-[11px] uppercase tracking-widest">New Password</label>
                <div class="relative group">
                  <Icon name="lucide:lock"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20 group-focus-within:text-red-400 transition-colors" />
                  <input v-model="passwordForm.newPassword" type="password" required
                    class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-white text-sm focus:outline-none focus:border-red-500/50 transition-all" />
                </div>
                <div class="flex gap-1 mt-1.5">
                  <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full transition-all duration-300"
                    :class="i <= passwordStrength ? strengthColor : 'bg-white/10'" />
                </div>
                <p v-if="passwordForm.newPassword" class="text-xs" :class="strengthTextColor">{{ strengthLabel }}</p>
              </div>

              <div class="space-y-1.5">
                <label class="text-white/40 text-[11px] uppercase tracking-widest">Confirm Password</label>
                <div class="relative group">
                  <Icon name="lucide:lock"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20 group-focus-within:text-red-400 transition-colors" />
                  <input v-model="passwordForm.confirmPassword" type="password" required
                    class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-10 py-2.5 text-white text-sm focus:outline-none focus:border-red-500/50 transition-all"
                    :class="passwordForm.confirmPassword && !passwordsMatch ? 'border-red-500/50' : ''" />
                  <Icon v-if="passwordForm.confirmPassword"
                    :name="passwordsMatch ? 'lucide:check-circle' : 'lucide:x-circle'"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4"
                    :class="passwordsMatch ? 'text-green-400' : 'text-red-400'" />
                </div>
              </div>

              <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-500 active:scale-[0.98] text-white text-sm font-semibold rounded-xl transition-all">
                Update Password
                <Icon name="lucide:shield-check" class="w-4 h-4" />
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>

    <!-- Toast -->
    <transition name="slide-up">
      <div v-if="toastMessage" class="fixed bottom-6 right-6 z-50">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium"
          :class="toastType === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
          <Icon :name="toastType === 'success' ? 'lucide:check-circle' : 'lucide:x-circle'" class="w-4 h-4" />
          {{ toastMessage }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/authStore'
import { storeToRefs } from 'pinia'

definePageMeta({
  middleware: 'auth' // protect this page
})

const authStore = useAuthStore()
const { user, token } = storeToRefs(authStore)
const { $api } = useNuxtApp()

const loading = ref(true)
const ordersLoading = ref(true)
const activeSection = ref('profile')
const avatarInput = ref(null)

// Form bindings
const profileForm = reactive({
  name: '',
  email: '',
  phone: '',
  dob: '',
  avatarFile: null,
})

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Orders data
const orders = ref([])

// Navigation
const navItems = [
  { key: 'profile', label: 'Profile Settings', icon: 'lucide:user' },
  { key: 'orders', label: 'Order History', icon: 'lucide:ticket' },
  { key: 'security', label: 'Security', icon: 'lucide:shield' },
]

// ── Fetch user profile ───────────────────────────────────
const fetchProfile = async () => {
  try {
    const response = await $api('/user/profile')
    // Update authStore user
    authStore.user = response
    // Populate form
    profileForm.name = response.name || ''
    profileForm.email = response.email || ''
    profileForm.phone = response.phone || ''
    profileForm.dob = response.dob || ''
  } catch (err) {
    console.error('Failed to fetch profile:', err)
    showToast('Could not load profile', 'error')
  }
}

// ── Fetch user bookings (orders) ─────────────────────────
const fetchOrders = async () => {
  ordersLoading.value = true
  try {
    const response = await $api('/user/bookings')
    // Expected response: { data: [...] } or just array
    orders.value = response.data || response
  } catch (err) {
    console.error('Failed to fetch bookings:', err)
    orders.value = []
  } finally {
    ordersLoading.value = false
  }
}

// ── Save profile (including avatar) ──────────────────────
const saveProfile = async () => {
  const formData = new FormData()
  formData.append('name', profileForm.name)
  formData.append('email', profileForm.email)
  if (profileForm.phone) formData.append('phone', profileForm.phone)
  if (profileForm.dob) formData.append('dob', profileForm.dob)
  if (profileForm.avatarFile) formData.append('avatar', profileForm.avatarFile)

  try {
    const response = await $api('/user/profile', {
      method: 'POST',
      body: formData,
    })
    // Update local user and store
    authStore.user = response
    showToast('Profile updated successfully', 'success')
  } catch (err) {
    console.error('Update failed:', err)
    showToast(err?.data?.message || 'Update failed', 'error')
  }
}

// ── Change password ──────────────────────────────────────
const changePassword = async () => {
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    showToast('New passwords do not match', 'error')
    return
  }
  if (passwordForm.newPassword.length < 6) {
    showToast('Password must be at least 6 characters', 'error')
    return
  }

  try {
    await $api('/changePassword', {
      method: 'POST',
      body: {
        current_password: passwordForm.currentPassword,
        new_password: passwordForm.newPassword,
        new_password_confirmation: passwordForm.confirmPassword,
      },
    })
    showToast('Password changed successfully', 'success')
    // Clear form
    passwordForm.currentPassword = ''
    passwordForm.newPassword = ''
    passwordForm.confirmPassword = ''
  } catch (err) {
    console.error('Password change failed:', err)
    showToast(err?.data?.message || 'Failed to change password', 'error')
  }
}

// ── Avatar upload handling ───────────────────────────────
const triggerAvatarUpload = () => avatarInput.value?.click()
const handleAvatarUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return
  profileForm.avatarFile = file
  // Show preview immediately
  const reader = new FileReader()
  reader.onload = (ev) => {
    authStore.user.avatar = ev.target.result
  }
  reader.readAsDataURL(file)
}

// ── Logout ───────────────────────────────────────────────
const handleLogout = async () => {
  await authStore.logout()
}

// ── Format date helper ───────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

// ── Toast notification ───────────────────────────────────
const toastMessage = ref('')
const toastType = ref('success')
let toastTimeout = null
const showToast = (msg, type = 'success') => {
  if (toastTimeout) clearTimeout(toastTimeout)
  toastMessage.value = msg
  toastType.value = type
  toastTimeout = setTimeout(() => {
    toastMessage.value = ''
  }, 3000)
}

// ── Password strength ────────────────────────────────────
const passwordsMatch = computed(() => passwordForm.newPassword === passwordForm.confirmPassword)
const passwordStrength = computed(() => {
  const p = passwordForm.newPassword
  if (!p) return 0
  let s = 0
  if (p.length >= 8) s++
  if (/[A-Z]/.test(p)) s++
  if (/[0-9]/.test(p)) s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
})
const strengthColor = computed(() => ['', 'bg-red-500', 'bg-orange-400', 'bg-yellow-400', 'bg-green-400'][passwordStrength.value])
const strengthTextColor = computed(() => ['', 'text-red-400', 'text-orange-400', 'text-yellow-400', 'text-green-400'][passwordStrength.value])
const strengthLabel = computed(() => ['', 'Weak', 'Fair', 'Good', 'Strong'][passwordStrength.value])

// ── Lifecycle ────────────────────────────────────────────
onMounted(async () => {
  loading.value = true
  await Promise.all([fetchProfile(), fetchOrders()])
  loading.value = false
})
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
</style>