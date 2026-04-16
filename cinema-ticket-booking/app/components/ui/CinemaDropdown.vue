<!-- components/CinemaDropdown.vue -->
<template>
  <DropdownMenuRoot v-model:open="open">
    <DropdownMenuTrigger as-child>
      <button type="button"
        class="flex items-center gap-1.5 cursor-pointer text-sm font-medium transition outline-none"
        :class="modelValue !== 'All Cinemas' ? 'text-red-500' : 'text-white/80 hover:text-white'">
        <Icon name="lucide:map-pin" class="w-4 h-4 text-red-500" />
        {{ modelValue === 'All Cinemas' ? 'All Cinemas' : modelValue.replace('Legend ', '') }}
        <Icon name="lucide:chevron-down" class="w-4 h-4 text-white/50 transition-transform duration-200"
          :class="open ? 'rotate-180' : ''" />
      </button>
    </DropdownMenuTrigger>

    <DropdownMenuPortal>
      <DropdownMenuContent align="end" :side-offset="8"
        class="z-[200] bg-[#1a0a0a] border border-white/10 min-w-64 max-h-[70vh] overflow-y-auto rounded-md"
        @interact-outside="open = false">

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-6">
          <Icon name="lucide:loader-circle" class="w-5 h-5 text-red-500 animate-spin" />
        </div>

        <template v-else>
          <DropdownMenuItem
            v-for="cinema in cinemaOptions"
            :key="cinema.value"
            class="flex items-center gap-3 px-4 py-3 cursor-pointer border-b border-white/10 last:border-0 outline-none hover:bg-white/5 transition-colors"
            :class="modelValue === cinema.value ? 'text-red-500' : 'text-white/80'"
            @click="() => { emit('update:modelValue', cinema.value); open = false }"
          >
            <Icon name="lucide:map-pin" class="w-4 h-4 shrink-0"
              :class="modelValue === cinema.value ? 'text-red-500' : 'text-white/40'" />
            {{ cinema.label }}
          </DropdownMenuItem>
        </template>

      </DropdownMenuContent>
    </DropdownMenuPortal>
  </DropdownMenuRoot>
</template>

<script setup>
import {
  DropdownMenuRoot, DropdownMenuTrigger,
  DropdownMenuContent, DropdownMenuItem, DropdownMenuPortal,
} from 'radix-vue'
import { useCinemaStore } from '~/stores/cinemaStore'

defineProps({
  modelValue: { type: String, default: 'All Cinemas' }
})

const emit = defineEmits(['update:modelValue'])
const open = ref(false)
const cinemaStore = useCinemaStore()
const loading = ref(false)

// Build options — "All Cinemas" first then API results
const cinemaOptions = computed(() => [
  { value: 'All Cinemas', label: 'All Cinemas' },
  ...cinemaStore.dropdownCinemas.map(c => ({
    value: c.name,
    label: c.name,
  }))
])

onMounted(async () => {
  if (cinemaStore.dropdownCinemas.length === 0) {
    loading.value = true
    try {
      await cinemaStore.fetchDropdownCinemas()
    } finally {
      loading.value = false
    }
  }
})
</script>