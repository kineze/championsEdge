<template>
  <nav id="siteNav" class="fixed left-0 right-0 top-0 z-50 transition-all duration-300">
    <div class="hidden border-b border-white/10 bg-slate-950/90 backdrop-blur md:block">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-2">
        <div class="flex items-center gap-3 text-sm text-slate-200">
          <a href="#" class="transition hover:text-cyan-300" aria-label="Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="transition hover:text-cyan-300" aria-label="Instagram">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="#" class="transition hover:text-cyan-300" aria-label="YouTube">
            <i class="fab fa-youtube"></i>
          </a>
          <a href="#" class="transition hover:text-cyan-300" aria-label="X">
            <i class="fab fa-x-twitter"></i>
          </a>
        </div>

        <div class="flex items-center gap-4 text-sm text-slate-200">
          <a href="mailto:info@championsedge.com" class="transition hover:text-cyan-300">info@championsedge.com</a>
          <a v-if="isAuthenticated" href="/member/dashboard" class="font-semibold transition hover:text-cyan-300">Member Dashboard</a>
          <a v-if="!isAuthenticated" href="/member/login" class="font-semibold transition hover:text-cyan-300">Login</a>
          <a v-if="!isAuthenticated" href="/member/register" class="font-semibold transition hover:text-cyan-300">Register</a>
        </div>
      </div>
    </div>

    <div :class="mainNavClass">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
        <a href="/" class="inline-flex items-center">
          <img src="/images/logo.png" alt="Logo" class="h-12 w-auto" />
        </a>

        <div class="hidden items-center space-x-6 text-white md:flex">
          <a href="/" class="transition hover:text-gray-200">Home</a>

          <div ref="desktopDropdownRef" class="relative">
            <button
              type="button"
              class="flex items-center gap-2 text-white transition hover:text-green-200"
              @click.prevent="desktopFacilitiesOpen = !desktopFacilitiesOpen"
            >
              Facilities
              <svg
                class="h-4 w-4 transition-transform duration-200"
                :class="desktopFacilitiesOpen ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div
              v-show="desktopFacilitiesOpen"
              class="absolute right-0 z-50 mt-5 w-64 overflow-hidden rounded-xl bg-white shadow-xl"
            >
              <div class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Facilities</div>
              <div class="max-h-64 overflow-y-auto">
                <template v-if="facilities.length">
                  <a
                    v-for="facility in facilities"
                    :key="facility.id"
                    :href="`/facilities/${facility.id}`"
                    class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                  >
                    {{ facility.title }}
                  </a>
                </template>
                <div v-else class="px-4 py-3 text-sm text-slate-400">No facilities</div>
              </div>
              <a href="/facilities" class="block px-4 py-3 text-sm font-semibold text-cyan-700 hover:bg-slate-50">View all</a>
            </div>
          </div>

          <a href="/about-us" class="transition hover:text-gray-200">About Us</a>
          <a href="/booking" class="rounded-lg bg-cyan-600 px-4 py-2 font-medium text-white shadow transition hover:bg-cyan-700">Book Now</a>
        </div>

        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-white transition hover:bg-white/20 md:hidden"
          @click="mobileOpen = true"
          aria-label="Open menu"
        >
          <i class="fas fa-bars text-base"></i>
        </button>
      </div>
    </div>

    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-[70] bg-slate-950/60 backdrop-blur-sm md:hidden"
      @click="mobileOpen = false"
    ></div>

    <aside
      class="fixed right-0 top-0 z-[80] h-full w-[84%] max-w-sm bg-slate-900 p-5 text-white shadow-2xl transition-transform duration-300 md:hidden"
      :class="mobileOpen ? 'translate-x-0' : 'translate-x-full'"
    >
      <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <a href="/" class="inline-flex items-center" @click="mobileOpen = false">
          <img src="/images/logo.png" alt="Logo" class="h-10 w-auto" />
        </a>
        <button
          type="button"
          class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/15 bg-white/10 transition hover:bg-white/20"
          @click="mobileOpen = false"
          aria-label="Close menu"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="mt-5 space-y-3 text-sm">
        <a href="/" class="block rounded-lg border border-white/10 px-3 py-2 transition hover:bg-white/10" @click="mobileOpen = false">Home</a>
        <a href="/about-us" class="block rounded-lg border border-white/10 px-3 py-2 transition hover:bg-white/10" @click="mobileOpen = false">About Us</a>
        <a href="/facilities" class="block rounded-lg border border-white/10 px-3 py-2 transition hover:bg-white/10" @click="mobileOpen = false">Facilities</a>
        <a v-if="isAuthenticated" href="/member/dashboard" class="block rounded-lg border border-white/10 px-3 py-2 transition hover:bg-white/10" @click="mobileOpen = false">Member Dashboard</a>

        <button
          type="button"
          class="flex w-full items-center justify-between rounded-lg border border-white/10 px-3 py-2 transition hover:bg-white/10"
          @click="mobileFacilitiesOpen = !mobileFacilitiesOpen"
        >
          <span>Facility Links</span>
          <i class="fas fa-chevron-down text-xs transition-transform" :class="mobileFacilitiesOpen ? 'rotate-180' : ''"></i>
        </button>

        <div v-if="mobileFacilitiesOpen" class="space-y-1 rounded-lg border border-white/10 bg-white/5 p-2">
          <template v-if="facilities.length">
            <a
              v-for="facility in facilities"
              :key="`mobile_${facility.id}`"
              :href="`/facilities/${facility.id}`"
              class="block rounded-md px-2 py-2 text-xs text-slate-200 transition hover:bg-white/10"
              @click="mobileOpen = false"
            >
              {{ facility.title }}
            </a>
          </template>
          <p v-else class="px-2 py-2 text-xs text-slate-400">No facilities found</p>
        </div>

        <a href="/booking" class="mt-2 block rounded-lg bg-cyan-600 px-3 py-2 font-semibold text-white transition hover:bg-cyan-700" @click="mobileOpen = false">Book Now</a>
      </div>

      <div class="mt-6 border-t border-white/10 pt-4">
        <p v-if="!isAuthenticated" class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Account</p>
        <div v-if="!isAuthenticated" class="grid grid-cols-2 gap-2">
          <a href="/member/login" class="rounded-lg border border-cyan-400/40 bg-cyan-500/10 px-3 py-2 text-center text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20" @click="mobileOpen = false">Login</a>
          <a href="/member/register" class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-center text-sm font-semibold text-white transition hover:bg-white/20" @click="mobileOpen = false">Register</a>
        </div>
      </div>
    </aside>
  </nav>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import axios from 'axios'

