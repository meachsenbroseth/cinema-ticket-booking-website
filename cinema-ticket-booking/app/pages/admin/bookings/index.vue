<!-- pages/admin/bookings.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Bookings</h1>
        <p class="text-white/50 text-sm mt-1">Manage customer bookings and ticket purchases</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-white/60 text-xs mb-1">Booking ID</label>
          <input
            v-model="filterBookingId"
            type="text"
            placeholder="Search by ID"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/30 focus:outline-none focus:border-red-500"
          />
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Status</label>
          <select v-model="filterStatus" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All Status</option>
            <option value="confirmed">Confirmed</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Movie</label>
          <select v-model="filterMovieId" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:border-red-500">
            <option value="all">All Movies</option>
            <option v-for="movie in movies" :key="movie.id" :value="movie.id">{{ movie.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-white/60 text-xs mb-1">Date Range</label>
          <input
            v-model="filterDate"
            type="date"
            class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500"
          />
        </div>
      </div>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-white/5 border-b border-white/10">
            <tr>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">ID</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Customer</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Movie</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Cinema</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Showtime</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Seats</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Total</th>
              <th class="text-left px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Status</th>
              <th class="text-right px-4 py-3 text-white/60 text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="booking in paginatedBookings" :key="booking.id" class="hover:bg-white/5 transition-colors">
              <td class="px-4 py-3 text-white/70 text-sm">#{{ booking.id }}</td>
              <td class="px-4 py-3">
                <div>
                  <p class="text-white font-medium">{{ booking.customerName }}</p>
                  <p class="text-white/40 text-xs">{{ booking.customerEmail }}</p>
                </div>
               </td>
              <td class="px-4 py-3 text-white/70">{{ getMovieTitle(booking.movieId) }}</td>
              <td class="px-4 py-3 text-white/70">{{ getCinemaName(booking.cinemaId) }}</td>
              <td class="px-4 py-3 text-white/70">{{ formatDate(booking.showDate) }} at {{ booking.showTime }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1">
                  <span v-for="seat in booking.seats" :key="seat" class="px-2 py-0.5 bg-red-500/20 rounded text-xs text-red-400">{{ seat }}</span>
                </div>
               </td>
              <td class="px-4 py-3 text-white font-semibold">${{ booking.totalAmount.toFixed(2) }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-500/20 text-green-400': booking.status === 'confirmed',
                    'bg-yellow-500/20 text-yellow-400': booking.status === 'pending',
                    'bg-red-500/20 text-red-400': booking.status === 'cancelled'
                  }"
                >
                  {{ booking.status }}
                </span>
               </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openViewModal(booking)" class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                    <Icon name="lucide:eye" class="w-4 h-4" />
                  </button>
                  <button @click="openEditModal(booking)" class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                    <Icon name="lucide:edit" class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(booking)" class="p-1.5 rounded-lg text-white/50 hover:text-red-500 hover:bg-red-500/10 transition-colors">
                    <Icon name="lucide:trash-2" class="w-4 h-4" />
                  </button>
                </div>
               </td>
             </tr>
            <tr v-if="filteredBookings.length === 0">
              <td colspan="9" class="px-4 py-12 text-center text-white/40">No bookings found</td>
             </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-white/5">
        <div class="text-white/40 text-sm">
          Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredBookings.length) }} of {{ filteredBookings.length }} bookings
        </div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed">Previous</button>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 rounded-lg border border-white/10 text-white/60 hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed">Next</button>
        </div>
      </div>
    </div>

    <!-- View Booking Details Modal -->
    <DialogRoot v-model:open="viewModalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl">
          <DialogTitle class="text-xl font-bold text-white mb-2">Booking Details</DialogTitle>
          <div v-if="selectedBooking" class="space-y-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div><span class="text-white/40">Booking ID:</span> <span class="text-white">#{{ selectedBooking.id }}</span></div>
              <div><span class="text-white/40">Status:</span> <span class="capitalize" :class="getStatusColor(selectedBooking.status)">{{ selectedBooking.status }}</span></div>
              <div><span class="text-white/40">Customer:</span> <span class="text-white">{{ selectedBooking.customerName }}</span></div>
              <div><span class="text-white/40">Email:</span> <span class="text-white">{{ selectedBooking.customerEmail }}</span></div>
              <div><span class="text-white/40">Phone:</span> <span class="text-white">{{ selectedBooking.customerPhone || 'N/A' }}</span></div>
              <div><span class="text-white/40">Movie:</span> <span class="text-white">{{ getMovieTitle(selectedBooking.movieId) }}</span></div>
              <div><span class="text-white/40">Cinema:</span> <span class="text-white">{{ getCinemaName(selectedBooking.cinemaId) }}</span></div>
              <div><span class="text-white/40">Show Date:</span> <span class="text-white">{{ formatDate(selectedBooking.showDate) }}</span></div>
              <div><span class="text-white/40">Show Time:</span> <span class="text-white">{{ selectedBooking.showTime }}</span></div>
              <div><span class="text-white/40">Seats:</span> <span class="text-white">{{ selectedBooking.seats.join(', ') }}</span></div>
              <div><span class="text-white/40">Ticket Price:</span> <span class="text-white">${{ selectedBooking.ticketPrice?.toFixed(2) || 'N/A' }}</span></div>
              <div><span class="text-white/40">Total Amount:</span> <span class="text-red-400 font-semibold">${{ selectedBooking.totalAmount.toFixed(2) }}</span></div>
            </div>
            <div v-if="selectedBooking.bookingDate">
              <div class="text-white/40 text-xs">Booked on: {{ new Date(selectedBooking.bookingDate).toLocaleString() }}</div>
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <button @click="viewModalOpen = false" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg">Close</button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Edit Status Modal -->
    <DialogRoot v-model:open="editModalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50 shadow-2xl">
          <DialogTitle class="text-xl font-bold text-white mb-2">Update Booking Status</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">Change the status of this booking.</DialogDescription>
          <form @submit.prevent="updateBookingStatus" class="space-y-4">
            <div>
              <label class="block text-white/60 text-sm mb-1">Status</label>
              <select v-model="editForm.status" class="w-full bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="flex gap-3 pt-4">
              <button type="button" @click="editModalOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">Cancel</button>
              <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg">Update</button>
            </div>
          </form>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- Delete Confirmation Dialog -->
    <DialogRoot v-model:open="deleteDialogOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#1a0a0a] border border-white/10 rounded-2xl p-6 z-50">
          <DialogTitle class="text-xl font-bold text-white mb-2">Delete Booking</DialogTitle>
          <DialogDescription class="text-white/40 text-sm mb-6">Are you sure you want to delete booking #{{ bookingToDelete?.id }}? This action cannot be undone.</DialogDescription>
          <div class="flex gap-3">
            <button @click="deleteDialogOpen = false" class="flex-1 px-4 py-2 border border-white/10 rounded-lg text-white/60 hover:bg-white/5">Cancel</button>
            <button @click="deleteBooking" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg">Delete</button>
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

