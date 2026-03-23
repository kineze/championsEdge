<template>
  <section class="space-y-4 p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-indigo-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-indigo-700 dark:text-indigo-300">Analytics</p>
          <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Dashboard Overview</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Key reservation and payment performance for the last 6 months.</p>
        </div>
        <button
          type="button"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="loading"
          @click="fetchAnalytics"
        >
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </button>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/50">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total Reservations</p>
          <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_reservations }}</p>
        </article>
        <article class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Approved</p>
          <p class="mt-2 text-2xl font-extrabold text-emerald-900 dark:text-emerald-100">{{ kpis.approved_reservations }}</p>
        </article>
        <article class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Pending</p>
          <p class="mt-2 text-2xl font-extrabold text-amber-900 dark:text-amber-100">{{ kpis.pending_reservations }}</p>
        </article>
        <article class="rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm dark:border-sky-500/30 dark:bg-sky-500/10">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Active Members</p>
          <p class="mt-2 text-2xl font-extrabold text-sky-900 dark:text-sky-100">{{ kpis.active_members }}</p>
        </article>
        <article class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">Reservation Earnings</p>
          <p class="mt-2 text-xl font-extrabold text-indigo-900 dark:text-indigo-100">{{ formatCurrency(kpis.reservation_revenue) }}</p>
        </article>
        <article class="rounded-xl border border-violet-200 bg-violet-50 p-4 shadow-sm dark:border-violet-500/30 dark:bg-violet-500/10">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">Subscription Earnings</p>
          <p class="mt-2 text-xl font-extrabold text-violet-900 dark:text-violet-100">{{ formatCurrency(kpis.subscription_revenue) }}</p>
        </article>
      </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[1.7fr_1fr]">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Earnings Trend (6 Months)</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Reservation earnings vs subscription earnings</p>
        <div class="mt-4 h-80 xl:h-[34rem] rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/40">
          <canvas ref="earningsChartRef"></canvas>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
          <h3 class="text-base font-bold text-slate-900 dark:text-white">Reservation Status Breakdown</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">Draft, reserved, active, rejected</p>
          <div class="mt-4 space-y-3">
            <div v-for="row in breakdownRows" :key="row.key">
              <div class="mb-1 flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ row.label }}</span>
                <span class="text-slate-500 dark:text-slate-400">{{ row.value }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full transition-all duration-300" :class="row.color" :style="{ width: `${breakdownPercent(row.value)}%` }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
          <h3 class="text-base font-bold text-slate-900 dark:text-white">Membership Breakdown</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">Active, expired, blocked</p>
          <div class="mt-4 space-y-3">
            <div v-for="row in membershipRows" :key="row.key">
              <div class="mb-1 flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ row.label }}</span>
                <span class="text-slate-500 dark:text-slate-400">{{ row.value }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full transition-all duration-300" :class="row.color" :style="{ width: `${membershipPercent(row.value)}%` }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/50">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Latest Monthly Reservation Revenue</p>
            <p class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">{{ formatCurrency(latestRevenue) }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/50">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Latest Monthly Subscription Revenue</p>
            <p class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">{{ formatCurrency(latestSubscriptionRevenue) }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
)

const toast = useToast()
const loading = ref(false)

const kpis = ref({
  total_reservations: 0,
  approved_reservations: 0,
  pending_reservations: 0,
  active_members: 0,
  reservation_revenue: 0,
  subscription_revenue: 0,
})

const trends = ref({
  months: [],
  reservations: [],
  revenue: [],
  subscription_revenue: [],
})

const statusBreakdown = ref({
  draft: 0,
  reserved: 0,
  active: 0,
  rejected: 0,
})

const membershipBreakdown = ref({
  active: 0,
  expired: 0,
  blocked: 0,
})

const earningsChartRef = ref(null)

let earningsChart = null

const fetchAnalytics = async () => {
  try {
    loading.value = true
    const { data } = await axios.get('/api/admin/dashboard/analytics')
    kpis.value = data?.kpis || kpis.value
    trends.value = data?.trends || trends.value
    statusBreakdown.value = data?.status_breakdown || statusBreakdown.value
    membershipBreakdown.value = data?.membership_breakdown || membershipBreakdown.value
    await nextTick()
    renderCharts()
  } catch {
    toast.error('Failed to load dashboard analytics.')
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

const isDark = () => document.documentElement.classList.contains('dark')

const destroyCharts = () => {
  if (earningsChart) earningsChart.destroy()
  earningsChart = null
}

const renderCharts = () => {
  destroyCharts()

  const axisColor = isDark() ? '#94a3b8' : '#64748b'
  const gridColor = isDark() ? 'rgba(148,163,184,0.2)' : 'rgba(100,116,139,0.15)'
  const labelColor = isDark() ? '#cbd5e1' : '#334155'

  if (earningsChartRef.value) {
    earningsChart = new Chart(earningsChartRef.value, {
      type: 'line',
      data: {
        labels: trends.value.months || [],
        datasets: [
          {
            label: 'Reservation Earnings',
            data: trends.value.revenue || [],
            borderColor: '#06b6d4',
            backgroundColor: 'rgba(6,182,212,0.18)',
            tension: 0.35,
            borderWidth: 2.5,
            pointRadius: 3.5,
            pointHoverRadius: 4.5,
            fill: false,
          },
          {
            label: 'Subscription Earnings',
            data: trends.value.subscription_revenue || [],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.18)',
            tension: 0.35,
            borderWidth: 2.5,
            pointRadius: 3.5,
            pointHoverRadius: 4.5,
            fill: false,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: { color: labelColor, boxWidth: 12, usePointStyle: true },
          },
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.dataset.label}: ${formatCurrency(ctx.parsed.y)}`,
            },
          },
        },
        scales: {
          x: {
            ticks: { color: axisColor },
            grid: { color: gridColor },
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: axisColor,
              callback: (value) => formatCurrency(value),
            },
            grid: { color: gridColor },
          },
        },
      },
    })
  }

}

const breakdownTotal = computed(() => {
  return Number(statusBreakdown.value.draft || 0)
    + Number(statusBreakdown.value.reserved || 0)
    + Number(statusBreakdown.value.active || 0)
    + Number(statusBreakdown.value.rejected || 0)
})

const breakdownPercent = (value) => {
  const total = Math.max(1, breakdownTotal.value)
  return Math.max(4, Math.round((Number(value || 0) / total) * 100))
}

const breakdownRows = computed(() => [
  { key: 'draft', label: 'Draft', value: Number(statusBreakdown.value.draft || 0), color: 'bg-amber-500' },
  { key: 'reserved', label: 'Reserved', value: Number(statusBreakdown.value.reserved || 0), color: 'bg-emerald-500' },
  { key: 'active', label: 'Active', value: Number(statusBreakdown.value.active || 0), color: 'bg-sky-500' },
  { key: 'rejected', label: 'Rejected', value: Number(statusBreakdown.value.rejected || 0), color: 'bg-rose-500' },
])

const membershipTotal = computed(() => {
  return Number(membershipBreakdown.value.active || 0)
    + Number(membershipBreakdown.value.expired || 0)
    + Number(membershipBreakdown.value.blocked || 0)
})

const membershipPercent = (value) => {
  const total = Math.max(1, membershipTotal.value)
  return Math.max(4, Math.round((Number(value || 0) / total) * 100))
}

const membershipRows = computed(() => [
  { key: 'active', label: 'Active', value: Number(membershipBreakdown.value.active || 0), color: 'bg-emerald-500' },
  { key: 'expired', label: 'Expired', value: Number(membershipBreakdown.value.expired || 0), color: 'bg-amber-500' },
  { key: 'blocked', label: 'Blocked', value: Number(membershipBreakdown.value.blocked || 0), color: 'bg-rose-500' },
])

const latestRevenue = computed(() => {
  const values = Array.isArray(trends.value.revenue) ? trends.value.revenue : []
  if (values.length === 0) return 0
  return Number(values[values.length - 1] || 0)
})

const latestSubscriptionRevenue = computed(() => {
  const values = Array.isArray(trends.value.subscription_revenue) ? trends.value.subscription_revenue : []
  if (values.length === 0) return 0
  return Number(values[values.length - 1] || 0)
})

onMounted(fetchAnalytics)
onBeforeUnmount(destroyCharts)
</script>
