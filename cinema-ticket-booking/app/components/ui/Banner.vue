<template>
  <Swiper
    :modules="modules"
    :autoplay="{ delay: 5000, disableOnInteraction: false }"
    :loop="true"
    :slides-per-view="1"
    :speed="800"
    :pagination="{ clickable: true, dynamicBullets: false }"
    class="w-full h-full"
  >
    <SwiperSlide v-for="movie in movies" :key="movie.id">
      <div class="relative w-full h-full">
        <img :src="movie.image" :alt="movie.title" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent" />
        <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent" />
        <div class="absolute bottom-16 left-10 max-w-lg space-y-4">
          <span class="text-xs text-red-500 uppercase tracking-widest font-semibold">Now Showing</span>
          <h2 class="text-4xl font-black text-white leading-tight">{{ movie.title }}</h2>
          <p class="text-sm text-white/60 line-clamp-2">{{ movie.description }}</p>
          <div class="flex items-center gap-3 pt-1">
            <button class="flex items-center gap-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-colors">
              <Icon name="lucide:ticket" class="w-4 h-4" />
              Book Now
            </button>
            <button class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium px-5 py-2.5 rounded-full border border-white/15 transition-colors">
              <Icon name="lucide:play" class="w-4 h-4" />
              Trailer
            </button>
          </div>
        </div>
      </div>
    </SwiperSlide>
  </Swiper>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'

const modules = [Autoplay, Pagination]

const movies = [
  {
    id: 1,
    title: 'Dune: Part Two',
    description: 'Paul Atreides unites with the Fremen to seek revenge against those who destroyed his family.',
    image: 'https://image.tmdb.org/t/p/original/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg',
  },
  {
    id: 2,
    title: 'Godzilla x Kong',
    description: 'The mighty Kong and the fearsome Godzilla team up against a colossal threat hidden in the world.',
    image: 'https://image.tmdb.org/t/p/original/tMefBSflR6PGQLv7WvFPpKLZkyk.jpg',
  },
  {
    id: 3,
    title: 'Civil War',
    description: 'A journey across a dystopian future America, balancing wonder and terror.',
    image: 'https://image.tmdb.org/t/p/original/sh7Rg8Er3tFcN9BpKIPOMvALgZd.jpg',
  },
]
</script>

<style scoped>
.swiper,
.swiper-wrapper,
.swiper-slide {
  height: 100% !important;
}

/* Custom pagination styles */
:deep(.swiper-pagination) {
  bottom: 24px !important;
  left: 40px !important;
  width: auto !important;
  text-align: left;
  z-index: 10;
}

:deep(.swiper-pagination-bullet) {
  background: white;
  opacity: 0.4;
  width: 6px;
  height: 6px;
  margin: 0 4px !important;
}

:deep(.swiper-pagination-bullet-active) {
  background: #ef4444;
  opacity: 1;
  width: 24px;
  border-radius: 3px;
}
</style>