definePageMeta({ layout: 'admin-layout' })

const { $api } = useNuxtApp()

// ---------- State ----------
const bookings = ref([])
const movies = ref([])
const cinemas = ref([])
const loading = ref(false)
const error = ref(null)

// Pagination
const pageSize = 5
const currentPage = ref(1)

// Filters
const filterBookingId = ref('')
const filterStatus = ref('all')
const filterMovieId = ref('all')
const filterDate = ref('')

// ---------- Fetch Movies (for filter dropdown) ----------
const fetchMovies = async () => {
  try {
    const response = await $api('/movies', { params: { per_page: 100 } })
    movies.value = response.data || response
  } catch (err) {
    console.error('Failed to fetch movies:', err)
  }
}

// ---------- Fetch Cinemas (for display) ----------
const fetchCinemas = async () => {
  try {
    const response = await $api('/cinemas', { params: { per_page: 100 } })
    cinemas.value = response.data || response
  } catch (err) {
    console.error('Failed to fetch cinemas:', err)
  }
}

// ---------- Fetch Bookings ----------
const fetchBookings = async () => {
  loading.value = true
  error.value = null

  try {
    const params = {
      per_page: 100,
    }

    if (filterBookingId.value) params.booking_id = filterBookingId.value
    if (filterStatus.value !== 'all') params.status = filterStatus.value
    if (filterMovieId.value !== 'all') params.movie_id = filterMovieId.value
    if (filterDate.value) params.date = filterDate.value

    const response = await $api('/bookings', { params })

    const rawBookings = response.data || []

    bookings.value = rawBookings.map(b => ({
      id: b.id,

      // 👇 FIX USER
      customerName: b.user?.name || 'Unknown',
      customerEmail: b.user?.email || '',
      customerPhone: b.user?.phone || '',

      // 👇 FIX MOVIE + CINEMA
      movieTitle: b.showtime?.movie?.title || 'Unknown',
      cinemaName: b.showtime?.hall?.cinema?.name || 'Unknown',

      // 👇 FIX SHOWTIME
      showDate: b.showtime?.show_date,
      showTime: b.showtime?.show_time,

      // 👇 FIX SEATS
      seats: b.booking_seats?.map(s => s.seat?.seat_code) || [],

      // 👇 FIX PRICE
      ticketPrice: Number(b.booking_seats?.[0]?.price || 0),
      totalAmount: Number(b.total_amount || 0),

      status: b.status,
      bookingDate: b.created_at,
    }))
  } catch (err) {
    console.error('Failed to fetch bookings:', err)
    error.value = 'Failed to load bookings'
  } finally {
    loading.value = false
  }
}

