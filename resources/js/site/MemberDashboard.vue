<template>
  <section class="relative min-h-screen overflow-hidden bg-slate-100 pb-20 pt-36 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-16 top-16 h-64 w-64 rounded-full bg-cyan-300/25 blur-3xl dark:bg-cyan-500/20"></div>
      <div class="absolute -right-16 bottom-6 h-72 w-72 rounded-full bg-emerald-300/25 blur-3xl dark:bg-emerald-500/20"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-6">
      <div class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-cyan-50 via-white to-blue-50 p-7 shadow-sm backdrop-blur dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-cyan-700 dark:text-cyan-300">Member Dashboard</p>
            <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Subscription Overview</h1>
            <p class="mt-3 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
              Manage your membership subscription, check remaining days, and renew or cancel when needed.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <a
              href="/user/profile"
              class="rounded-xl border border-slate-300 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            >
              Edit Profile
            </a>
            <a
              href="/member/register"
              class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
            >
              Buy Subscription
            </a>
            <button
              type="button"
              class="rounded-xl border border-slate-300 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="logout"
            >
              Logout
            </button>
          </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Current Facility</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ active?.facility?.title || '-' }}</p>
          </div>

          <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Current Plan</p>
            <p class="mt-2 text-lg font-bold text-slate-900 capitalize dark:text-white">{{ active?.plan?.frequency || '-' }}</p>
          </div>

          <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Remaining Days</p>
            <p class="mt-2 text-2xl font-extrabold text-emerald-900 dark:text-emerald-100">{{ remainingDays }}</p>
          </div>

          <div class="rounded-2xl border border-blue-200/80 bg-blue-50/80 p-4 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">Total Subscriptions</p>
            <p class="mt-2 text-2xl font-extrabold text-blue-900 dark:text-blue-100">{{ stats.total_subscriptions || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Current Subscription</h2>
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="fetchSummary">Refresh</button>
          </div>

          <div v-if="loading" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Loading subscription details...
          </div>

          <div v-else-if="!active" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No subscription found.
            <div class="mt-4">
              <a href="/member/register" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-cyan-700">
                Buy Subscription
              </a>
            </div>
          </div>

          <div v-else class="mt-4 space-y-4">
            <div class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-950/60 sm:grid-cols-2">
              <p><span class="font-semibold">Facility:</span> {{ active.facility?.title || '-' }}</p>
              <p><span class="font-semibold">Plan:</span> {{ active.plan?.frequency || '-' }}</p>
              <p><span class="font-semibold">Price:</span> {{ formatCurrency(active.plan?.price) }}</p>
              <p><span class="font-semibold">Status:</span> <span class="capitalize">{{ active.is_blocked ? 'cancelled' : 'active' }}</span></p>
              <p><span class="font-semibold">Start Date:</span> {{ formatDate(active.subscription_start_date) }}</p>
              <p><span class="font-semibold">End Date:</span> {{ formatDate(active.subscription_end_date) }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <button
                type="button"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="actionLoading || active.is_blocked"
                @click="renew"
              >
                {{ actionLoading === 'renew' ? 'Renewing...' : 'Renew Subscription' }}
              </button>
              <button
                type="button"
                class="rounded-xl border border-red-300 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20"
                :disabled="actionLoading || active.is_blocked"
                @click="cancel"
              >
                {{ actionLoading === 'cancel' ? 'Cancelling...' : 'Cancel Subscription' }}
              </button>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
          <div
            v-if="!hasActiveSubscription"
            class="mb-4 rounded-xl border border-cyan-300 bg-cyan-50 p-4 text-sm text-cyan-900 dark:border-cyan-500/40 dark:bg-cyan-500/10 dark:text-cyan-200"
          >
            <p class="font-semibold">You do not have an active subscription.</p>
            <p class="mt-1">Purchase a subscription to access member facilities.</p>
            <a href="/member/register" class="mt-3 inline-flex rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700">
              Buy Subscription
            </a>
          </div>

          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Subscription History</h2>

          <div v-if="history.length === 0" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No history available.
          </div>

          <div v-else class="mt-4 space-y-2">
            <article
              v-for="item in history"
              :key="item.id"
              class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-700 dark:bg-slate-950/60"
            >
              <p class="font-semibold text-slate-900 dark:text-white">{{ item.facility?.title || '-' }} · {{ item.plan?.frequency || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(item.subscription_start_date) }} - {{ formatDate(item.subscription_end_date) }}</p>
              <p class="mt-1 text-xs">
                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" :class="item.is_blocked ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'">
                  {{ item.is_blocked ? 'Cancelled' : 'Active' }}
                </span>
              </p>
            </article>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">Purchased Training Sessions</h2>

          <div v-if="purchasedSessions.length === 0" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No purchased training sessions yet.
          </div>

          <div v-else class="mt-4 space-y-2">
            <article
              v-for="item in purchasedSessions"
              :key="item.id"
              class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-700 dark:bg-slate-950/60"
            >
              <p class="font-semibold text-slate-900 dark:text-white">{{ item.session_title || '-' }}</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ item.facility_title || '-' }} · Trainer: {{ item.trainer_name || '-' }}
              </p>
              <div class="mt-2 grid gap-1 text-xs text-slate-600 dark:text-slate-300">
                <p><span class="font-semibold">Purchased:</span> {{ formatDateTime(item.purchased_at) }}</p>
                <p><span class="font-semibold">Renewal Date:</span> {{ formatDate(item.renewal_date) }}</p>
                <p><span class="font-semibold">Frequency:</span> <span class="capitalize">{{ item.frequency || 'monthly' }}</span></p>
                <p><span class="font-semibold">Amount:</span> {{ formatCurrency(item.amount) }}</p>
                <p><span class="font-semibold">Transaction:</span> {{ item.transaction_id || '-' }}</p>
              </div>
              <div class="mt-3">
                <button
                  type="button"
                  class="rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="renewingSessionId === item.training_session_id"
                  @click="renewTrainingSession(item)"
                >
                  {{ renewingSessionId === item.training_session_id ? 'Initializing...' : 'Renew Session' }}
                </button>
              </div>
            </article>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80 lg:col-span-2">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Available Training Sessions</h2>
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="fetchAvailableTrainingSessions"
            >
              Refresh
            </button>
          </div>

          <div v-if="loadingAvailableSessions" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Loading available training sessions...
          </div>

          <div v-else-if="availableSessions.length === 0" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No training sessions available right now.
          </div>

          <div v-else class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <article
              v-for="session in availableSessions"
              :key="session.id"
              class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950/60"
            >
              <img
                :src="sessionImage(session)"
                :alt="session.session_title || 'Training session'"
                class="h-36 w-full object-cover"
              />
              <div class="p-3 text-sm">
                <p class="font-semibold text-slate-900 dark:text-white">{{ session.session_title || '-' }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  {{ session.facility?.title || '-' }} · Trainer: {{ session.trainer?.name || '-' }}
                </p>
                <div class="mt-2 flex items-center justify-between">
                  <p class="text-sm font-bold text-cyan-700">{{ formatCurrency(session.amount) }}</p>
                  <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ session.frequency || 'monthly' }}</p>
                </div>
                <div class="mt-3">
                  <span
                    v-if="isPurchasedSession(session.id)"
                    class="inline-flex rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300"
                  >
                    Purchased
                  </span>
                  <a
                    v-else
                    :href="`/training-sessions/${session.id}/purchase`"
                    class="inline-flex rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700"
                  >
                    Purchase Session
                  </a>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const actionLoading = ref('')
