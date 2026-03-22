<template>
  <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700">Booking Calendar</p>
        <h3 class="mt-1 text-xl font-black tracking-tight text-slate-900">{{ titleText }}</h3>
        <p class="mt-1 text-sm text-slate-600">View booked slots by month, week, or day.</p>
      </div>
      <button
        type="button"
        class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="loading"
        @click="fetchEvents"
      >
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 md:p-3">
      <FullCalendar :options="calendarOptions" />
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

const props = defineProps({
  facilityId: {
    type: [String, Number],
    default: null,
  },
})

const toast = useToast()
const loading = ref(false)
const events = ref([])

const normalizedFacilityId = computed(() => {
  if (props.facilityId === null || props.facilityId === undefined || props.facilityId === '') return null
  return Number(props.facilityId)
})

const titleText = computed(() => {
  return normalizedFacilityId.value ? 'Facility Bookings' : 'All Facilities Bookings'
})

const fetchEvents = async () => {
  try {
    loading.value = true
    const params = {}
    if (normalizedFacilityId.value) params.facility_id = normalizedFacilityId.value

    const res = await axios.get('/api/public/reservations/calendar-events', { params })
    events.value = Array.isArray(res.data.events) ? res.data.events : []
  } catch {
    events.value = []
    toast.error('Failed to load booking calendar')
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

watch(
  () => normalizedFacilityId.value,
  () => {
    fetchEvents()
  }
)

onMounted(fetchEvents)
</script>

<style scoped>
:deep(.fc) {
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

:deep(.fc .fc-toolbar-title) {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
}

:deep(.fc .fc-button) {
  background: #0ea5e9;
  border-color: #0284c7;
  color: #fff;
  font-weight: 700;
  box-shadow: none;
}

:deep(.fc .fc-button:hover) {
  background: #0284c7;
  border-color: #0369a1;
}

:deep(.fc .fc-button-primary:not(:disabled).fc-button-active),
:deep(.fc .fc-button-primary:not(:disabled):active) {
  background: #0369a1;
  border-color: #0c4a6e;
}

:deep(.fc .fc-daygrid-day-number),
:deep(.fc .fc-col-header-cell-cushion) {
  color: #0f172a;
  font-weight: 600;
}

:deep(.fc .fc-event) {
  border-radius: 0.5rem;
  padding: 2px 4px;
  font-weight: 700;
}
</style>