// ---------- Helpers ----------
const getMovieTitle = (id) => movies.value.find(m => m.id == id)?.title || 'Unknown'
const getCinemaName = (id) => cinemas.value.find(c => c.id == id)?.name || 'Unknown'
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
const getStatusColor = (status) => {
  if (status === 'confirmed') return 'text-green-400'
  if (status === 'pending') return 'text-yellow-400'
  return 'text-red-400'
}

// ---------- Computed: Filtered & Paginated ----------
const filteredBookings = computed(() => {
  let result = bookings.value
  if (filterBookingId.value) {
    result = result.filter(b => b.id.toString().includes(filterBookingId.value))
  }
  if (filterStatus.value !== 'all') {
    result = result.filter(b => b.status === filterStatus.value)
  }
  if (filterMovieId.value !== 'all') {
    result = result.filter(b => b.movieId == filterMovieId.value)
  }
  if (filterDate.value) {
    result = result.filter(b => b.showDate === filterDate.value)
  }
  return result
})

const totalPages = computed(() => Math.ceil(filteredBookings.value.length / pageSize))
const paginatedBookings = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredBookings.value.slice(start, start + pageSize)
})

watch([filterBookingId, filterStatus, filterMovieId, filterDate], () => {
  currentPage.value = 1
  fetchBookings()
})

// ---------- View Modal ----------
const viewModalOpen = ref(false)
const selectedBooking = ref(null)
const openViewModal = (booking) => {
  selectedBooking.value = booking
  viewModalOpen.value = true
}

// ---------- Edit Status Modal ----------
const editModalOpen = ref(false)
const editingBooking = ref(null)
const editForm = reactive({ status: '' })
const openEditModal = (booking) => {
  editingBooking.value = booking
  editForm.status = booking.status
  editModalOpen.value = true
}

const updateBookingStatus = async () => {
  if (!editingBooking.value) return
  try {
    await $api(`/admin/bookings/${editingBooking.value.id}/status`, {
      method: 'PATCH',
      body: { status: editForm.status },
    })
    // Update local data
    const index = bookings.value.findIndex(b => b.id === editingBooking.value.id)
    if (index !== -1) {
      bookings.value[index].status = editForm.status
    }
    editModalOpen.value = false
  } catch (err) {
    console.error('Status update failed:', err)
    alert(err?.data?.message || 'Failed to update status')
  }
}

// ---------- Delete ----------
const deleteDialogOpen = ref(false)
const bookingToDelete = ref(null)
const confirmDelete = (booking) => {
  bookingToDelete.value = booking
  deleteDialogOpen.value = true
}

const deleteBooking = async () => {
  if (!bookingToDelete.value) return
  try {
    await $api(`/admin/bookings/${bookingToDelete.value.id}`, { method: 'DELETE' })
    bookings.value = bookings.value.filter(b => b.id !== bookingToDelete.value.id)
    deleteDialogOpen.value = false
    bookingToDelete.value = null
  } catch (err) {
    console.error('Delete failed:', err)
    alert(err?.data?.message || 'Delete failed')
  }
}

// ---------- Lifecycle ----------
onMounted(async () => {
  await Promise.all([
    fetchMovies(),
    fetchCinemas(),
    fetchBookings(),
  ])
})
</script>