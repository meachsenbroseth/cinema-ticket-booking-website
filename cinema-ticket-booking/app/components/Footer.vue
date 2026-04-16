<template>
  <footer class="border-t border-white/10 bg-[#0a0000] mt-16">

    <!-- Main footer content -->
    <div class="px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">

      <!-- Brand col -->
      <div class="md:col-span-1 flex flex-col gap-4">
        <div class="flex items-center gap-2">
          <Icon name="lucide:clapperboard" class="w-6 h-6 text-red-500" />
          <span class="text-xl font-bold text-white">Cinema</span>
        </div>
        <p class="text-white/40 text-sm leading-relaxed">
          Cambodia's premier cinema experience. Bringing the magic of movies to life.
        </p>
        <!-- Social icons -->
        <div class="flex items-center gap-3">
          <a v-for="s in socialLinks" :key="s.label" :href="s.path"
            class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center text-white/40 hover:text-white hover:border-white/30 transition-colors">
            <Icon :name="s.icon" class="w-3.5 h-3.5" />
          </a>
        </div>
      </div>

      <!-- Links cols -->
      <div v-for="col in footerCols" :key="col.heading" class="flex flex-col gap-3">
        <h3 class="text-white/60 text-xs font-semibold uppercase tracking-wider">{{ col.heading }}</h3>
        <ul class="flex flex-col gap-2">
          <li v-for="item in col.links" :key="item.label">
            <nuxt-link :to="item.path"
              class="text-white/40 text-sm hover:text-white/70 transition-colors">
              {{ item.label }}
            </nuxt-link>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bottom bar -->
    <div class="border-t border-white/5 px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
      <p class="text-white/25 text-xs">© {{ new Date().getFullYear() }} Cinema. All rights reserved.</p>
      <div class="flex items-center gap-5">
        <nuxt-link v-for="item in legalLinks" :key="item.label" :to="item.path"
          class="text-white/25 text-xs hover:text-white/50 transition-colors">
          {{ item.label }}
        </nuxt-link>
      </div>
    </div>

  </footer>
</template>

<script setup>
const socialLinks = [
  { label: 'Facebook',  icon: 'lucide:facebook',  path: '#' },
  { label: 'Instagram', icon: 'lucide:instagram',  path: '#' },
  { label: 'Twitter',   icon: 'lucide:twitter',    path: '#' },
  { label: 'YouTube',   icon: 'lucide:youtube',    path: '#' },
]

const footerCols = [
  {
    heading: 'Company',
    links: [
      { label: 'About Us',    path: '/about' },
      { label: 'Contact Us',  path: '/contact' },
      { label: 'Careers',     path: '/careers' },
      { label: 'Press',       path: '/press' },
    ],
  },
  {
    heading: 'Movies',
    links: [
      { label: 'Now Showing', path: '/movies/now-showing' },
      { label: 'Coming Soon', path: '/movies/coming-soon' },
      { label: 'Cinemas',     path: '/cinemas' },
      { label: 'Offers',      path: '/offers' },
    ],
  },
]

const legalLinks = [
  { label: 'Privacy Policy',    path: '/privacy' },
  { label: 'Terms of Service',  path: '/terms' },
  { label: 'Cookie Policy',     path: '/cookies' },
]
</script>