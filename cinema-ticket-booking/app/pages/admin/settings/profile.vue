<!-- pages/admin/profile.vue -->
<template>
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">Profile Settings</h1>
      <p class="text-white/50 text-sm mt-1">Manage your account information and password</p>
    </div>

    <!-- Profile Form -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-6 mb-6">
      <div class="flex items-center gap-4 mb-6 pb-4 border-b border-white/10">
        <div class="relative">
          <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
            {{ form.name ? form.name.charAt(0).toUpperCase() : 'A' }}
          </div>
          <button
            @click="triggerAvatarUpload"
            class="absolute bottom-0 right-0 p-1.5 bg-red-600 hover:bg-red-500 rounded-full transition-colors"
          >
            <Icon name="lucide:camera" class="w-3.5 h-3.5 text-white" />
          </button>
          <input
            ref="avatarInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleAvatarUpload"
          />
        </div>
        <div>
          <p class="text-white font-medium">{{ form.name }}</p>
          <p class="text-white/40 text-sm">{{ form.email }}</p>
        </div>
      </div>

      <form @submit.prevent="saveProfile" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-white/60 text-sm mb-1">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
              placeholder="Your name"
            />
          </div>
          <div>
            <label class="block text-white/60 text-sm mb-1">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
              placeholder="admin@example.com"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-white/60 text-sm mb-1">Phone Number</label>
            <input
              v-model="form.phone"
              type="tel"
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
              placeholder="+855 12 345 678"
            />
          </div>
          <div>
            <label class="block text-white/60 text-sm mb-1">Role</label>
            <input
              :value="form.role"
              type="text"
              disabled
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white/50 cursor-not-allowed"
            />
          </div>
        </div>

        <div>
          <label class="block text-white/60 text-sm mb-1">Bio</label>
          <textarea
            v-model="form.bio"
            rows="3"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
            placeholder="Tell us about yourself..."
          ></textarea>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="px-6 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-colors"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>

    <!-- Change Password Section -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-6 mb-6">
      <h2 class="text-lg font-semibold text-white mb-4">Change Password</h2>
      <form @submit.prevent="changePassword" class="space-y-4">
        <div>
          <label class="block text-white/60 text-sm mb-1">Current Password</label>
          <div class="relative">
            <input
              v-model="passwordForm.currentPassword"
              :type="showCurrentPassword ? 'text' : 'password'"
              required
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500 pr-10"
              placeholder="Enter current password"
            />
            <button
              type="button"
              @click="showCurrentPassword = !showCurrentPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60"
            >
              <Icon :name="showCurrentPassword ? 'lucide:eye-off' : 'lucide:eye'" class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div>
          <label class="block text-white/60 text-sm mb-1">New Password</label>
          <div class="relative">
            <input
              v-model="passwordForm.newPassword"
              :type="showNewPassword ? 'text' : 'password'"
              required
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500 pr-10"
              placeholder="Enter new password"
            />
            <button
              type="button"
              @click="showNewPassword = !showNewPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60"
            >
              <Icon :name="showNewPassword ? 'lucide:eye-off' : 'lucide:eye'" class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div>
          <label class="block text-white/60 text-sm mb-1">Confirm New Password</label>
          <div class="relative">
            <input
              v-model="passwordForm.confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              required
              class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500 pr-10"
              placeholder="Confirm new password"
            />
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60"
            >
              <Icon :name="showConfirmPassword ? 'lucide:eye-off' : 'lucide:eye'" class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            class="px-6 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-colors"
          >
            Update Password
          </button>
        </div>
      </form>
    </div>

    <!-- Danger Zone -->
    <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-6">
      <h2 class="text-lg font-semibold text-red-400 mb-2">Danger Zone</h2>
      <p class="text-white/40 text-sm mb-4">Once you delete your account, there is no going back. Please be certain.</p>
      <button
        @click="confirmDeleteAccount"
        class="px-4 py-2 border border-red-500/50 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors text-sm font-medium"
      >
        Delete Account
      </button>
    </div>

    <!-- Success/Error Toast (simple alert for demo) -->
    <div v-if="toastMessage" class="fixed bottom-4 right-4 z-50 animate-slide-up">
      <div :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'" class="px-4 py-3 rounded-lg shadow-lg text-white text-sm">
        {{ toastMessage }}
      </div>
    </div>

    <!-- Delete Account Confirmation Dialog -->
    <DialogRoot v-model:open="deleteDialogOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Account</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            Are you absolutely sure? This action cannot be undone. All your data will be permanently removed.
          </DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">Cancel</button>
            <button @click="deleteAccount" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg">Yes, Delete</button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>
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
import { useAuthStore } from '~/stores/authStore'

