<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const sessions = ref([])
const loading = ref(false)
const isAuthenticated = document.body?.dataset?.authenticated === 'true'

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

const purchaseSession = (sessionId) => {
  window.location.href = `/training-sessions/${sessionId}/purchase`
}

onMounted(fetchSessions)
</script>

<template>
  <section class="relative overflow-hidden bg-slate-950 pb-20 pt-32 text-white">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-16 top-14 h-72 w-72 rounded-full bg-cyan-500/25 blur-3xl"></div>
      <div class="absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-amber-500/20 blur-3xl"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(34,211,238,0.12),transparent_45%)]"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-6">
      <div class="grid items-end gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Champions Edge</p>
          <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">Training Sessions</h1>
          <p class="mt-4 max-w-3xl text-sm text-slate-300 sm:text-base">
            Explore all available coaching programs and purchase your session with secure checkout.
          </p>
        </div>

        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur">
          <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-200">Purchase Access</p>
          <p class="mt-2 text-sm text-slate-200">
            {{ isAuthenticated ? 'Select a session and continue to payment.' : 'Login is required before payment.' }}
          </p>
          <a
            :href="isAuthenticated ? '/member/dashboard' : '/member/login'"
            class="mt-4 inline-flex rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400"
          >
            {{ isAuthenticated ? 'Go to Dashboard' : 'Member Login' }}
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="relative bg-slate-100 py-16 dark:bg-slate-950">
    <div class="mx-auto max-w-7xl px-6">
      <div class="mb-8 flex items-center justify-between">
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">Available Programs</h2>
      </div>

      <div v-if="loading" class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
        Loading training sessions...
      </div>

      <div v-else-if="sessions.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
        No training sessions available yet.
      </div>

      <div v-else class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="session in sessions"
          :key="session.id"
          class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-700 dark:bg-slate-900"
        >
          <div class="relative">
            <img
              :src="sessionImage(session)"
              :alt="session.session_title"
              class="h-52 w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-3">
              <p class="text-sm font-semibold text-white">{{ session.facility?.title || '-' }}</p>
            </div>
          </div>

          <div class="p-4">
            <h3 class="line-clamp-1 text-lg font-bold text-slate-900 dark:text-white">{{ session.session_title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-slate-600 dark:text-slate-300">
              {{ session.description || 'No description available.' }}
            </p>

            <div class="mt-3 grid gap-1 text-xs text-slate-500 dark:text-slate-400">
              <p><span class="font-semibold text-slate-700 dark:text-slate-200">Trainer:</span> {{ session.trainer?.name || '-' }}</p>
              <p><span class="font-semibold text-slate-700 dark:text-slate-200">Frequency:</span> <span class="capitalize">{{ session.frequency || 'monthly' }}</span></p>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <p class="text-base font-black text-cyan-700 dark:text-cyan-300">{{ formatCurrency(session.amount) }}</p>
              <a
                :href="`/training-sessions/${session.id}`"
                class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
              >
                Details
              </a>
            </div>

            <button
              type="button"
              class="mt-3 w-full rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
              @click="purchaseSession(session.id)"
            >
              {{ isAuthenticated ? 'Purchase Session' : 'Login & Purchase' }}
            </button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
