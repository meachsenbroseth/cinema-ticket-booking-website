<template>
  <!-- Row 1 -->
  <div class="flex items-center justify-between px-8 h-16 border-b border-white/10">
    <div>
      <SearchBar />
    </div>

    <div class="absolute left-1/2 -translate-x-1/2 flex flex-col items-center gap-0.5">
      <Icon name="lucide:clapperboard" class="w-6 h-6 text-red-500" />
    </div>

    <div class="flex items-center gap-2">
      <!-- Tickets Button (always visible) -->
      <NuxtLink to="/account/tickets">
        <button
          class="flex items-center gap-1.5 bg-white/8 hover:bg-white/12 border border-white/10 rounded-full px-4 h-9 text-sm font-medium transition-colors">
          <Icon name="lucide:ticket" class="w-4 h-4" />
          Tickets
        </button>
      </NuxtLink>

      <!-- Auth Section: Show Avatar if logged in, else Login button -->
      <div v-if="authStore.isLoggedIn" class="relative">
        <button
          @click="toggleDropdown"
          class="flex items-center gap-2 bg-white/8 hover:bg-white/12 border border-white/10 rounded-full pl-2 pr-3 h-9 text-sm font-medium transition-colors"
        >
          <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center overflow-hidden">
            <img
              v-if="authStore.user?.avatar"
              :src="authStore.user.avatar"
              class="w-full h-full object-cover"
            />
            <Icon v-else name="lucide:user" class="w-3.5 h-3.5 text-red-400" />
          </div>
          <span class="text-white/80">{{ authStore.user?.name?.split(' ')[0] || 'User' }}</span>
          <Icon name="lucide:chevron-down" class="w-3.5 h-3.5 text-white/50" />
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="dropdownOpen"
          class="absolute right-0 mt-2 w-56 bg-[#1a0a0a] border border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden"
        >
          <div class="px-4 py-3 border-b border-white/10">
            <p class="text-white text-sm font-semibold">{{ authStore.user?.name }}</p>
            <p class="text-white/40 text-xs">{{ authStore.user?.email }}</p>
          </div>
          <div class="py-2">
            <NuxtLink
              to="/account"
              class="flex items-center gap-3 px-4 py-2 text-sm text-white/70 hover:bg-white/10 transition-colors"
            >
              <Icon name="lucide:user" class="w-4 h-4" />
              My Account
            </NuxtLink>
            <NuxtLink
              to="/account/tickets"
              class="flex items-center gap-3 px-4 py-2 text-sm text-white/70 hover:bg-white/10 transition-colors"
            >
              <Icon name="lucide:ticket" class="w-4 h-4" />
              My Tickets
            </NuxtLink>
            <button
              v-if="authStore.isAdmin"
              @click="navigateTo('/admin')"
              class="w-full flex items-center gap-3 px-4 py-2 text-sm text-white/70 hover:bg-white/10 transition-colors"
            >
              <Icon name="lucide:layout-dashboard" class="w-4 h-4" />
              Admin Panel
            </button>
            <button
              @click="handleLogout"
              class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors border-t border-white/10 mt-1"
            >
              <Icon name="lucide:log-out" class="w-4 h-4" />
              Sign Out
            </button>
          </div>
        </div>
      </div>

      <NuxtLink v-else to="/auth/login">
        <button
          class="flex items-center gap-1.5 bg-white/8 hover:bg-white/12 border border-white/10 rounded-full px-4 h-9 text-sm font-medium transition-colors"
        >
          <Icon name="lucide:user" class="w-4 h-4" />
          Join Now
        </button>
      </NuxtLink>
    </div>
  </div>

  <!-- Row 2 -->
  <div class="flex items-center justify-between px-8 h-11">
    <div class="flex items-center gap-1">
      <nuxt-link
        v-for="link in links"
        :key="link.path"
        :to="link.path"
        class="flex items-center gap-2 px-4 h-11 text-sm font-medium text-white/60 hover:text-white border-b-2 border-transparent hover:border-white/20 transition-all"
        active-class="!text-white !border-red-500"
      >
        <Icon :name="link.icon" class="w-4 h-4" :class="link.path === '/' ? 'text-red-500' : ''" />
        {{ link.label }}
      </nuxt-link>
    </div>

    <CinemaDropdown />
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/authStore'
import CinemaDropdown from './ui/CinemaDropdown.vue'
import SearchBar from './ui/SearchBar.vue'

const authStore = useAuthStore()
const dropdownOpen = ref(false)

const links = [
  { label: 'Home', path: '/', icon: 'lucide:house' },
  { label: 'Cinema', path: '/cinema', icon: 'lucide:map-pin' },
]

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const handleLogout = async () => {
  await authStore.logout()
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownOpen.value && !event.target.closest('.relative')) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>