definePageMeta({ layout: 'admin-layout' })

const { $api } = useNuxtApp()
const authStore = useAuthStore()

// ---------- Profile Form ----------
const avatarInput = ref(null)
const loading = ref(false)
const form = reactive({
  name: '',
  email: '',
  phone: '',
  role: '',
  bio: '',
  avatar: null,
  avatarFile: null, // for file upload
})

// ---------- Fetch Profile from API ----------
const fetchProfile = async () => {
  loading.value = true
  try {
    const response = await $api('/user/profile')
    // Map API fields to form fields
    form.name = response.name || ''
    form.email = response.email || ''
    form.phone = response.phone || ''
    form.role = response.role === 'admin' ? 'Super Admin' : response.role || 'Admin'
    form.bio = response.bio || ''
    form.avatar = response.avatar || null
  } catch (err) {
    console.error('Failed to fetch profile:', err)
    showToast('Failed to load profile', 'error')
  } finally {
    loading.value = false
  }
}

// ---------- Save Profile ----------
const saveProfile = async () => {
  const formData = new FormData()
  formData.append('name', form.name)
  formData.append('email', form.email)
  if (form.phone) formData.append('phone', form.phone)
  if (form.bio) formData.append('bio', form.bio)
  if (form.avatarFile) formData.append('avatar', form.avatarFile)

  try {
    const response = await $api('/user/profile', {
      method: 'POST',
      body: formData,
    })
    // Update auth store with new data
    if (authStore) {
      authStore.user = { ...authStore.user, ...response }
    }
    showToast('Profile updated successfully!', 'success')
  } catch (err) {
    console.error('Save failed:', err)
    showToast(err?.data?.message || 'Update failed', 'error')
  }
}

// ---------- Avatar Upload ----------
const triggerAvatarUpload = () => {
  avatarInput.value?.click()
}

const handleAvatarUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.avatarFile = file
    // Preview
    const reader = new FileReader()
    reader.onload = (e) => {
      form.avatar = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// ---------- Change Password ----------
const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const changePassword = async () => {
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    showToast('New passwords do not match!', 'error')
    return
  }
  if (passwordForm.newPassword.length < 6) {
    showToast('Password must be at least 6 characters', 'error')
    return
  }

  try {
    await $api('/change-password', {
      method: 'POST',
      body: {
        current_password: passwordForm.currentPassword,
        new_password: passwordForm.newPassword,
        new_password_confirmation: passwordForm.confirmPassword,
      },
    })
    showToast('Password changed successfully!', 'success')
    // Clear form
    passwordForm.currentPassword = ''
    passwordForm.newPassword = ''
    passwordForm.confirmPassword = ''
  } catch (err) {
    console.error('Password change failed:', err)
    showToast(err?.data?.message || 'Failed to change password', 'error')
  }
}

// ---------- Delete Account ----------
const deleteDialogOpen = ref(false)
const confirmDeleteAccount = () => {
  deleteDialogOpen.value = true
}

const deleteAccount = async () => {
  try {
    await $api('/user/delete', { method: 'DELETE' })
    showToast('Account deleted. Redirecting...', 'error')
    setTimeout(() => {
      authStore.logout()
      navigateTo('/login')
    }, 1500)
  } catch (err) {
    console.error('Delete failed:', err)
    showToast(err?.data?.message || 'Failed to delete account', 'error')
    deleteDialogOpen.value = false
  }
}

// ---------- Toast ----------
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

// ---------- Lifecycle ----------
onMounted(() => {
  fetchProfile()
})
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