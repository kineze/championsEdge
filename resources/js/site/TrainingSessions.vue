<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const sessions = ref([])
const loading = ref(false)

const fetchSessions = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/public/training-sessions')
    sessions.value = Array.isArray(res.data.training_sessions) ? res.data.training_sessions : []
  } catch {
    sessions.value = []
  } finally {
    loading.value = false
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

const sessionImage = (session) => {
  if (!session) return '/images/slide1.jpg'
  if (session.display_image_url) return session.display_image_url
  const path = session.display_image
  if (!path) return '/images/slide1.jpg'
  if (String(path).startsWith('http')) return path
  return `/storage/${path}`
}

onMounted(() => {
  fetchSessions()
})
</script>

<template>
  <section
    class="relative flex h-[52vh] items-center justify-center bg-cover bg-center"
    style="background-image: url('/images/gym.jpg');"
  >
    <div class="absolute inset-0 bg-black/60"></div>
    <h1 class="relative text-center text-4xl font-bold text-white md:text-6xl">
      Training Sessions
    </h1>
  </section>

  <section class="bg-slate-100 py-20">
    <div class="mx-auto max-w-7xl px-6">
      <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 class="text-3xl font-bold text-slate-900">Available Programs</h2>
          <p class="mt-1 text-sm text-slate-500">Browse all monthly training sessions.</p>
        </div>
      </div>

      <div v-if="loading" class="text-sm text-slate-500">Loading training sessions...</div>

      <div v-else class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <a
          v-for="session in sessions"
          :key="session.id"
          :href="`/training-sessions/${session.id}`"
          class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
        >
          <img
            :src="sessionImage(session)"
            :alt="session.session_title"
            class="h-48 w-full object-cover"
          />
          <div class="p-4">
            <h3 class="line-clamp-1 text-lg font-semibold text-slate-900">{{ session.session_title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ session.description || 'No description available.' }}</p>
            <div class="mt-3 space-y-1 text-xs text-slate-500">
              <p><span class="font-semibold text-slate-700">Trainer:</span> {{ session.trainer?.name || '-' }}</p>
              <p><span class="font-semibold text-slate-700">Facility:</span> {{ session.facility?.title || '-' }}</p>
              <p><span class="font-semibold text-slate-700">Frequency:</span> {{ session.frequency || 'monthly' }}</p>
            </div>
            <div class="mt-4 flex items-center justify-between">
              <span class="text-sm font-bold text-cyan-700">{{ formatCurrency(session.amount) }}</span>
              <span class="text-xs font-semibold uppercase tracking-[0.08em] text-cyan-700 group-hover:text-cyan-900">View Details</span>
            </div>
          </div>
        </a>

        <div v-if="sessions.length === 0" class="col-span-full text-sm text-slate-500">
          No training sessions available yet.
        </div>
      </div>
    </div>
  </section>
</template>
