<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  sessionId: {
    type: [String, Number],
    required: true,
  },
})

const session = ref(null)
const loading = ref(false)
const isAuthenticated = document.body?.dataset?.authenticated === 'true'

const fetchSession = async () => {
  try {
    loading.value = true
    const res = await axios.get(`/api/public/training-sessions/${props.sessionId}`)
    session.value = res.data.training_session || null
  } catch {
    session.value = null
  } finally {
    loading.value = false
  }
}

const heroImage = computed(() => {
  if (session.value?.display_image_url) return session.value.display_image_url
  const image = session.value?.display_image
  if (!image) return '/images/slide1.jpg'
  if (String(image).startsWith('http')) return image
  return `/storage/${image}`
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

onMounted(() => {
  fetchSession()
})
</script>

<template>
  <section
    class="relative bg-slate-900 py-24 md:pt-32"
    :style="{ backgroundImage: `url(${heroImage})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
  >
    <div class="absolute inset-0 bg-[linear-gradient(115deg,rgba(2,6,23,0.9),rgba(2,6,23,0.55),rgba(8,47,73,0.7))]"></div>

    <div class="relative mx-auto max-w-7xl px-6">
      <a href="/training-sessions" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/30 bg-cyan-400/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/20">
        Back to Sessions
      </a>

      <div class="mt-6 max-w-3xl">
        <h1 class="text-4xl font-black tracking-tight text-white md:text-6xl">
          {{ session?.session_title || 'Training Session' }}
        </h1>
        <p class="mt-4 text-base leading-relaxed text-slate-200">
          {{ session?.description || 'No description available.' }}
        </p>
      </div>

      <div class="mt-8 grid max-w-3xl grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Trainer</p>
          <p class="mt-1 text-sm font-bold text-white">{{ session?.trainer?.name || '-' }}</p>
        </div>
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Facility</p>
          <p class="mt-1 text-sm font-bold text-white">{{ session?.facility?.title || '-' }}</p>
        </div>
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Frequency</p>
          <p class="mt-1 text-sm font-bold capitalize text-white">{{ session?.frequency || 'monthly' }}</p>
        </div>
        <div class="rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur">
          <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-300">Amount</p>
          <p class="mt-1 text-sm font-bold text-white">{{ formatCurrency(session?.amount) }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-6">
      <div v-if="loading" class="text-sm text-slate-500">Loading training session...</div>

      <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
          <h2 class="text-2xl font-bold text-slate-900">Session Overview</h2>
          <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700">
            {{ session?.description || 'No description available.' }}
          </p>

          <div class="mt-6 flex flex-wrap items-center gap-2">
            <a
              v-if="isAuthenticated && session?.id"
              :href="`/training-sessions/${session.id}/purchase`"
              class="inline-flex rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
            >
              Purchase Session
            </a>
            <a
              v-else
              href="/member/register"
              class="inline-flex rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
            >
              Register to Purchase
            </a>
            <a
              v-if="!isAuthenticated"
              href="/member/login"
              class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
            >
              Login
            </a>
          </div>
        </article>

        <aside class="space-y-5">
          <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <img :src="heroImage" :alt="session?.session_title || 'Training session'" class="h-56 w-full object-cover" />
            <div class="p-5">
              <h3 class="text-lg font-semibold text-slate-900">Pricing</h3>
              <p class="mt-2 text-3xl font-black text-cyan-700">{{ formatCurrency(session?.amount) }}</p>
              <p class="text-xs uppercase tracking-[0.12em] text-slate-500">Billed {{ session?.frequency || 'monthly' }}</p>
            </div>
          </div>

          <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Purchase This Session</h3>
            <p class="mt-2 text-sm text-slate-600">
              Continue with secure checkout to purchase this training session.
            </p>
            <a
              v-if="isAuthenticated && session?.id"
              :href="`/training-sessions/${session.id}/purchase`"
              class="mt-4 inline-flex rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
            >
              Purchase Session
            </a>
            <a
              v-else
              href="/member/register"
              class="mt-4 inline-flex rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
            >
              Register to Purchase
            </a>
          </div>
        </aside>
      </div>
    </div>
  </section>
</template>
