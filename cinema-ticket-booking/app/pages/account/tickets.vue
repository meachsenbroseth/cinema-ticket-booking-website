<template>
  <div class="min-h-screen bg-[#0d0000] py-10 px-4">
    <div class="max-w-4xl mx-auto">

      <!-- Header -->
      <div class="mb-8">
        <p class="text-red-500 text-xs uppercase tracking-widest font-semibold mb-1">Account</p>
        <h1 class="text-3xl font-black text-white">My Tickets</h1>
        <p class="text-white/40 text-sm mt-1">View and manage your bookings</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="text-white text-center">
          <Icon name="lucide:loader-circle" class="w-12 h-12 animate-spin mx-auto mb-4 text-red-500" />
          <p class="text-white/50">Loading your tickets...</p>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-12">
        <Icon name="lucide:alert-circle" class="w-12 h-12 text-white/20 mx-auto mb-3" />
        <p class="text-white/40">{{ error }}</p>
        <button @click="fetchTickets" class="mt-4 text-red-500 hover:text-red-400 text-sm">Try Again</button>
      </div>

      <!-- Tabs -->
      <div v-else class="flex items-center border-b border-white/10 mb-8">
        <button v-for="tab in ['upcoming', 'past']" :key="tab" @click="activeTab = tab"
          class="relative px-6 py-3 text-sm font-semibold capitalize transition-colors"
          :class="activeTab === tab ? 'text-red-500' : 'text-white/40 hover:text-white/70'">
          {{ tab }}
          <span v-if="activeTab === tab" class="absolute bottom-0 left-0 w-full h-0.5 bg-red-600 rounded-full" />
        </button>
        <span class="ml-auto text-white/30 text-xs">{{ filteredTickets.length }} ticket{{ filteredTickets.length !== 1 ?
          's' : '' }}</span>
      </div>

      <!-- Tickets List -->
      <div v-if="filteredTickets.length > 0" class="space-y-4">
        <div v-for="ticket in filteredTickets" :key="ticket.id"
          class="group bg-white/[0.03] hover:bg-white/[0.05] border border-white/10 hover:border-white/20 rounded-2xl overflow-hidden transition-all duration-200">
          <div class="flex flex-col sm:flex-row">

            <!-- Poster -->
            <div class="relative sm:w-28 h-44 sm:h-auto shrink-0">
              <img :src="ticket.poster" :alt="ticket.movieTitle" class="w-full h-full object-cover" />
              <div class="absolute inset-0 bg-gradient-to-r from-transparent to-black/40 sm:block hidden" />
              <span
                class="absolute top-2 left-2 bg-black/70 border border-white/10 text-white/80 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                {{ ticket.format }}
              </span>
              <span class="absolute bottom-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full" :class="ticket.status === 'confirmed'
                ? 'bg-green-500/30 text-green-400 border border-green-500/30'
                : ticket.status === 'used'
                  ? 'bg-gray-500/30 text-gray-400 border border-gray-500/30'
                  : 'bg-white/10 text-white/40 border border-white/10'">
                {{ ticket.status === 'confirmed' ? 'Confirmed' : ticket.status === 'used' ? 'Used' : ticket.status }}
              </span>
            </div>

            <!-- Info -->
            <div class="flex-1 p-5 min-w-0">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                  <h2 class="text-white font-bold text-lg leading-tight truncate">{{ ticket.movieTitle }}</h2>
                  <p class="text-white/30 text-xs mt-0.5">#{{ ticket.booking_code || ticket.id }}</p>
                </div>
                <span class="text-red-400 font-black text-xl shrink-0">${{ ticket.totalAmount.toFixed(2) }}</span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-4">
                <div class="flex items-center gap-2 text-white/50 text-xs">
                  <Icon name="lucide:map-pin" class="w-3.5 h-3.5 text-red-500 shrink-0" />
                  <span class="truncate">{{ ticket.cinemaName }}</span>
                </div>
                <div class="flex items-center gap-2 text-white/50 text-xs">
                  <Icon name="lucide:calendar" class="w-3.5 h-3.5 shrink-0" />
                  {{ formatDate(ticket.showDate) }}
                </div>
                <div class="flex items-center gap-2 text-white/50 text-xs">
                  <Icon name="lucide:clock" class="w-3.5 h-3.5 shrink-0" />
                  {{ ticket.showTime }}
                </div>
              </div>

              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-white/30 text-xs">Seats</span>
                <span v-for="seat in ticket.seats" :key="seat"
                  class="px-2 py-0.5 bg-red-500/15 border border-red-500/20 rounded text-red-400 text-xs font-semibold">
                  {{ seat }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="flex sm:flex-col items-center justify-end gap-1 px-4 py-4 border-t sm:border-t-0 sm:border-l border-white/10 bg-white/[0.02]">
              <button @click="viewTicketDetails(ticket)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-white/40 hover:text-white hover:bg-white/10 transition-all"
                title="View details">
                <Icon name="lucide:eye" class="w-4 h-4" />
              </button>
              <button @click="downloadTicket(ticket)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-white/40 hover:text-white hover:bg-white/10 transition-all"
                title="Download">
                <Icon name="lucide:download" class="w-4 h-4" />
              </button>
              <button v-if="ticket.status === 'confirmed' && activeTab === 'upcoming'" @click="showQRCode(ticket)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-white/40 hover:text-red-400 hover:bg-red-500/10 transition-all"
                title="QR Code">
                <Icon name="lucide:qr-code" class="w-4 h-4" />
              </button>
            </div>

          </div>

          <div class="border-t border-dashed border-white/10 px-5 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-1.5 text-white/20 text-xs">
              <Icon name="lucide:ticket" class="w-3.5 h-3.5" />
              Legend Cinema · {{ ticket.seats.length }} {{ ticket.seats.length === 1 ? 'seat' : 'seats' }}
            </div>
            <button v-if="ticket.status === 'confirmed' && activeTab === 'upcoming'" @click="showQRCode(ticket)"
              class="text-red-500/70 hover:text-red-400 text-xs font-medium transition-colors flex items-center gap-1">
              Show QR
              <Icon name="lucide:chevron-right" class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
          <Icon name="lucide:ticket" class="w-8 h-8 text-white/20" />
        </div>
        <p class="text-white/40 font-medium">No {{ activeTab }} tickets</p>
        <!-- <p class="text-white/20 text-sm mt-1">{{ activeTab === 'upcoming' ? 'Book a movie to see tickets here' : 'Your
          past tickets will appear here' }}</p> -->
        <NuxtLink to="/"
          class="mt-5 px-5 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-full transition-colors">
          Browse Movies
        </NuxtLink>
      </div>

    </div>

    <!-- Ticket Details Modal -->
    <DialogRoot v-model:open="detailsModalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-[#130505] border border-white/10 rounded-2xl z-50 overflow-hidden">
          <div v-if="selectedTicket" class="relative h-32 overflow-hidden">
            <img :src="selectedTicket.poster" class="w-full h-full object-cover object-top" />
            <div class="absolute inset-0 bg-gradient-to-t from-[#130505] to-transparent" />
            <div class="absolute bottom-3 left-5">
              <p class="text-white font-black text-xl leading-tight">{{ selectedTicket.movieTitle }}</p>
              <p class="text-white/40 text-xs">{{ selectedTicket.booking_code || selectedTicket.id }}</p>
            </div>
          </div>

          <div v-if="selectedTicket" class="p-5 space-y-3">
            <div v-for="row in ticketRows(selectedTicket)" :key="row.label"
              class="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
              <span class="text-white/40 text-sm">{{ row.label }}</span>
              <span class="text-white text-sm font-medium" :class="row.class">{{ row.value }}</span>
            </div>
            <div class="pt-2 text-center">
              <p class="text-white/20 text-xs">Scan QR code at cinema entrance</p>
            </div>
          </div>

          <div class="px-5 pb-5">
            <button @click="detailsModalOpen = false"
              class="w-full py-2.5 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition-colors">
              Close
            </button>
          </div>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

    <!-- QR Modal -->
    <DialogRoot v-model:open="qrModalOpen">
      <DialogPortal>
        <DialogOverlay class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50" />
        <DialogContent
          class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl p-6 z-50 text-center">
          <DialogTitle class="text-gray-900 font-bold text-lg mb-1">Ticket QR Code</DialogTitle>
          <p class="text-gray-400 text-sm mb-5">{{ qrTicket?.movieTitle }} · {{ qrTicket?.booking_code || qrTicket?.id
            }}</p>
          <div class="w-48 h-48 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-5">
            <Icon name="lucide:qr-code" class="w-36 h-36 text-gray-700" />
          </div>
          <p class="text-gray-400 text-xs mb-5">Show this at the cinema entrance</p>
          <button @click="qrModalOpen = false"
            class="w-full py-2.5 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition-colors">
            Done
          </button>
        </DialogContent>
      </DialogPortal>
    </DialogRoot>

  </div>
</template>

<script setup>
import {
  DialogRoot, DialogPortal, DialogOverlay,
  DialogContent, DialogTitle,
} from 'radix-vue'

definePageMeta({
  middleware: 'auth'
})

const { $api } = useNuxtApp()

// State
const tickets = ref([])
const loading = ref(true)
const error = ref(null)
const activeTab = ref('upcoming')
const detailsModalOpen = ref(false)
const qrModalOpen = ref(false)
const selectedTicket = ref(null)
const qrTicket = ref(null)

// ── Fetch user bookings ─────────────────────────
const fetchTickets = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('/user/bookings', { params: { per_page: 50 } })

    // ✅ FIX: handle pagination structure
    const bookings = response.data?.data || response.data || response

    tickets.value = bookings.map(booking => ({
      id: booking.id,
      booking_code: booking.booking_code,

      // ✅ FIX: correct movie path
      movieTitle: booking.showtime?.movie?.title || 'Unknown',
      poster: booking.showtime?.movie?.poster || '',

      // ✅ FIX: correct cinema path
      cinemaName: booking.showtime?.hall?.cinema?.name || 'Unknown',

      // ✅ FIX: correct date/time fields
      showDate: booking.showtime?.show_date,
      showTime: booking.showtime?.show_time?.slice(0, 5),

      // ✅ FIX: correct seats mapping
      seats: booking.booking_seats?.map(s => s.seat?.seat_code) || [],

      totalAmount: parseFloat(booking.total_amount || 0),

      status: booking.status,
      format: booking.showtime?.format?.toUpperCase() || '2D',
    }))
  } catch (err) {
    console.error('Failed to fetch tickets:', err)
    error.value = err?.data?.message || 'Failed to load your tickets'
  } finally {
    loading.value = false
  }
}

// ── Computed filtered tickets ───────────────────
const filteredTickets = computed(() => {
  const today = new Date().toISOString().slice(0, 10)

  if (activeTab.value === 'upcoming') {
    return tickets.value.filter(t =>
      t.showDate >= today &&
      t.status !== 'cancelled' &&
      t.status !== 'used'
    )
  } else {
    return tickets.value.filter(t =>
      t.showDate < today ||
      t.status === 'used' ||
      t.status === 'cancelled'
    )
  }
})

// ── Helpers ─────────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const ticketRows = (t) => [
  { label: 'Cinema', value: t.cinemaName },
  { label: 'Date', value: formatDate(t.showDate) },
  { label: 'Time', value: t.showTime },
  { label: 'Seats', value: t.seats.join(', ') },
  { label: 'Format', value: t.format },
  { label: 'Total', value: `$${t.totalAmount.toFixed(2)}`, class: 'text-red-400 font-bold' },
  {
    label: 'Status',
    value: t.status === 'confirmed'
      ? 'Confirmed'
      : t.status === 'used'
        ? 'Used'
        : t.status,
    class:
      t.status === 'confirmed'
        ? 'text-green-400'
        : t.status === 'used'
          ? 'text-gray-400'
          : 'text-white/40'
  },
]

const viewTicketDetails = (ticket) => {
  selectedTicket.value = ticket
  detailsModalOpen.value = true
}

const showQRCode = (ticket) => {
  qrTicket.value = ticket
  qrModalOpen.value = true
}

const downloadTicket = (ticket) => {
  alert(`Downloading ticket #${ticket.booking_code || ticket.id}`)
}

// ── Lifecycle ───────────────────────────────────
onMounted(() => {
  fetchTickets()
})
</script>