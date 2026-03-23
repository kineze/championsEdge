<template>
  <section class="relative min-h-screen bg-slate-100 pb-20 pt-36 text-slate-900">
    <div class="mx-auto max-w-5xl px-6">
      <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-cyan-700">Training Session Checkout</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Purchase Training Session</h1>
        <p class="mt-2 text-sm text-slate-600">Only active subscribed members can proceed with payment.</p>
      </div>

      <div v-if="loading" class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
        Loading checkout details...
      </div>

      <div v-else class="mt-6 grid gap-6 lg:grid-cols-[1.15fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">Member Details</h2>
          <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <p><span class="font-semibold">Name:</span> {{ data.user?.name || '-' }}</p>
            <p><span class="font-semibold">Email:</span> {{ data.user?.email || '-' }}</p>
            <p><span class="font-semibold">Phone:</span> {{ data.user?.profile?.phone || '-' }}</p>
            <p><span class="font-semibold">NIC:</span> {{ data.user?.profile?.nic || '-' }}</p>
          </div>

          <h3 class="mt-6 text-base font-bold text-slate-900">Active Subscription</h3>
          <div
            class="mt-3 rounded-xl p-4 text-sm"
            :class="data.active_subscription ? 'border border-emerald-200 bg-emerald-50' : 'border border-amber-200 bg-amber-50'"
          >
            <template v-if="data.active_subscription">
              <p><span class="font-semibold">Facility:</span> {{ data.active_subscription?.facility?.title || '-' }}</p>
              <p><span class="font-semibold">Plan:</span> {{ data.active_subscription?.plan?.frequency || '-' }}</p>
              <p><span class="font-semibold">Valid Until:</span> {{ formatDate(data.active_subscription?.subscription_end_date) }}</p>
            </template>
            <template v-else>
              <p class="font-semibold text-amber-800">No active subscription found.</p>
              <p class="mt-1 text-amber-700">You need an active member subscription to complete this payment.</p>
            </template>
          </div>
        </div>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">Session Summary</h2>
          <img
            :src="sessionImage(data.training_session)"
            :alt="data.training_session?.session_title || 'Training session'"
            class="mt-4 h-48 w-full rounded-xl object-cover"
          />

          <div class="mt-4 text-sm">
            <p class="text-base font-bold text-slate-900">{{ data.training_session?.session_title || '-' }}</p>
            <p class="mt-1 text-slate-600">Trainer: {{ data.training_session?.trainer?.name || '-' }}</p>
            <p class="text-slate-600">Facility: {{ data.training_session?.facility?.title || '-' }}</p>
            <p class="mt-2 text-slate-600">Frequency: <span class="capitalize">{{ data.training_session?.frequency || 'monthly' }}</span></p>
          </div>

          <div class="mt-4 rounded-xl border border-cyan-200 bg-cyan-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-cyan-700">Amount</p>
            <p class="mt-1 text-2xl font-black text-cyan-900">{{ formatCurrency(data.training_session?.amount) }}</p>
          </div>

          <button
            type="button"
            class="mt-5 w-full rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="paying || !data.can_purchase"
            @click="payNow"
          >
            {{ !data.can_purchase ? 'Purchase Not Available' : (paying ? 'Initializing Payment...' : 'Pay with Seylan') }}
          </button>
          <p v-if="data.eligibility_message" class="mt-2 text-xs text-amber-700">{{ data.eligibility_message }}</p>
        </aside>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  sessionId: {
    type: [String, Number],
    required: true,
  },
})

const toast = useToast()
const loading = ref(false)
const paying = ref(false)
const data = ref({
  user: null,
  training_session: null,
  active_subscription: null,
  already_purchased: false,
  can_purchase: false,
  eligibility_message: null,
})

const fetchMeta = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/member/training-sessions/${props.sessionId}/purchase-meta`)
    data.value = res.data || data.value
  } catch (error) {
    const status = error?.response?.status
    const message = error?.response?.data?.message || 'Unable to load checkout details.'
    toast.error(message)

    if (status === 401) {
      window.location.href = '/member/login'
      return
    }

    window.location.href = `/training-sessions/${props.sessionId}`
  } finally {
    loading.value = false
  }
}

const payNow = async () => {
  paying.value = true
  try {
    const res = await axios.post(`/api/member/training-sessions/${props.sessionId}/initiate-payment`)
    const checkoutUrl = res?.data?.payment?.checkout_url
    if (!checkoutUrl) throw new Error('Missing checkout URL')
    window.location.href = checkoutUrl
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Unable to initialize payment.')
  } finally {
    paying.value = false
  }
}

const sessionImage = (session) => {
  if (!session) return '/images/slide1.jpg'
  if (session.display_image_url) return session.display_image_url
  if (!session.display_image) return '/images/slide1.jpg'
  if (String(session.display_image).startsWith('http')) return session.display_image
  return `/storage/${session.display_image}`
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString()
}

onMounted(fetchMeta)
</script>