const renewingSessionId = ref(null)
const active = ref(null)
const history = ref([])
const purchasedSessions = ref([])
const availableSessions = ref([])
const loadingAvailableSessions = ref(false)
const stats = ref({
  total_subscriptions: 0,
  active_subscriptions: 0,
  cancelled_subscriptions: 0,
  total_training_sessions_purchased: 0,
})

const remainingDays = computed(() => {
  if (!active.value?.subscription_end_date || active.value?.is_blocked) return 0
  const end = new Date(active.value.subscription_end_date)
  const now = new Date()
  const oneDay = 1000 * 60 * 60 * 24
  const diff = Math.ceil((new Date(end.getFullYear(), end.getMonth(), end.getDate()) - new Date(now.getFullYear(), now.getMonth(), now.getDate())) / oneDay)
  return diff > 0 ? diff : 0
})

const hasActiveSubscription = computed(() => Number(stats.value?.active_subscriptions || 0) > 0)
const purchasedSessionIds = computed(() => new Set(
  (Array.isArray(purchasedSessions.value) ? purchasedSessions.value : [])
    .map((item) => Number(item.training_session_id))
    .filter((id) => Number.isFinite(id))
))

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

const formatDateTime = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const sessionImage = (session) => {
  if (!session) return '/images/slide1.jpg'
  if (session.display_image_url) return session.display_image_url
  const image = session.display_image
  if (!image) return '/images/slide1.jpg'
  if (String(image).startsWith('http')) return image
  return `/storage/${image}`
}

