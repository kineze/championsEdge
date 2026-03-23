<template>
  <section class="calendar-shell relative overflow-hidden rounded-3xl border border-cyan-200/70 bg-white/95 p-4 shadow-2xl shadow-cyan-200/70 dark:border-cyan-200/40 dark:bg-slate-950 dark:shadow-cyan-900/20 md:p-6">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-12 top-6 h-40 w-40 rounded-full bg-cyan-300/30 blur-3xl dark:bg-cyan-400/20"></div>
      <div class="absolute -right-10 bottom-0 h-48 w-48 rounded-full bg-blue-300/25 blur-3xl dark:bg-blue-500/20"></div>
    </div>

    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700 dark:text-cyan-300">Admin Calendar</p>
        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">All Bookings</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Full calendar view of all reservations with customer names.</p>
      </div>
      <button
        type="button"
        class="rounded-xl border border-cyan-300/80 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-cyan-300/50 dark:bg-cyan-400/15 dark:text-cyan-100 dark:hover:bg-cyan-400/25"
        :disabled="loading"
        @click="fetchEvents"
      >
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>

    <div class="relative mt-4 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.12em]">
      <span class="rounded-full border border-amber-300/70 bg-amber-100/90 px-3 py-1 text-amber-700 dark:border-amber-300/50 dark:bg-amber-400/15 dark:text-amber-100">Draft</span>
      <span class="rounded-full border border-sky-300/70 bg-sky-100/90 px-3 py-1 text-sky-700 dark:border-sky-300/50 dark:bg-sky-400/15 dark:text-sky-100">Reserved</span>
      <span class="rounded-full border border-emerald-300/70 bg-emerald-100/90 px-3 py-1 text-emerald-700 dark:border-emerald-300/50 dark:bg-emerald-400/15 dark:text-emerald-100">Active</span>
      <span class="rounded-full border border-rose-300/70 bg-rose-100/90 px-3 py-1 text-rose-700 dark:border-rose-300/50 dark:bg-rose-400/15 dark:text-rose-100">Rejected</span>
    </div>

    <div class="relative mt-4 overflow-hidden rounded-2xl border border-cyan-200/60 bg-white/90 p-2 backdrop-blur dark:border-cyan-200/30 dark:bg-slate-900/80 md:p-3">
      <FullCalendar :options="calendarOptions" />
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

const toast = useToast()
const loading = ref(false)
const events = ref([])

const fetchEvents = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/admin/reservations/calendar-events')
    events.value = Array.isArray(res.data.events) ? res.data.events : []
  } catch {
    events.value = []
    toast.error('Failed to load admin booking calendar')
  } finally {
    loading.value = false
  }
}

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay',
  },
  buttonText: {
    today: 'Today',
    month: 'Month',
    week: 'Week',
    day: 'Day',
  },
  height: 'auto',
  events: events.value,
  eventDisplay: 'block',
  eventTimeFormat: {
    hour: '2-digit',
    minute: '2-digit',
    meridiem: 'short',
  },
  dayMaxEvents: true,
  nowIndicator: true,
  slotMinTime: '05:00:00',
  slotMaxTime: '23:00:00',
}))

onMounted(fetchEvents)
</script>

<style scoped>
:deep(.fc) {
  --fc-border-color: rgba(148, 163, 184, 0.35);
  --fc-page-bg-color: transparent;
  --fc-neutral-bg-color: rgba(226, 232, 240, 0.45);
  --fc-today-bg-color: rgba(14, 165, 233, 0.1);
  --fc-list-event-hover-bg-color: rgba(14, 165, 233, 0.16);
  font-family: 'Space Grotesk', 'Sora', 'Inter', sans-serif;
  color: #0f172a;
}

:deep(.dark .fc) {
  --fc-border-color: rgba(148, 163, 184, 0.25);
  --fc-neutral-bg-color: rgba(15, 23, 42, 0.4);
  --fc-today-bg-color: rgba(34, 211, 238, 0.12);
  color: #e2e8f0;
}

:deep(.fc .fc-toolbar.fc-header-toolbar) {
  margin-bottom: 0.8rem;
  gap: 0.6rem;
}

:deep(.fc .fc-toolbar-title) {
  font-size: 1rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  color: #0f172a;
  text-transform: uppercase;
}

:deep(.dark .fc .fc-toolbar-title) {
  color: #e2e8f0;
}

:deep(.fc .fc-toolbar-chunk) {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

:deep(.fc .fc-button) {
  background: linear-gradient(135deg, #0ea5e9, #2563eb);
  border: 1px solid rgba(125, 211, 252, 0.5);
  color: #ecfeff;
  font-weight: 700;
  border-radius: 0.65rem;
  box-shadow: 0 8px 18px rgba(2, 132, 199, 0.32);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

:deep(.fc .fc-button:hover) {
  background: linear-gradient(135deg, #06b6d4, #1d4ed8);
  border-color: rgba(125, 211, 252, 0.9);
  transform: translateY(-1px);
}

:deep(.fc .fc-button-primary:not(:disabled).fc-button-active),
:deep(.fc .fc-button-primary:not(:disabled):active) {
  background: linear-gradient(135deg, #0891b2, #1e3a8a);
  border-color: rgba(125, 211, 252, 0.9);
  box-shadow: 0 8px 18px rgba(14, 165, 233, 0.35);
}

:deep(.fc .fc-scrollgrid) {
  border-radius: 0.9rem;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.35);
}

:deep(.fc .fc-col-header-cell) {
  background: transparent;
}

:deep(.fc .fc-daygrid-day-number),
:deep(.fc .fc-col-header-cell-cushion) {
  color: #0f172a;
  font-weight: 600;
}

:deep(.dark .fc .fc-daygrid-day-number),
:deep(.dark .fc .fc-col-header-cell-cushion) {
  color: #dbeafe;
}

:deep(.fc .fc-daygrid-day-frame) {
  background: transparent;
}

:deep(.fc .fc-timegrid-slot-label),
:deep(.fc .fc-timegrid-axis-cushion) {
  color: #334155;
  font-weight: 500;
}

:deep(.dark .fc .fc-timegrid-slot-label),
:deep(.dark .fc .fc-timegrid-axis-cushion) {
  color: #cbd5e1;
}

:deep(.fc .fc-event-main) {
  text-shadow: 0 1px 1px rgba(2, 6, 23, 0.25);
}

:deep(.dark .fc .fc-event-main) {
  text-shadow: 0 1px 1px rgba(2, 6, 23, 0.45);
}

:deep(.fc .fc-event) {
  border-radius: 0.7rem;
  padding: 0.2rem 0.4rem;
  font-weight: 700;
  border-width: 1px !important;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.28);
}

:deep(.fc .fc-daygrid-more-link) {
  color: #67e8f9;
  font-weight: 700;
}

:deep(.fc-theme-standard td),
:deep(.fc-theme-standard th) {
  border-color: rgba(148, 163, 184, 0.3);
}

:deep(.dark .fc-theme-standard td),
:deep(.dark .fc-theme-standard th) {
  border-color: rgba(148, 163, 184, 0.22);
}

@media (max-width: 640px) {
  :deep(.fc .fc-toolbar) {
    align-items: flex-start;
    gap: 0.5rem;
  }

  :deep(.fc .fc-toolbar-title) {
    font-size: 0.86rem;
  }

  :deep(.fc .fc-button) {
    font-size: 0.68rem;
    padding: 0.3rem 0.5rem;
  }
}
</style>
