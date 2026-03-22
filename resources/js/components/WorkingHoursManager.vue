<template>
  <div class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/80">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Working Hours</h3>
        <p class="text-xs text-slate-500">Manage opening hours for all days at once.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="loading || saving"
        @click="saveAll"
      >
        <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
        <span>{{ saving ? 'Saving...' : 'Save All' }}</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Day</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Start Time</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">End Time</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Blocked</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Loading working hours...</td>
            </tr>
            <tr v-else v-for="row in rows" :key="row.day" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3 font-semibold capitalize text-slate-800 dark:text-slate-100">{{ row.day }}</td>
              <td class="px-4 py-3">
                <input
                  v-model="row.start_time"
                  type="time"
                  :disabled="row.is_blocked"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                />
              </td>
              <td class="px-4 py-3">
                <input
                  v-model="row.end_time"
                  type="time"
                  :disabled="row.is_blocked"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                />
              </td>
              <td class="px-4 py-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                  <input v-model="row.is_blocked" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @change="toggleBlocked(row)" />
                  Closed
                </label>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const WEEK_DAYS = [
  'monday',
  'tuesday',
  'wednesday',
  'thursday',
  'friday',
  'saturday',
  'sunday',
]

const loading = ref(false)
const saving = ref(false)
const rows = ref([])

const defaultRow = (day) => ({
  day,
  start_time: '08:00',
  end_time: '17:00',
  is_blocked: false,
})

const buildRows = (items) => {
  const map = new Map((Array.isArray(items) ? items : []).map((item) => [String(item.day), item]))

  rows.value = WEEK_DAYS.map((day) => {
    const existing = map.get(day)
    if (!existing) return defaultRow(day)

    return {
      day,
      start_time: existing.start_time ? String(existing.start_time).slice(0, 5) : '08:00',
      end_time: existing.end_time ? String(existing.end_time).slice(0, 5) : '17:00',
      is_blocked: Boolean(existing.is_blocked),
    }
  })
}

const fetchWorkingHours = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/working-hours')
    buildRows(res.data.working_hours)
  } catch {
    buildRows([])
    toast.error('Failed to load working hours')
  } finally {
    loading.value = false
  }
}

const toggleBlocked = (row) => {
  if (row.is_blocked) {
    row.start_time = ''
    row.end_time = ''
    return
  }

  if (!row.start_time) row.start_time = '08:00'
  if (!row.end_time) row.end_time = '17:00'
}

const saveAll = async () => {
  for (const row of rows.value) {
    if (row.is_blocked) continue

    if (!row.start_time || !row.end_time) {
      toast.error(`Please set both start and end time for ${row.day}.`)
      return
    }

    if (row.end_time <= row.start_time) {
      toast.error(`End time must be after start time for ${row.day}.`)
      return
    }
  }

  try {
    saving.value = true
    const payload = {
      hours: rows.value.map((row) => ({
        day: row.day,
        start_time: row.is_blocked ? null : row.start_time,
        end_time: row.is_blocked ? null : row.end_time,
        is_blocked: Boolean(row.is_blocked),
      })),
    }

    const res = await axios.put('/api/working-hours/bulk', payload)
    buildRows(res.data.working_hours)
    toast.success('Working hours saved successfully')
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
      if (err.response?.data?.message) {
        toast.error(err.response.data.message)
      }
    } else {
      toast.error('Failed to save working hours')
    }
  } finally {
    saving.value = false
  }
}

onMounted(fetchWorkingHours)
</script>
