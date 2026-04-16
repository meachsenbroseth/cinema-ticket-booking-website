<template>
  <div class="min-h-screen bg-[#0d0000] text-white flex flex-col">
    <header class="sticky top-0 z-[100] bg-gradient-to-b from-[#1a0505]/95 to-[#0d0d0d]/95 backdrop-blur-sm text-white border-b border-white/10">
      <Navbar />
    </header>
    <main class="flex-1">
      <slot />
    </main>
    <footer>
      <Footer />
    </footer>
  </div>
</template>