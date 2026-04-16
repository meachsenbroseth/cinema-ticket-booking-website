<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Showtimes</h1>
        <p class="text-white/50 text-sm mt-1">Manage movie schedules and time slots</p>
      </div>
      <button @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors shadow-lg shadow-red-500/20">
        <Icon name="lucide:plus" class="w-4 h-4" />
        Add Showtime
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-white/60 text-xs mb-1">Movie</label>
          <select v-model="filterMovieId"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All Movies</option>
            <option v-for="movie in movies" :key="movie.id" :value="movie.id">{{ movie.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Cinema</label>
          <select v-model="filterCinemaId"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All Cinemas</option>
            <option v-for="cinema in cinemas" :key="cinema.id" :value="cinema.id">{{ cinema.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Date</label>
          <input v-model="filterDate" type="date"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-if="error" class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm flex items-center gap-2">
      <Icon name="lucide:alert-circle" class="w-4 h-4 shrink-0" />
      {{ error }}
      <button @click="fetchShowtimes" class="ml-auto underline">Retry</button>
    </div>

    <!-- Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Movie</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Cinema · Hall</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Date</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Time</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Format</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Price</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Status</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">

            <!-- Loading -->
            <tr v-if="loading" v-for="i in 5" :key="i">
              <td colspan="8" class="px-4 py-3">
                <div class="animate-pulse h-8 bg-white/5 rounded-lg" />
              </td>
            </tr>

            <template v-else>
              <tr v-for="showtime in showtimes" :key="showtime.id" class="hover:bg-white/5 transition-colors">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img v-if="showtime.movie?.poster" :src="showtime.movie.poster"
                      class="w-8 h-10 object-cover rounded shrink-0" />
                    <div v-else class="w-8 h-10 rounded bg-white/10 flex items-center justify-center shrink-0">
                      <Icon name="lucide:film" class="w-4 h-4 text-white/30" />
                    </div>
                    <span class="text-white font-medium text-sm">{{ showtime.movie?.title }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <p class="text-white/70 text-sm">{{ showtime.hall?.cinema?.name }}</p>
                  <p class="text-white/30 text-xs">{{ showtime.hall?.name }}</p>
                </td>
                <td class="px-4 py-3 text-white/70 text-sm">{{ formatDate(showtime.show_date) }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 bg-white/10 rounded text-xs text-white/70">
                    {{ formatTime(showtime.show_time) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 bg-white/10 rounded text-xs text-white/60 uppercase">
                    {{ showtime.format }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <p class="text-white/70 text-sm">${{ showtime.price_standard }}</p>
                  <p class="text-yellow-400/70 text-xs">VIP ${{ showtime.price_vip }}</p>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize"
                    :class="statusClass(showtime.status)">
                    {{ showtime.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openEditModal(showtime)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                      <Icon name="lucide:edit" class="w-4 h-4" />
                    </button>
                    <button @click="confirmDelete(showtime)"
                      class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                      <Icon name="lucide:trash-2" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty -->
              <tr v-if="showtimes.length === 0">
                <td colspan="8" class="px-4 py-12 text-center">
                  <Icon name="lucide:calendar-x" class="w-10 h-10 text-white/10 mx-auto mb-2" />
                  <p class="text-white/40 text-sm">No showtimes found</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <p class="text-white/40 text-sm">Showing {{ showingFrom }} to {{ showingTo }} of {{ totalItems }}</p>
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
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl max-h-[90vh] overflow-y-auto">
          <DialogTitle class="text-xl font-bold text-white mb-1">
            {{ editingShowtime ? 'Edit Showtime' : 'Add Showtime' }}
          </DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-5">
            {{ editingShowtime ? 'Update showtime details.' : 'Schedule a new showtime.' }}
          </DialogDescription>

          <!-- Modal Error -->
          <div v-if="modalError" class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-400 text-sm">
            {{ modalError }}
          </div>

          <form @submit.prevent="saveShowtime" class="space-y-4">

            <!-- Movie -->
            <div>
              <label class="block text-white/60 text-sm mb-1">Movie</label>
              <select v-model="form.movie_id" required
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="">Select movie</option>
                <option v-for="movie in movies" :key="movie.id" :value="movie.id">{{ movie.title }}</option>
              </select>
            </div>

            <!-- Cinema → Hall -->
            <div>
              <label class="block text-white/60 text-sm mb-1">Cinema</label>
              <select @change="fetchHalls($event.target.value)"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="">Select cinema</option>
                <option v-for="cinema in cinemas" :key="cinema.id" :value="cinema.id">{{ cinema.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-white/60 text-sm mb-1">Hall</label>
              <select v-model="form.hall_id" required
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="">Select hall</option>
                <option v-for="hall in halls" :key="hall.id" :value="hall.id">{{ hall.name }} ({{ hall.type }})</option>
              </select>
            </div>

            <!-- Date + Time -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-white/60 text-sm mb-1">Date</label>
                <input v-model="form.show_date" type="date" required
                  class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">Time</label>
                <input v-model="form.show_time" type="time" required
                  class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
              </div>
            </div>

            <!-- Format -->
            <div>
              <label class="block text-white/60 text-sm mb-1">Format</label>
              <select v-model="form.format"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="2d">2D</option>
                <option value="3d">3D</option>
                <option value="imax">IMAX</option>
                <option value="4dx">4DX</option>
              </select>
            </div>

            <!-- Prices -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-white/60 text-sm mb-1">Standard Price ($)</label>
                <input v-model="form.price_standard" type="number" step="0.01" min="0" required
                  class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
              </div>
              <div>
                <label class="block text-white/60 text-sm mb-1">VIP Price ($)</label>
                <input v-model="form.price_vip" type="number" step="0.01" min="0" required
                  class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-white/60 text-sm mb-1">Status</label>
              <select v-model="form.status"
                class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="modalOpen = false"
                class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5 transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="saving"
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                <Icon v-if="saving" name="lucide:loader-circle" class="w-4 h-4 animate-spin" />
                {{ saving ? 'Saving...' : (editingShowtime ? 'Update' : 'Create') }}
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
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Showtime</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">
            Delete
            <span class="text-white font-medium">
              {{ showtimeToDelete?.movie?.title }} — {{ formatTime(showtimeToDelete?.show_time) }} on {{ formatDate(showtimeToDelete?.show_date) }}
            </span>?
            This cannot be undone.
          </DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false"
              class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">
              Cancel
            </button>
            <button @click="deleteShowtime" :disabled="deleting"
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
const showtimes    = ref([])
const movies       = ref([])
const cinemas      = ref([])
const halls        = ref([])
const loading      = ref(false)
const error        = ref(null)
const totalItems   = ref(0)
const lastPage     = ref(1)
const currentPage  = ref(1)
const pageSize     = 10

// ── Filters ──────────────────────────────────────────────
const filterMovieId  = ref('all')
const filterCinemaId = ref('all')
const filterDate     = ref('')

// ── Fetch Showtimes ──────────────────────────────────────
const fetchShowtimes = async () => {
  loading.value = true
  error.value   = null
  try {
    const params = {
      per_page: pageSize,
      page:     currentPage.value,
    }
    if (filterMovieId.value  !== 'all') params.movie_id  = filterMovieId.value
    if (filterCinemaId.value !== 'all') params.cinema_id = filterCinemaId.value
    if (filterDate.value)               params.date       = filterDate.value

    const response     = await $api('/showtimes', { params })
    showtimes.value    = response.data
    totalItems.value   = response.total
    lastPage.value     = response.last_page
  } catch (err) {
    error.value = 'Failed to load showtimes'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// ── Fetch Movies & Cinemas for dropdowns ─────────────────
const fetchDropdowns = async () => {
  try {
    const [moviesRes, cinemasRes] = await Promise.all([
      $api('/movies', { params: { per_page: 100 } }),
      $api('/cinemas/dropdown'),
    ])
    movies.value  = moviesRes.data  ?? []
    cinemas.value = Array.isArray(cinemasRes) ? cinemasRes : cinemasRes.data ?? []
  } catch (err) {
    console.error('Failed to load dropdowns:', err)
  }
}

// Fetch halls when cinema changes in form
const fetchHalls = async (cinemaId) => {
  if (!cinemaId) return
  try {
    const response = await $api('/halls', { params: { cinema_id: cinemaId } })
    halls.value = response.data ?? []
    form.hall_id = halls.value[0]?.id ?? ''
  } catch (err) {
    console.error('Failed to load halls:', err)
  }
}

watch([filterMovieId, filterCinemaId, filterDate], () => {
  currentPage.value = 1
  fetchShowtimes()
})

watch(currentPage, fetchShowtimes)

onMounted(async () => {
  await Promise.all([fetchShowtimes(), fetchDropdowns()])
})

// ── Modal ────────────────────────────────────────────────
const modalOpen       = ref(false)
const editingShowtime = ref(null)
const saving          = ref(false)
const modalError      = ref(null)

const form = reactive({
  movie_id:       '',
  hall_id:        '',
  show_date:      '',
  show_time:      '',
  format:         '2d',
  price_standard: 5.50,
  price_vip:      9.00,
  status:         'scheduled',
})

watch(() => form.hall_id ? cinemas.value.find(c =>
  halls.value.find(h => h.id === form.hall_id && h.cinema_id === c.id)
)?.id : null, (cinemaId) => {
  if (cinemaId) fetchHalls(cinemaId)
})

const openCreateModal = () => {
  editingShowtime.value = null
  modalError.value      = null
  form.movie_id         = movies.value[0]?.id ?? ''
  form.hall_id          = ''
  form.show_date        = new Date().toISOString().slice(0, 10)
  form.show_time        = '19:00'
  form.format           = '2d'
  form.price_standard   = 5.50
  form.price_vip        = 9.00
  form.status           = 'scheduled'
  modalOpen.value       = true
}

const openEditModal = (showtime) => {
  editingShowtime.value = showtime
  modalError.value      = null
  form.movie_id         = showtime.movie_id
  form.hall_id          = showtime.hall_id
  form.show_date        = showtime.show_date
  form.show_time        = showtime.show_time?.slice(0, 5) // "19:00:00" → "19:00"
  form.format           = showtime.format
  form.price_standard   = showtime.price_standard
  form.price_vip        = showtime.price_vip
  form.status           = showtime.status
  modalOpen.value       = true

  // Load halls for this showtime's cinema
  if (showtime.hall?.cinema?.id) {
    fetchHalls(showtime.hall.cinema.id)
  }
}

const saveShowtime = async () => {
  saving.value     = true
  modalError.value = null
  try {
    if (editingShowtime.value) {
      await $api(`/showtimes/${editingShowtime.value.id}`, {
        method: 'PUT',
        body:   form,
      })
    } else {
      await $api('/showtimes', {
        method: 'POST',
        body:   form,
      })
    }
    modalOpen.value = false
    await fetchShowtimes()
  } catch (err) {
    modalError.value = err?.data?.message || 'Something went wrong'
  } finally {
    saving.value = false
  }
}

// ── Delete ───────────────────────────────────────────────
const deleteDialogOpen  = ref(false)
const showtimeToDelete  = ref(null)
const deleting          = ref(false)

const confirmDelete = (showtime) => {
  showtimeToDelete.value  = showtime
  deleteDialogOpen.value  = true
}

const deleteShowtime = async () => {
  if (!showtimeToDelete.value) return
  deleting.value = true
  try {
    await $api(`/showtimes/${showtimeToDelete.value.id}`, { method: 'DELETE' })
    deleteDialogOpen.value = false
    showtimeToDelete.value = null
    await fetchShowtimes()
  } catch (err) {
    console.error('Delete failed:', err)
  } finally {
    deleting.value = false
  }
}

// ── Helpers ──────────────────────────────────────────────
const formatDate = (d) =>
  new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const formatTime = (t) => {
  if (!t) return ''
  return new Date(`2000-01-01T${t}`).toLocaleTimeString('en-US', {
    hour: 'numeric', minute: '2-digit', hour12: true,
  })
}

const statusClass = (status) => ({
  'scheduled':  'bg-green-500/20 text-green-400',
  'completed':  'bg-blue-500/20 text-blue-400',
  'cancelled':  'bg-red-500/20 text-red-400',
}[status] ?? 'bg-white/10 text-white/50')

const showingFrom = computed(() => (currentPage.value - 1) * pageSize + 1)
const showingTo   = computed(() => Math.min(currentPage.value * pageSize, totalItems.value))
</script>