<!-- components/admin/AdminSidebar.vue (updated) -->
<template>
  <aside class="fixed left-0 top-0 z-40 h-screen transition-all duration-300 bg-[#0a0000] border-r border-white/10"
    :class="adminStore.sidebarCollapsed ? 'w-20' : 'w-64'">
    <!-- Logo & Toggle -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
      <div v-if="!adminStore.sidebarCollapsed" class="flex items-center gap-2">
        <Icon name="lucide:clapperboard" class="w-6 h-6 text-red-500" />
        <span class="text-white font-bold text-lg">Admin</span>
      </div>
      <div v-else class="mx-auto">
        <Icon name="lucide:clapperboard" class="w-6 h-6 text-red-500" />
      </div>
      <button @click="adminStore.toggleSidebar" class="p-1 rounded-md hover:bg-white/10 transition-colors"
        :class="adminStore.sidebarCollapsed ? 'mx-auto' : ''">
        <Icon :name="adminStore.sidebarCollapsed ? 'lucide:panel-right-open' : 'lucide:panel-right-close'"
          class="w-4 h-4 text-white/60" />
      </button>
    </div>

    <!-- Navigation Menu (Radix) -->
    <NavigationMenuRoot class="relative flex flex-col gap-1 p-4">
      <NavigationMenuList class="flex flex-col space-y-1">
        <NavigationMenuItem v-for="item in menuItems" :key="item.title">
          <NuxtLink v-if="!item.children" :to="item.to"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="[
              $route.path === item.to
                ? 'bg-red-600/20 text-red-500'
                : 'text-white/60 hover:bg-white/5 hover:text-white'
            ]">
            <Icon :name="item.icon" class="w-5 h-5" />
            <span v-if="!adminStore.sidebarCollapsed">{{ item.title }}</span>
          </NuxtLink>

          <CollapsibleRoot v-else v-model:open="item.open">
            <CollapsibleTrigger
              class="flex w-full items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-white/60 hover:bg-white/5 hover:text-white">
              <div class="flex items-center gap-3">
                <Icon :name="item.icon" class="w-5 h-5" />
                <span v-if="!adminStore.sidebarCollapsed">{{ item.title }}</span>
              </div>
              <Icon v-if="!adminStore.sidebarCollapsed"
                :name="item.open ? 'lucide:chevron-down' : 'lucide:chevron-right'"
                class="w-4 h-4 transition-transform" />
            </CollapsibleTrigger>

            <CollapsibleContent class="mt-1 ml-6 space-y-1">
              <NuxtLink v-for="child in item.children" :key="child.title" :to="child.to"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
                :class="[
                  $route.path === child.to
                    ? 'bg-red-600/20 text-red-500'
                    : 'text-white/50 hover:bg-white/5 hover:text-white'
                ]">
                <Icon :name="child.icon" class="w-4 h-4" />
                <span v-if="!adminStore.sidebarCollapsed">{{ child.title }}</span>
              </NuxtLink>
            </CollapsibleContent>
          </CollapsibleRoot>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenuRoot>

    <!-- Bottom Logout -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
      <button @click="handleLogout"
        class="flex w-full items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-white/60 hover:bg-white/5 hover:text-white transition-colors">
        <Icon name="lucide:log-out" class="w-5 h-5" />
        <span v-if="!adminStore.sidebarCollapsed">Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import {
  NavigationMenuRoot,
  NavigationMenuList,
  NavigationMenuItem,
  CollapsibleRoot,
  CollapsibleTrigger,
  CollapsibleContent,
} from 'radix-vue'
import { useAdminStore } from '~/stores/admin'
import { useAuthStore } from '~/stores/authStore'

const adminStore = useAdminStore()
const authStore = useAuthStore()

const menuItems = ref([
  {
    title: 'Dashboard',
    icon: 'lucide:layout-dashboard',
    to: '/admin',
  },
  {
    title: 'Users',
    icon: 'lucide:users',
    to: '/admin/users',
  },
  {
    title: 'Movies',
    icon: 'lucide:film',
    to: '/admin/movies',
  },
  {
    title: 'Cinemas',
    icon: 'lucide:map-pin',
    to: '/admin/cinemas',
  },  {
    title: 'Halls',
    icon: 'lucide:armchair',
    to: '/admin/halls',
  },
  {
    title: 'Showtimes',
    icon: 'lucide:clock',
    to: '/admin/showtimes',
  },
  {
    title: 'Bookings',
    icon: 'lucide:ticket',
    to: '/admin/bookings',
  },
  {
    title: 'Banners',
    icon: 'lucide:image',
    to: '/admin/banners',
  },
  {
    title: 'Settings',
    icon: 'lucide:settings',
    open: false,
    children: [
      { title: 'Profile', icon: 'lucide:user', to: '/admin/settings/profile' },
      { title: 'Security', icon: 'lucide:shield', to: '/admin/settings/security' },
    ],
  },
])

const handleLogout = async () => {
  await authStore.logout()
  // After logout, authStore clears token & user and navigates to /login
}
</script>