<!-- pages/admin/cinemas.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Cinemas</h1>
        <p class="text-white/50 text-sm mt-1">Manage cinema locations, formats, and pricing</p>
      </div>
      <button @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors shadow-lg shadow-red-500/20">
        <Icon name="lucide:plus" class="w-4 h-4" />
        Add Cinema
      </button>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <Icon name="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" />
          <input v-model="searchQuery" type="text" placeholder="Search by name or address..."
            class="w-full bg-black/40 border border-white/10 rounded-lg pl-9 pr-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500 transition-colors" />
        </div>
        <select v-model="formatFilter"
          class="bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
          <option value="all">All Formats</option>
          <option value="2D">2D</option>
          <option value="3D">3D</option>
          <option value="IMAX">IMAX</option>
          <option value="4DX">4DX</option>
        </select>
      </div>
    </div>

    <!-- Cinemas Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Name</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Address</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Phone</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Format</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="cinema in paginatedCinemas" :key="cinema.id" class="hover:bg-white/5 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center">
                    <Icon name="lucide:building" class="w-4 h-4 text-red-500" />
                  </div>
                  <span class="text-white font-medium">{{ cinema.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-white/70 text-sm max-w-xs truncate">{{ cinema.address }}</td>
              <td class="px-4 py-3 text-white/70 text-sm max-w-xs truncate">{{ cinema.phone }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white/10 text-white/60">
                  {{ cinema.format }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(cinema)"
                    class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                    <Icon name="lucide:edit" class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(cinema)"
                    class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                    <Icon name="lucide:trash-2" class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredCinemas.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-white/40">
                No cinemas found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1"
        class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <div class="text-white/40 text-sm">
          Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredCinemas.length)
          }} of {{ filteredCinemas.length }} cinemas
        </div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
            Previous
          </button>
          <button @click="currentPage++" :disabled="currentPage === totalPages"
            class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Cinema Modal -->
    <DialogRoot v-model:open="modalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl">
          <DialogTitle class="text-xl font-bold text-white mb-2">
            {{ editingCinema ? 'Edit Cinema' : 'Add New Cinema' }}
          </DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            {{ editingCinema ? 'Update cinema details.' : 'Fill in the details to add a new cinema.' }}
          </DialogDescription>

          <form @submit.prevent="saveCinema" class="space-y-4">
            <div>
              <label class="block text-white/60 text-sm mb-1">Cinema Name</label>
              <input v-model="form.name" type="text" required
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="Legend 271 Mega Mall" />
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">Address</label>
              <input v-model="form.address" type="text" required
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="Aeon Mall, 4th Floor, Phnom Penh" />
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">Format</label>
              <select v-model="form.format"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="2D">2D</option>
                <option value="3D">3D</option>
                <option value="IMAX">IMAX</option>
                <option value="4DX">4DX</option>
              </select>
            </div>
            <div>
              <label class="block text-white/60 text-sm mb-1">City</label>
              <input v-model="form.city" type="text"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="Phnom Penh" />
            </div>

            <div>
              <label class="block text-white/60 text-sm mb-1">Phone</label>
              <input v-model="form.phone" type="tel"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
                placeholder="+855 23 123 456" />
            </div>

            <div class="flex gap-3 pt-4">
              <button type="button" @click="modalOpen = false"
                class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">
                Cancel
              </button>
              <button type="submit"
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-colors">
                {{ editingCinema ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Delete Confirmation Dialog -->
    <DialogRoot v-model:open="deleteDialogOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Cinema</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            Are you sure you want to delete <span class="text-red-400">{{ cinemaToDelete?.name }}</span>? This action
            cannot be undone.
          </DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false"
              class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">
              Cancel
            </button>
            <button @click="deleteCinema"
              class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition-colors">
              Delete
            </button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
} from 'radix-vue'

definePageMeta({
  layout: 'admin-layout'
})

const { $api } = useNuxtApp()

// ---------- State ----------
const cinemas = ref([])
const loading = ref(false)
const error = ref(null)

// Search & Filter
const searchQuery = ref('')
const formatFilter = ref('all')

// Pagination
const pageSize = 5
const currentPage = ref(1)

// ---------- Fetch Cinemas from API ----------
const fetchCinemas = async () => {
  loading.value = true
  error.value = null
  try {
    const params = {
      per_page: 100, // fetch many for local filtering/pagination
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (formatFilter.value !== 'all') params.format = formatFilter.value

    const response = await $api('/cinemas', { params })
    // Assuming API returns { data: [...], ... }
    cinemas.value = response.data || response
  } catch (err) {
    console.error('Fetch error:', err)
    error.value = 'Failed to load cinemas'
  } finally {
    loading.value = false
  }
}

// Watch filters and re‑fetch
watch([searchQuery, formatFilter], () => {
  currentPage.value = 1
  fetchCinemas()
})

// ---------- Local filtering & pagination (client side) ----------
const filteredCinemas = computed(() => {
  let result = cinemas.value
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(c =>
      c.name?.toLowerCase().includes(q) || c.address?.toLowerCase().includes(q)
    )
  }
  if (formatFilter.value !== 'all') {
    result = result.filter(c => c.format === formatFilter.value)
  }
  return result
})

const totalPages = computed(() => Math.ceil(filteredCinemas.value.length / pageSize))
const paginatedCinemas = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredCinemas.value.slice(start, start + pageSize)
})

watch([searchQuery, formatFilter], () => {
  currentPage.value = 1
})

// ---------- Modal State ----------
const modalOpen = ref(false)
const editingCinema = ref(null)

const form = reactive({
  name: '',
  address: '',
  format: '2D',
  phone: '',
})

// ---------- Open Create/Edit Modals ----------
const openCreateModal = () => {
  editingCinema.value = null
  form.name = ''
  form.address = ''
  form.format = '2D'
  form.phone = ''
  modalOpen.value = true
}

const openEditModal = (cinema) => {
  editingCinema.value = cinema
  form.name = cinema.name
  form.address = cinema.address
  form.format = cinema.format
  form.phone = cinema.phone || ''
  modalOpen.value = true
}

// ---------- Save Cinema (Create / Update) ----------
const saveCinema = async () => {
  const payload = {
    name: form.name,
    address: form.address,
    format: form.format,
    phone: form.phone,
  }

  try {
    if (editingCinema.value) {
      await $api(`/cinemas/${editingCinema.value.id}`, {
        method: 'PUT',
        body: payload,
      })
    } else {
      await $api('/cinemas', {
        method: 'POST',
        body: payload,
      })
    }
    modalOpen.value = false
    await fetchCinemas() // refresh list
  } catch (err) {
    console.error('Save error:', err)
    alert(err?.data?.message || 'Operation failed')
  }
}

// ---------- Delete Cinema ----------
const deleteDialogOpen = ref(false)
const cinemaToDelete = ref(null)

const confirmDelete = (cinema) => {
  cinemaToDelete.value = cinema
  deleteDialogOpen.value = true
}

const deleteCinema = async () => {
  if (!cinemaToDelete.value) return
  try {
    await $api(`/cinemas/${cinemaToDelete.value.id}`, { method: 'DELETE' })
    deleteDialogOpen.value = false
    cinemaToDelete.value = null
    await fetchCinemas()
  } catch (err) {
    console.error('Delete error:', err)
    alert(err?.data?.message || 'Delete failed')
  }
}

// ---------- Lifecycle ----------
onMounted(() => {
  fetchCinemas()
})
</script>