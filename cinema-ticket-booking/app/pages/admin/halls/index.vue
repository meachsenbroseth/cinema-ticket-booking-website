<!-- pages/admin/halls.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Halls</h1>
        <p class="text-white/50 text-sm mt-1">Manage cinema halls and seat layouts</p>
      </div>
      <button @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors shadow-lg shadow-red-500/20">
        <Icon name="lucide:plus" class="w-4 h-4" />
        Add Hall
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-white/60 text-xs mb-1">Cinema</label>
          <select v-model="filterCinemaId"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All Cinemas</option>
            <option v-for="c in cinemas" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Type</label>
          <select v-model="filterType"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All Types</option>
            <option value="standard">Standard</option>
            <option value="imax">IMAX</option>
            <option value="4dx">4DX</option>
            <option value="vip">VIP</option>
          </select>
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Status</label>
          <select v-model="filterActive"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-if="error"
      class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm flex items-center gap-2">
      <Icon name="lucide:alert-circle" class="w-4 h-4 shrink-0" />
      {{ error }}
      <button @click="fetchHalls" class="ml-auto underline">Retry</button>
    </div>

    <!-- Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Hall</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Cinema</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Type</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Layout</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Seats</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Status</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">

            <!-- Loading -->
            <tr v-if="loading" v-for="i in 5" :key="i">
              <td colspan="7" class="px-4 py-3">
                <div class="animate-pulse h-8 bg-white/5 rounded-lg" />
              </td>
            </tr>

            <template v-else>
              <tr v-for="hall in halls" :key="hall.id" class="hover:bg-white/5 transition-colors">
                <td class="px-4 py-3">
                  <p class="text-white font-medium">{{ hall.name }}</p>
                </td>
                <td class="px-4 py-3 text-white/70 text-sm">{{ hall.cinema?.name }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase"
                    :class="typeClass(hall.type)">
                    {{ hall.type }}
                  </span>
                </td>
                <td class="px-4 py-3 text-white/60 text-sm">
                  {{ hall.total_rows }} rows × {{ hall.seats_per_row }} seats
                </td>
                <td class="px-4 py-3 text-white/70 text-sm">
                  {{ hall.seats_count ?? hall.total_rows * hall.seats_per_row }}
                </td>
                <td class="px-4 py-3">
                  <button @click="toggleActive(hall)"
                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium transition-colors"
                    :class="hall.is_active
                      ? 'bg-green-500/20 text-green-400 hover:bg-green-500/30'
                      : 'bg-red-500/20 text-red-400 hover:bg-red-500/30'">
                    <span class="w-1.5 h-1.5 rounded-full"
                      :class="hall.is_active ? 'bg-green-400' : 'bg-red-400'" />
                    {{ hall.is_active ? 'Active' : 'Inactive' }}
                  </button>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openSeatsModal(hall)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-blue-400 hover:bg-blue-500/10 transition-colors"
                      title="View Seats">
                      <Icon name="lucide:armchair" class="w-4 h-4" />
                    </button>
                    <button @click="openEditModal(hall)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                      <Icon name="lucide:edit" class="w-4 h-4" />
                    </button>
                    <button @click="confirmDelete(hall)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                      <Icon name="lucide:trash-2" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="halls.length === 0">
                <td colspan="7" class="px-4 py-12 text-center">
                  <Icon name="lucide:building-2" class="w-10 h-10 text-white/10 mx-auto mb-2" />
                  <p class="text-white/40 text-sm">No halls found</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <p class="text-white/40 text-sm">
          Showing {{ showingFrom }} to {{ showingTo }} of {{ totalItems }}
        </p>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-sm">
            Previous
          </button>
          <span class="px-3 py-1 text-white/40 text-sm">{{ currentPage }} / {{ lastPage }}</span>
          <button @click="currentPage++" :disabled="currentPage === lastPage"
            class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-sm">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <DialogRoot v-model:open="modalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl max-h-[90vh] overflow-y-auto">
          <DialogTitle class="text-xl font-bold text-white mb-1">
            {{ editingHall ? 'Edit Hall' : 'Add Hall' }}
          </DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-5">
            {{ editingHall ? 'Update hall details.' : 'Create a new hall and auto-generate seats.' }}
          </DialogDescription>

          <div v-if="modalError"
            class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm">
            {{ modalError }}
          </div>

          <form @submit.prevent="saveHall" class="space-y-4">

            <div>
              <label class="block text-white/60 text-sm mb-1">Cinema</label>
              <select v-model="form.cinema_id" required :disabled="!!editingHall"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500 disabled:opacity-50">
                <option value="">Select cinema</option>
                <option v-for="c in cinemas" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-white/60 text-sm mb-1">Hall Name</label>
              <input v-model="form.name" type="text" required placeholder="Hall 1"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" />
            </div>

            <div>
              <label class="block text-white/60 text-sm mb-1">Type</label>
              <select v-model="form.type"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="standard">Standard</option>
                <option value="imax">IMAX</option>
                <option value="4dx">4DX</option>
                <option value="vip">VIP</option>
              </select>
            </div>

            <!-- Seat layout — only for create -->
            <template v-if="!editingHall">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-white/60 text-sm mb-1">Total Rows</label>
                  <input v-model.number="form.total_rows" type="number" min="1" max="26" required
                    class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
                  <p class="text-white/30 text-xs mt-1">A–Z (max 26)</p>
                </div>
                <div>
                  <label class="block text-white/60 text-sm mb-1">Seats per Row</label>
                  <input v-model.number="form.seats_per_row" type="number" min="1" max="30" required
                    class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
                  <p class="text-white/30 text-xs mt-1">Max 30</p>
                </div>
              </div>

              <!-- VIP rows -->
              <div>
                <label class="block text-white/60 text-sm mb-1">
                  VIP Rows
                  <span class="text-white/30 font-normal">(optional)</span>
                </label>
                <input v-model="vipRowsInput" type="text" placeholder="e.g. F,G"
                  class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500" />
                <p class="text-white/30 text-xs mt-1">Comma separated, e.g. F,G for last rows</p>
              </div>

              <!-- Preview -->
              <div v-if="form.total_rows && form.seats_per_row"
                class="p-3 bg-white/5 rounded-xl text-sm text-white/60">
                <Icon name="lucide:info" class="w-4 h-4 inline mr-1 text-blue-400" />
                Will generate
                <span class="text-white font-semibold">{{ form.total_rows * form.seats_per_row }}</span>
                seats
                <span v-if="vipRowsInput" class="text-yellow-400">
                  (rows {{ vipRowsInput.toUpperCase() }} → VIP)
                </span>
              </div>
            </template>

            <div>
              <label class="flex items-center gap-2 cursor-pointer">
                <div @click="form.is_active = !form.is_active"
                  class="w-4 h-4 rounded border flex items-center justify-center transition-all shrink-0"
                  :class="form.is_active ? 'bg-red-600 border-red-600' : 'border-white/20 bg-white/5'">
                  <Icon v-if="form.is_active" name="lucide:check" class="w-3 h-3 text-white" />
                </div>
                <span class="text-white/60 text-sm">Active</span>
              </label>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modalOpen = false"
                class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving"
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                <Icon v-if="saving" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
                {{ saving ? 'Saving...' : (editingHall ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Seats Viewer Modal -->
    <DialogRoot v-model:open="seatsModalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl max-h-[90vh] overflow-y-auto">
          <DialogTitle class="text-xl font-bold text-white mb-1">
            {{ viewingHall?.name }} — Seat Map
          </DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-5">
            {{ viewingHall?.cinema?.name }} ·
            {{ viewingHall?.total_rows }} rows ×
            {{ viewingHall?.seats_per_row }} seats
          </DialogDescription>

          <div v-if="seatsLoading" class="flex justify-center py-10">
            <Icon name="lucide:loader-circle" class="w-8 h-8 text-red-500 animate-spin" />
          </div>

          <div v-else class="overflow-x-auto">
            <div v-for="(rowSeats, row) in seatsByRow" :key="row" class="flex items-center gap-1.5 mb-2">
              <span class="w-6 text-center text-white/30 text-xs font-semibold shrink-0">{{ row }}</span>
              <button v-for="seat in rowSeats" :key="seat.id"
                class="w-7 h-6 rounded-t-lg text-[9px] font-semibold transition-all"
                :class="seat.type === 'vip'
                  ? 'bg-yellow-500/30 border border-yellow-500/40 text-yellow-400'
                  : 'bg-white/10 border border-white/10 text-white/50'">
                {{ seat.number }}
              </button>
            </div>
          </div>

          <!-- Legend -->
          <div class="flex items-center gap-6 mt-5 pt-4 border-t border-white/10">
            <div class="flex items-center gap-2">
              <div class="w-6 h-5 rounded-t-lg bg-white/10 border border-white/10" />
              <span class="text-white/40 text-xs">Standard</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-6 h-5 rounded-t-lg bg-yellow-500/30 border border-yellow-500/40" />
              <span class="text-white/40 text-xs">VIP</span>
            </div>
          </div>

          <button @click="seatsModalOpen = false"
            class="mt-4 w-full py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors text-sm">
            Close
          </button>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Delete Confirm -->
    <DialogRoot v-model:open="deleteDialogOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Hall</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            Delete <span class="text-red-400 font-medium">{{ hallToDelete?.name }}</span> from
            {{ hallToDelete?.cinema?.name }}? All seats will be deleted too.
          </DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false"
              class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">
              Cancel
            </button>
            <button @click="deleteHall" :disabled="deleting"
              class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white font-semibold rounded-lg flex items-center justify-center gap-2">
              <Icon v-if="deleting" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>
  </div>
</template>

<script setup>
import {
  DialogRoot, DialogPortal, DialogOverlay,
  DialogContent, DialogTitle, DialogDescription,
} from 'radix-vue'

definePageMeta({ layout: 'admin-layout', middleware: 'admin' })

const { $api } = useNuxtApp()

// ── State ────────────────────────────────────────────────
const halls       = ref([])
const cinemas     = ref([])
const loading     = ref(false)
const error       = ref(null)
const totalItems  = ref(0)
const lastPage    = ref(1)
const currentPage = ref(1)
const pageSize    = 10

// ── Filters ──────────────────────────────────────────────
const filterCinemaId = ref('all')
const filterType     = ref('all')
const filterActive   = ref('all')

// ── Fetch ────────────────────────────────────────────────
const fetchHalls = async () => {
  loading.value = true
  error.value   = null
  try {
    const params = { per_page: pageSize, page: currentPage.value }
    if (filterCinemaId.value !== 'all') params.cinema_id = filterCinemaId.value
    if (filterType.value     !== 'all') params.type      = filterType.value
    if (filterActive.value   !== 'all') params.is_active = filterActive.value

    const response   = await $api('/halls', { params })
    halls.value      = response.data
    totalItems.value = response.total
    lastPage.value   = response.last_page
  } catch (err) {
    error.value = 'Failed to load halls'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const fetchCinemas = async () => {
  try {
    const response = await $api('/cinemas/dropdown')
    cinemas.value  = Array.isArray(response) ? response : response.data ?? []
  } catch (err) {
    console.error('Failed to load cinemas:', err)
  }
}

watch([filterCinemaId, filterType, filterActive], () => {
  currentPage.value = 1
  fetchHalls()
})

watch(currentPage, fetchHalls)

onMounted(async () => {
  await Promise.all([fetchHalls(), fetchCinemas()])
})

// ── Modal ────────────────────────────────────────────────
const modalOpen   = ref(false)
const editingHall = ref(null)
const saving      = ref(false)
const modalError  = ref(null)
const vipRowsInput = ref('')

const form = reactive({
  cinema_id:    '',
  name:         '',
  type:         'standard',
  total_rows:   7,
  seats_per_row:12,
  is_active:    true,
})

const openCreateModal = () => {
  editingHall.value = null
  modalError.value  = null
  vipRowsInput.value = ''
  form.cinema_id     = cinemas.value[0]?.id ?? ''
  form.name          = ''
  form.type          = 'standard'
  form.total_rows    = 7
  form.seats_per_row = 12
  form.is_active     = true
  modalOpen.value    = true
}

const openEditModal = (hall) => {
  editingHall.value  = hall
  modalError.value   = null
  vipRowsInput.value = ''
  form.cinema_id     = hall.cinema_id
  form.name          = hall.name
  form.type          = hall.type
  form.total_rows    = hall.total_rows
  form.seats_per_row = hall.seats_per_row
  form.is_active     = !!hall.is_active
  modalOpen.value    = true
}

const saveHall = async () => {
  saving.value     = true
  modalError.value = null
  try {
    const body = { ...form }

    // Add vip_rows only on create
    if (!editingHall.value && vipRowsInput.value) {
      body.vip_rows = vipRowsInput.value
        .split(',')
        .map(r => r.trim().toUpperCase())
        .filter(Boolean)
    }

    if (editingHall.value) {
      await $api(`/halls/${editingHall.value.id}`, { method: 'PUT', body })
    } else {
      await $api('/halls', { method: 'POST', body })
    }

    modalOpen.value = false
    await fetchHalls()
  } catch (err) {
    modalError.value = err?.data?.message || 'Something went wrong'
  } finally {
    saving.value = false
  }
}

// ── Toggle Active ────────────────────────────────────────
const toggleActive = async (hall) => {
  try {
    await $api(`/halls/${hall.id}/toggle-active`, { method: 'PATCH' })
    hall.is_active = !hall.is_active
  } catch (err) {
    console.error('Toggle failed:', err)
  }
}

// ── Seats Viewer ─────────────────────────────────────────
const seatsModalOpen = ref(false)
const viewingHall    = ref(null)
const seatsByRow     = ref({})
const seatsLoading   = ref(false)

const openSeatsModal = async (hall) => {
  viewingHall.value  = hall
  seatsModalOpen.value = true
  seatsLoading.value = true
  seatsByRow.value   = {}
  try {
    const response = await $api(`/halls/${hall.id}`)
    seatsByRow.value = response.seats ?? {}
  } catch (err) {
    console.error('Failed to load seats:', err)
  } finally {
    seatsLoading.value = false
  }
}

// ── Delete ───────────────────────────────────────────────
const deleteDialogOpen = ref(false)
const hallToDelete     = ref(null)
const deleting         = ref(false)

const confirmDelete = (hall) => {
  hallToDelete.value    = hall
  deleteDialogOpen.value = true
}

const deleteHall = async () => {
  if (!hallToDelete.value) return
  deleting.value = true
  try {
    await $api(`/halls/${hallToDelete.value.id}`, { method: 'DELETE' })
    deleteDialogOpen.value = false
    hallToDelete.value     = null
    await fetchHalls()
  } catch (err) {
    console.error('Delete failed:', err)
  } finally {
    deleting.value = false
  }
}

// ── Helpers ──────────────────────────────────────────────
const typeClass = (type) => ({
  standard: 'bg-white/10 text-white/60',
  imax:     'bg-blue-500/20 text-blue-400',
  '4dx':    'bg-purple-500/20 text-purple-400',
  vip:      'bg-yellow-500/20 text-yellow-400',
}[type] ?? 'bg-white/10 text-white/50')

const showingFrom = computed(() => (currentPage.value - 1) * pageSize + 1)
const showingTo   = computed(() => Math.min(currentPage.value * pageSize, totalItems.value))
</script>