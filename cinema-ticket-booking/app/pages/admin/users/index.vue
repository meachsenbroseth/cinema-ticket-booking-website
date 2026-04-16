<script setup>
import {
  DialogRoot, DialogPortal, DialogOverlay,
  DialogContent, DialogTitle, DialogDescription,
} from 'radix-vue'

definePageMeta({ layout: 'admin-layout', middleware: 'admin' })

const { $api } = useNuxtApp()

// ── State ────────────────────────────────────────────────
const users       = ref([])
const loading     = ref(false)
const error       = ref(null)
const searchQuery = ref('')
const roleFilter  = ref('all')
const currentPage = ref(1)
const pageSize    = ref(10)
const totalUsers  = ref(0)
const lastPage    = ref(1)

// ── Fetch Users ──────────────────────────────────────────
const fetchUsers = async () => {
  loading.value = true
  error.value   = null
  try {
    const params = {
      per_page: pageSize.value,
      page:     currentPage.value,
    }
    if (searchQuery.value) params.search  = searchQuery.value
    if (roleFilter.value !== 'all') params.role = roleFilter.value

    const response = await $api('/admin/users', { params })
    users.value     = response.data
    totalUsers.value = response.total
    lastPage.value  = response.last_page
  } catch (err) {
    error.value = 'Failed to load users'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Reset page and refetch when filters change
watch([searchQuery, roleFilter], () => {
  currentPage.value = 1
  fetchUsers()
})

watch(currentPage, fetchUsers)

onMounted(fetchUsers)

// ── Modal ────────────────────────────────────────────────
const modalOpen   = ref(false)
const editingUser = ref(null)
const saving      = ref(false)
const modalError  = ref(null)

const form = reactive({
  name:     '',
  email:    '',
  role:     'user',
  password: '',
})

const openCreateModal = () => {
  editingUser.value = null
  modalError.value  = null
  form.name     = ''
  form.email    = ''
  form.role     = 'user'
  form.password = ''
  modalOpen.value = true
}

const openEditModal = (user) => {
  editingUser.value = user
  modalError.value  = null
  form.name     = user.name
  form.email    = user.email
  form.role     = user.role
  form.password = ''
  modalOpen.value = true
}

const saveUser = async () => {
  saving.value     = true
  modalError.value = null
  try {
    if (editingUser.value) {
      // Update role only
      await $api(`/admin/users/${editingUser.value.id}/role`, {
        method: 'PATCH',
        body:   { role: form.role },
      })
    } else {
      // Create via auth register
      await $api('/auth/register', {
        method: 'POST',
        body: {
          name:                  form.name,
          email:                 form.email,
          password:              form.password,
          password_confirmation: form.password,
          role:                  form.role,
        },
      })
    }
    modalOpen.value = false
    await fetchUsers()
  } catch (err) {
    modalError.value = err?.data?.message || 'Something went wrong'
  } finally {
    saving.value = false
  }
}

// ── Delete ───────────────────────────────────────────────
const deleteDialogOpen = ref(false)
const userToDelete     = ref(null)
const deleting         = ref(false)

const confirmDelete = (user) => {
  userToDelete.value     = user
  deleteDialogOpen.value = true
}

const deleteUser = async () => {
  if (!userToDelete.value) return
  deleting.value = true
  try {
    await $api(`/admin/users/${userToDelete.value.id}`, { method: 'DELETE' })
    deleteDialogOpen.value = false
    userToDelete.value     = null
    await fetchUsers()
  } catch (err) {
    console.error('Delete failed:', err)
  } finally {
    deleting.value = false
  }
}

// ── Helpers ──────────────────────────────────────────────
const formatDate = (iso) =>
  new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const showingFrom = computed(() => (currentPage.value - 1) * pageSize.value + 1)
const showingTo   = computed(() => Math.min(currentPage.value * pageSize.value, totalUsers.value))
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Users</h1>
        <p class="text-white/50 text-sm mt-1">Manage system users and their roles</p>
      </div>
      <button @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors shadow-lg shadow-red-500/20">
        <Icon name="lucide:user-plus" class="w-4 h-4" />
        Add User
      </button>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <Icon name="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" />
          <input v-model="searchQuery" type="text" placeholder="Search by name or email..."
            class="w-full bg-black/40 border border-white/10 rounded-lg pl-9 pr-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500 transition-colors" />
        </div>
        <select v-model="roleFilter"
          class="bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
          <option value="all">All Roles</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
      </div>
    </div>

    <!-- Error -->
    <div v-if="error" class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm flex items-center gap-2">
      <Icon name="lucide:alert-circle" class="w-4 h-4 shrink-0" />
      {{ error }}
      <button @click="fetchUsers" class="ml-auto underline hover:no-underline">Retry</button>
    </div>

    <!-- Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">User</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Email</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Role</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Bookings</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Joined</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <!-- Loading skeleton -->
            <tr v-if="loading" v-for="i in 5" :key="i">
              <td colspan="6" class="px-4 py-3">
                <div class="animate-pulse h-8 bg-white/5 rounded-lg" />
              </td>
            </tr>

            <!-- Users -->
            <template v-else>
              <tr v-for="user in users" :key="user.id" class="hover:bg-white/5 transition-colors">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-900/40 border border-red-500/30 flex items-center justify-center text-white text-xs font-bold shrink-0">
                      {{ user.name?.charAt(0).toUpperCase() }}
                    </div>
                    <span class="text-white font-medium">{{ user.name }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-white/70 text-sm">{{ user.email }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    :class="user.role === 'admin' ? 'bg-red-500/20 text-red-400' : 'bg-white/10 text-white/60'">
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-4 py-3 text-white/50 text-sm">
                  {{ user.bookings_count ?? 0 }}
                </td>
                <td class="px-4 py-3 text-white/50 text-sm">{{ formatDate(user.created_at) }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openEditModal(user)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                      <Icon name="lucide:edit" class="w-4 h-4" />
                    </button>
                    <button @click="confirmDelete(user)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                      <Icon name="lucide:trash-2" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty -->
              <tr v-if="users.length === 0">
                <td colspan="6" class="px-4 py-12 text-center">
                  <Icon name="lucide:users" class="w-10 h-10 text-white/10 mx-auto mb-2" />
                  <p class="text-white/40 text-sm">No users found</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <p class="text-white/40 text-sm">
          Showing {{ showingFrom }} to {{ showingTo }} of {{ totalUsers }} users
        </p>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-sm">
            Previous
          </button>
          <span class="px-3 py-1 text-white/40 text-sm">{{ currentPage }} / {{ lastPage }}</span>
          <button @click="currentPage++" :disabled="currentPage === lastPage"
            class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-sm">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <DialogRoot v-model:open="modalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl">
          <DialogTitle class="text-xl font-bold text-white mb-1">
            {{ editingUser ? 'Edit User' : 'Add New User' }}
          </DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            {{ editingUser ? 'Update user role.' : 'Create a new user account.' }}
          </DialogDescription>

          <!-- Modal Error -->
          <div v-if="modalError" class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm">
            {{ modalError }}
          </div>

          <form @submit.prevent="saveUser" class="space-y-4">
            <div v-if="!editingUser">
              <label class="block text-white/60 text-sm mb-1">Full Name</label>
              <input v-model="form.name" type="text" required placeholder="John Doe"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" />
            </div>
            <div v-if="!editingUser">
              <label class="block text-white/60 text-sm mb-1">Email</label>
              <input v-model="form.email" type="email" required placeholder="john@example.com"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" />
            </div>
            <div v-if="editingUser" class="p-3 bg-white/5 rounded-lg">
              <p class="text-white font-medium">{{ editingUser.name }}</p>
              <p class="text-white/40 text-sm">{{ editingUser.email }}</p>
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">Role</label>
              <select v-model="form.role"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div v-if="!editingUser">
              <label class="block text-white/60 text-sm mb-1">Password</label>
              <input v-model="form.password" type="password" required placeholder="Min. 8 characters"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" />
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modalOpen = false"
                class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving"
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                <Icon v-if="saving" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
                {{ saving ? 'Saving...' : (editingUser ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Delete Confirm -->
    <DialogRoot v-model:open="deleteDialogOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete User</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            Are you sure you want to delete
            <span class="text-red-400 font-medium">{{ userToDelete?.name }}</span>?
            This action cannot be undone.
          </DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false"
              class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">
              Cancel
            </button>
            <button @click="deleteUser" :disabled="deleting"
              class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
              <Icon v-if="deleting" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>
  </div>
</template>