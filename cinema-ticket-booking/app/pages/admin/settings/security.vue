<!-- pages/admin/security.vue -->
<template>
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">Security Settings</h1>
      <p class="text-white/50 text-sm mt-1">Manage your account security and authentication</p>
    </div>

    <!-- Two-Factor Authentication -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-6 mb-6">
      <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
          <h2 class="text-lg font-semibold text-white mb-1">Two-Factor Authentication</h2>
          <p class="text-white/40 text-sm">Add an extra layer of security to your account</p>
        </div>
        <button
          @click="toggle2FA"
          class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-[#1a0a0a]"
          :class="twoFAEnabled ? 'bg-red-600' : 'bg-white/20'"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            :class="twoFAEnabled ? 'translate-x-6' : 'translate-x-1'"
          />
        </button>
      </div>

      <div v-if="twoFAEnabled" class="mt-4 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
        <div class="flex items-center gap-3">
          <Icon name="lucide:shield-check" class="w-5 h-5 text-green-400" />
          <div>
            <p class="text-green-400 text-sm font-medium">2FA is enabled</p>
            <p class="text-white/40 text-xs">Your account is protected with two-factor authentication</p>
          </div>
        </div>
      </div>

      <div v-else class="mt-4">
        <button
          @click="setup2FA"
          class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-medium rounded-lg transition-colors"
        >
          Set up 2FA
        </button>
      </div>
    </div>

    <!-- Active Sessions -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-6 mb-6">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
        <div>
          <h2 class="text-lg font-semibold text-white mb-1">Active Sessions</h2>
          <p class="text-white/40 text-sm">Manage devices where you're logged in</p>
        </div>
        <button
          @click="logoutAllDevices"
          class="text-red-400 text-sm hover:text-red-300 transition-colors"
        >
          Log out all devices
        </button>
      </div>

      <div class="space-y-3">
        <div
          v-for="session in activeSessions"
          :key="session.id"
          class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
              <Icon :name="session.icon" class="w-4 h-4 text-white/60" />
            </div>
            <div>
              <p class="text-white text-sm font-medium">{{ session.device }}</p>
              <p class="text-white/40 text-xs">{{ session.location }} • Last active {{ session.lastActive }}</p>
            </div>
          </div>
          <button
            v-if="!session.isCurrent"
            @click="revokeSession(session.id)"
            class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors"
          >
            <Icon name="lucide:log-out" class="w-4 h-4" />
          </button>
          <span v-else class="text-xs text-green-400">Current session</span>
        </div>
      </div>
    </div>

    <!-- Login History -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-6 mb-6">
      <h2 class="text-lg font-semibold text-white mb-4">Recent Login Activity</h2>
      <div class="space-y-3">
        <div
          v-for="login in loginHistory"
          :key="login.id"
          class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full" :class="login.success ? 'bg-green-500/20' : 'bg-red-500/20'">
              <Icon :name="login.success ? 'lucide:log-in' : 'lucide:alert-triangle'" class="w-4 h-4 m-2" :class="login.success ? 'text-green-400' : 'text-red-400'" />
            </div>
            <div>
              <p class="text-white text-sm font-medium">{{ login.device }}</p>
              <p class="text-white/40 text-xs">{{ login.location }} • {{ login.time }}</p>
            </div>
          </div>
          <span
            class="text-xs px-2 py-0.5 rounded-full"
            :class="login.success ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
          >
            {{ login.success ? 'Success' : 'Failed' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Email Notifications -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-6">
      <h2 class="text-lg font-semibold text-white mb-4">Security Notifications</h2>
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white text-sm font-medium">Login alerts</p>
            <p class="text-white/40 text-xs">Get email when a new device logs in</p>
          </div>
          <button
            @click="toggleNotification('loginAlerts')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
            :class="notifications.loginAlerts ? 'bg-red-600' : 'bg-white/20'"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="notifications.loginAlerts ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white text-sm font-medium">Password change alerts</p>
            <p class="text-white/40 text-xs">Get email when password is changed</p>
          </div>
          <button
            @click="toggleNotification('passwordAlerts')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
            :class="notifications.passwordAlerts ? 'bg-red-600' : 'bg-white/20'"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="notifications.passwordAlerts ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>
      </div>
    </div>

    <!-- 2FA Setup Modal -->
    <DialogRoot v-model:open="setupModalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Set up Two-Factor Authentication</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">Scan the QR code with your authenticator app</DialogDescription>
          <div class="flex justify-center mb-4">
            <div class="w-48 h-48 bg-white p-2 rounded-lg">
              <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">
                QR Code Placeholder
              </div>
            </div>
          </div>
          <p class="text-white/60 text-sm text-center mb-4">Or enter this code manually: <span class="text-red-400 font-mono">ABC123DEF456</span></p>
          <div class="mb-4">
            <label class="block text-white/60 text-sm mb-1">Verification Code</label>
            <input
              v-model="verificationCode"
              type="text"
              placeholder="000000"
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-center text-2xl tracking-widest"
            />
          </div>
          <div class="flex gap-3">
            <button @click="setupModalOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">Cancel</button>
            <button @click="enable2FA" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg">Verify & Enable</button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Toast -->
    <div v-if="toastMessage" class="fixed bottom-4 right-4 z-50 animate-slide-up">
      <div :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'" class="px-4 py-3 rounded-lg shadow-lg text-white text-sm">
        {{ toastMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
} from 'radix-vue'

definePageMeta({ layout: 'admin-layout' })

// Two-factor authentication
const twoFAEnabled = ref(false)
const setupModalOpen = ref(false)
const verificationCode = ref('')

const toggle2FA = () => {
  if (twoFAEnabled.value) {
    // Disable 2FA
    twoFAEnabled.value = false
    showToast('Two-factor authentication disabled', 'success')
  } else {
    setup2FA()
  }
}

const setup2FA = () => {
  verificationCode.value = ''
  setupModalOpen.value = true
}

const enable2FA = () => {
  if (verificationCode.value === '123456') { // Mock verification
    twoFAEnabled.value = true
    setupModalOpen.value = false
    showToast('Two-factor authentication enabled', 'success')
  } else {
    showToast('Invalid verification code', 'error')
  }
}

// Active sessions
const activeSessions = ref([
  {
    id: 1,
    device: 'Chrome on Windows',
    location: 'Phnom Penh, Cambodia',
    lastActive: '2 minutes ago',
    icon: 'lucide:computer',
    isCurrent: true,
  },
  {
    id: 2,
    device: 'Safari on iPhone',
    location: 'Phnom Penh, Cambodia',
    lastActive: '2 hours ago',
    icon: 'lucide:smartphone',
    isCurrent: false,
  },
  {
    id: 3,
    device: 'Firefox on MacBook',
    location: 'Bangkok, Thailand',
    lastActive: '3 days ago',
    icon: 'lucide:laptop',
    isCurrent: false,
  },
])

const revokeSession = (id) => {
  activeSessions.value = activeSessions.value.filter(s => s.id !== id)
  showToast('Session revoked', 'success')
}

const logoutAllDevices = () => {
  activeSessions.value = activeSessions.value.filter(s => s.isCurrent)
  showToast('Logged out from all other devices', 'success')
}

// Login history
const loginHistory = ref([
  {
    id: 1,
    device: 'Chrome on Windows',
    location: 'Phnom Penh, Cambodia',
    time: 'Today, 09:23 AM',
    success: true,
  },
  {
    id: 2,
    device: 'Safari on iPhone',
    location: 'Phnom Penh, Cambodia',
    time: 'Yesterday, 8:15 PM',
    success: true,
  },
  {
    id: 3,
    device: 'Firefox on MacBook',
    location: 'Bangkok, Thailand',
    time: 'Apr 1, 2025, 2:30 PM',
    success: true,
  },
  {
    id: 4,
    device: 'Edge on Windows',
    location: 'Unknown',
    time: 'Mar 30, 2025, 11:45 PM',
    success: false,
  },
])

// Notifications
const notifications = reactive({
  loginAlerts: true,
  passwordAlerts: true,
})

const toggleNotification = (key) => {
  notifications[key] = !notifications[key]
  showToast(`${key === 'loginAlerts' ? 'Login alerts' : 'Password change alerts'} ${notifications[key] ? 'enabled' : 'disabled'}`, 'success')
}

// Toast
const toastMessage = ref('')
const toastType = ref('success')
let toastTimeout = null
const showToast = (message, type = 'success') => {
  if (toastTimeout) clearTimeout(toastTimeout)
  toastMessage.value = message
  toastType.value = type
  toastTimeout = setTimeout(() => {
    toastMessage.value = ''
  }, 3000)
}
</script>

<style scoped>
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-slide-up {
  animation: slideUp 0.3s ease-out;
}
</style>