const facilities = ref([])
const desktopFacilitiesOpen = ref(false)
const mobileFacilitiesOpen = ref(false)
const mobileOpen = ref(false)
const hasScrolled = ref(false)
const desktopDropdownRef = ref(null)
const transparentNav = document.body?.dataset?.transparentNav === 'true'
const isAuthenticated = document.body?.dataset?.authenticated === 'true'

const fetchFacilities = async () => {
  try {
    const res = await axios.get('/api/public/facilities')
    facilities.value = Array.isArray(res.data?.facilities) ? res.data.facilities : []
  } catch {
    facilities.value = []
  }
}

const onScroll = () => {
  hasScrolled.value = window.scrollY > 30
}

const onClickOutside = (event) => {
  if (!desktopFacilitiesOpen.value) return
  if (desktopDropdownRef.value && !desktopDropdownRef.value.contains(event.target)) {
    desktopFacilitiesOpen.value = false
  }
}

const mainNavClass = computed(() => {
  if (!transparentNav) return 'bg-gray-800 shadow'
  return hasScrolled.value ? 'bg-gray-900/95 shadow-lg backdrop-blur' : 'bg-transparent'
})

watch(mobileOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

onMounted(() => {
  fetchFacilities()
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
  document.addEventListener('click', onClickOutside)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('click', onClickOutside)
  document.body.style.overflow = ''
})
</script>
