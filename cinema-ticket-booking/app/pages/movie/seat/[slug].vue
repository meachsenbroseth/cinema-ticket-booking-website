<template>
  <div class="min-h-screen bg-[#0d0000] py-10 px-4">
    <div class="max-w-4xl mx-auto">

      <!-- Back -->
      <NuxtLink :to="`/movie/${slug}`"
        class="inline-flex items-center gap-2 text-white/40 hover:text-white text-sm transition-colors mb-8">
        <Icon name="lucide:arrow-left" class="w-4 h-4" />
        Back to Movies
      </NuxtLink>

      <!-- Movie Info Bar -->
      <div
        class="bg-white/[0.04] border border-white/10 rounded-2xl p-5 mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
          <img :src="moviePoster" alt="poster" class="w-12 h-16 object-cover rounded-xl" />
          <div>
            <h2 class="text-white font-bold text-lg leading-tight">{{ movieTitle }}</h2>
            <div class="flex items-center gap-2 mt-1 text-white/40 text-sm">
              <Icon name="lucide:map-pin" class="w-3.5 h-3.5 text-red-500" />
              {{ cinemaName }}
              <span class="text-white/20">·</span>
              <Icon name="lucide:clock" class="w-3.5 h-3.5" />
              {{ showtime }}
            </div>
          </div>
        </div>
        <div class="text-right">
          <p class="text-white/30 text-xs uppercase tracking-widest mb-1">Prices</p>
          <div class="flex gap-3">
            <div>
              <span class="text-white/40 text-xs">Standard</span>
              <p class="text-white font-bold text-lg">${{ standardPrice }}</p>
            </div>
            <div>
              <span class="text-white/40 text-xs">VIP</span>
              <p class="text-yellow-500 font-bold text-lg">${{ vipPrice }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Screen -->
      <div class="mb-10 text-center flex flex-col items-center">
        <div class="relative w-full max-w-[650px]">
          <div class="h-1.5 bg-gradient-to-r from-transparent via-red-500/60 to-transparent rounded-full" />
          <div class="h-8 bg-gradient-to-b from-red-500/10 to-transparent rounded-b-[50%]" />
        </div>
        <p class="text-white/20 text-xs uppercase tracking-[0.3em] mt-1">Screen</p>
      </div>

      <!-- Seat Grid -->
      <div class="overflow-x-auto pb-4">
        <div class="min-w-[600px] flex flex-col items-center px-2">

          <!-- Column numbers -->
          <div class="flex items-center gap-1.5 mb-3">
            <div class="w-8 shrink-0" /> <!-- spacer for row label -->
            <div v-for="n in seatCount" :key="n" class="w-8 text-center text-white/20 text-[10px] font-medium shrink-0">
              {{ n }}
            </div>
            <div class="w-8 shrink-0" /> <!-- spacer for right row label -->
          </div>

          <!-- Rows -->
          <div v-for="(rowLetter, rowIndex) in rows" :key="rowLetter"
            class="flex items-center justify-center gap-1.5 mb-2">

            <div class="w-8 text-center text-white/30 text-xs font-semibold shrink-0">{{ rowLetter }}</div>

            <button v-for="seatNum in seatCount" :key="`${rowLetter}-${seatNum}`"
              @click="toggleSeat(rowLetter, seatNum)" :disabled="isSeatBooked(rowLetter, seatNum)"
              class="w-8 h-7 rounded-t-lg shrink-0 transition-all duration-150"
              :class="getSeatClass(rowLetter, seatNum, rowIndex)" />

            <div class="w-8 text-center text-white/30 text-xs font-semibold shrink-0">{{ rowLetter }}</div>
          </div>

        </div>
      </div>

      <!-- Legend -->
      <div class="flex flex-wrap items-center justify-center gap-6 mt-8 mb-8">
        <div v-for="item in legend" :key="item.label" class="flex items-center gap-2">
          <div class="w-6 h-5 rounded-t-lg shrink-0" :class="item.color" />
          <span class="text-white/40 text-xs">{{ item.label }}</span>
        </div>
      </div>

      <!-- Summary -->
      <transition name="slide-up">
        <div v-if="selectedSeats.length > 0" class="bg-white/[0.04] border border-white/10 rounded-2xl p-6 mb-4">

          <div class="flex items-start justify-between gap-6 flex-wrap mb-5">
            <div>
              <p class="text-white/40 text-xs uppercase tracking-widest mb-3">Selected Seats</p>
              <div class="flex flex-wrap gap-2">
                <span v-for="seat in selectedSeatsDetails" :key="seat.id"
                  class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-semibold"
                  :class="seat.isPremium ? 'bg-yellow-500/20 border border-yellow-500/30 text-yellow-400' : 'bg-red-600/20 border border-red-500/20 text-red-400'">
                  {{ seat.id }}
                  <button @click="removeSeat(seat.id)" class="hover:text-white transition-colors">
                    <Icon name="lucide:x" class="w-3 h-3" />
                  </button>
                </span>
              </div>
            </div>
            <div class="text-right space-y-1">
              <div v-if="standardSeatsCount > 0" class="flex items-center justify-end gap-8 text-sm">
                <span class="text-white/40">{{ standardSeatsCount }} Standard × ${{ standardPrice }}</span>
                <span class="text-white">${{ (standardSeatsCount * standardPrice).toFixed(2) }}</span>
              </div>
              <div v-if="vipSeatsCount > 0" class="flex items-center justify-end gap-8 text-sm">
                <span class="text-white/40">{{ vipSeatsCount }} VIP × ${{ vipPrice }}</span>
                <span class="text-white">${{ (vipSeatsCount * vipPrice).toFixed(2) }}</span>
              </div>
              <div class="h-px bg-white/10 my-2" />
              <div class="flex items-center justify-end gap-8">
                <span class="text-white/60 text-sm font-medium">Total</span>
                <span class="text-red-500 font-black text-xl">${{ totalAmount.toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <button @click="proceedToPayment"
            class="w-full py-3.5 bg-red-600 hover:bg-red-500 active:scale-[0.98] text-white font-bold rounded-xl transition-all flex items-center justify-center gap-2 group">
            Continue to Payment
            <Icon name="lucide:arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
          </button>
        </div>

        <!-- Empty -->
        <div v-else class="border border-dashed border-white/10 rounded-2xl p-10 text-center mb-4">
          <Icon name="lucide:armchair" class="w-10 h-10 text-white/10 mx-auto mb-3" />
          <p class="text-white/30 text-sm">Click on a seat to select it</p>
        </div>
      </transition>

    </div>
  </div>
</template>

<script setup>
const route = useRoute()
const slug = route.params.slug
const { $api } = useNuxtApp()

// ── Query params from movie detail page ──────────────────
const showtimeId = route.query.showtime_id
const cinemaName = ref(route.query.cinema || 'Legend Cinema')
const showtime = ref(route.query.time || '')
const movieTitle = ref('')
const moviePoster = ref('')

// ── Pricing from API ─────────────────────────────────────
const standardPrice = ref(Number(route.query.price) || 5.50)
const vipPrice = ref(9.00)

// ── Seat state ───────────────────────────────────────────
const rows = ref([])   // ['A','B','C'...]
const seatCount = ref(0)    // seats per row
const seatMap = ref({})   // { A: [{id,seat_code,type,is_booked},...] }
const selectedSeats = ref([])  // seat_codes like "A5"
const loading = ref(true)
const error = ref(null)

// ── Fetch seat availability ──────────────────────────────
const fetchSeats = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $api(`/seats/availability?showtime_id=${showtimeId}`)

    // Update prices from API
    standardPrice.value = Number(response.price_standard)
    vipPrice.value = Number(response.price_vip)

    // Build seat map — response.seats = { A: [{id,seat_code,row,number,type,price,is_booked}] }
    seatMap.value = response.seats ?? {}

    // Derive rows and seatCount from the seat map
    rows.value = Object.keys(seatMap.value).sort()
    seatCount.value = Math.max(...Object.values(seatMap.value).map(r => r.length), 0)

  } catch (err) {
    error.value = 'Failed to load seat map'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// ── Fetch movie info ─────────────────────────────────────
const fetchMovie = async () => {
  try {
    const response = await $api(`/movies/${slug}`)
    movieTitle.value = response.title
    moviePoster.value = response.poster
  } catch (err) {
    console.error('Failed to fetch movie:', err)
  }
}

onMounted(async () => {
  await Promise.all([fetchMovie(), fetchSeats()])
})

// ── Seat helpers ─────────────────────────────────────────
const getSeat = (row, num) => seatMap.value[row]?.[num - 1]

const isSeatBooked = (row, num) => getSeat(row, num)?.is_booked ?? false
const isSeatSelected = (row, num) => {
  const seat = getSeat(row, num)
  return seat ? selectedSeats.value.includes(seat.seat_code) : false
}
const isPremiumSeat = (row, num) => getSeat(row, num)?.type === 'vip'

const getSeatClass = (row, num) => {
  if (isSeatBooked(row, num)) return 'bg-white/5 cursor-not-allowed opacity-40'
  if (isSeatSelected(row, num)) return 'bg-red-600 shadow-lg shadow-red-500/40 scale-110 cursor-pointer'
  if (isPremiumSeat(row, num)) return 'bg-yellow-500/20 border border-yellow-500/30 hover:bg-yellow-500/40 cursor-pointer hover:-translate-y-0.5'
  return 'bg-white/10 hover:bg-white/25 cursor-pointer hover:-translate-y-0.5'
}

const toggleSeat = (row, num) => {
  const seat = getSeat(row, num)
  if (!seat || seat.is_booked) return
  if (isSeatSelected(row, num)) {
    selectedSeats.value = selectedSeats.value.filter(s => s !== seat.seat_code)
  } else {
    selectedSeats.value.push(seat.seat_code)
  }
}

const removeSeat = (seatCode) => {
  selectedSeats.value = selectedSeats.value.filter(s => s !== seatCode)
}

// ── Selected seats with details ──────────────────────────
const selectedSeatsDetails = computed(() => {
  return selectedSeats.value.map(code => {
    const row = code.charAt(0)
    const rowSeats = seatMap.value[row] ?? []
    const seat = rowSeats.find(s => s.seat_code === code)
    const isVip = seat?.type === 'vip'
    return {
      id: code,
      seatId: seat?.id,
      isPremium: isVip,
      price: isVip ? vipPrice.value : standardPrice.value,
    }
  })
})

const standardSeatsCount = computed(() =>
  selectedSeatsDetails.value.filter(s => !s.isPremium).length
)
const vipSeatsCount = computed(() =>
  selectedSeatsDetails.value.filter(s => s.isPremium).length
)
const totalAmount = computed(() =>
  selectedSeatsDetails.value.reduce((sum, s) => sum + s.price, 0)
)

// ── Legend ───────────────────────────────────────────────
const legend = [
  { label: 'Standard', color: 'bg-white/10' },
  { label: 'Selected', color: 'bg-red-600' },
  { label: 'Booked', color: 'bg-white/5 opacity-40' },
  { label: 'VIP', color: 'bg-yellow-500/40 border border-yellow-500/40' },
]

// ── Proceed to payment ───────────────────────────────────
const proceedToPayment = async () => {
  if (!selectedSeats.value.length) return

  try {
    // Create booking via API
    const booking = await $api('/bookings', {
      method: 'POST',
      body: {
        showtime_id: showtimeId,
        seat_ids: selectedSeatsDetails.value.map(s => s.seatId),
      },
    })

    // Navigate to checkout with booking code
    navigateTo({
      path: `/checkout/${slug}`,
      query: {
        booking_id: booking.data.id,
        booking_code: booking.data.booking_code,
        total: totalAmount.value.toFixed(2),
      },
    })
  } catch (err) {
    // Seats might have been taken — refresh
    if (err?.status === 422) {
      alert(err?.data?.message || 'Some seats are no longer available.')
      await fetchSeats()
      selectedSeats.value = []
    } else {
      alert('Failed to create booking. Please try again.')
    }
  }
}
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