const isPurchasedSession = (sessionId) => purchasedSessionIds.value.has(Number(sessionId))

const fetchSummary = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/member/subscription/summary')
    active.value = data.active_subscription || null
    history.value = Array.isArray(data.subscriptions) ? data.subscriptions : []
    purchasedSessions.value = Array.isArray(data.training_session_purchases) ? data.training_session_purchases : []
    stats.value = data.stats || stats.value
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load member dashboard.')
  } finally {
    loading.value = false
  }
}

const fetchAvailableTrainingSessions = async () => {
  loadingAvailableSessions.value = true
  try {
    const { data } = await axios.get('/api/public/training-sessions')
    availableSessions.value = Array.isArray(data?.training_sessions) ? data.training_sessions : []
  } catch {
    availableSessions.value = []
    toast.error('Failed to load available training sessions.')
  } finally {
    loadingAvailableSessions.value = false
  }
}

const renew = async () => {
  if (!active.value?.id) return
  if (!window.confirm('Renew this subscription now?')) return

  actionLoading.value = 'renew'
  try {
    const { data } = await axios.post(`/api/member/subscription/${active.value.id}/renew`)
    const checkoutUrl = data?.payment?.checkout_url
    if (!checkoutUrl) {
      throw new Error('Missing checkout URL')
    }
    window.location.href = checkoutUrl
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to initialize renewal payment.')
  } finally {
    actionLoading.value = ''
  }
}

const cancel = async () => {
  if (!active.value?.id) return
  if (!window.confirm('Are you sure you want to cancel this subscription?')) return

  actionLoading.value = 'cancel'
  try {
    await axios.post(`/api/member/subscription/${active.value.id}/cancel`)
    toast.success('Subscription cancelled successfully.')
    await fetchSummary()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to cancel subscription.')
  } finally {
    actionLoading.value = ''
  }
}

const renewTrainingSession = async (item) => {
  if (!item?.training_session_id) return
  if (!window.confirm(`Renew "${item.session_title}" now?`)) return

  renewingSessionId.value = item.training_session_id
  try {
    const { data } = await axios.post(`/api/member/training-sessions/${item.training_session_id}/renew-payment`)
    const checkoutUrl = data?.payment?.checkout_url
    if (!checkoutUrl) {
      throw new Error('Missing checkout URL')
    }
    window.location.href = checkoutUrl
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to initialize training session renewal.')
  } finally {
    renewingSessionId.value = null
  }
}

const logout = async () => {
  try {
    await axios.post('/logout')
    window.location.href = '/'
  } catch {
    toast.error('Unable to logout right now.')
  }
}

onMounted(async () => {
  await Promise.all([fetchSummary(), fetchAvailableTrainingSessions()])
})
</script>
