<template>
  <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/70">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h4 class="text-base font-bold text-slate-900 dark:text-white">Training Sessions</h4>
        <p class="text-xs text-slate-500">Create and manage training sessions for this facility.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition hover:brightness-110"
        @click="openCreateModal"
      >
        <i class="fas fa-plus"></i>
        <span>Add Session</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Title</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Trainer</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Frequency</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Loading training sessions...</td>
            </tr>
            <tr v-else-if="sessions.length === 0">
              <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No training sessions found.</td>
            </tr>
            <tr v-for="session in sessions" :key="session.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <img
                    v-if="imageUrl(session)"
                    :src="imageUrl(session)"
                    :alt="session.session_title"
                    class="h-10 w-10 rounded-lg object-cover"
                  />
                  <div>
                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ session.session_title }}</p>
                    <p v-if="session.display_image" class="text-xs text-slate-500">Image uploaded</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ session.trainer?.name || '-' }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatCurrency(session.amount) }}</td>
              <td class="px-4 py-3 capitalize text-slate-700 dark:text-slate-200">{{ session.frequency || 'monthly' }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-slate-300"
                    title="View details"
                    @click="openViewModal(session)"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-indigo-500/10 hover:text-indigo-700 dark:text-slate-300"
                    title="Edit session"
                    @click="openEditModal(session)"
                  >
                    <i class="fas fa-pen"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-red-500/10 hover:text-red-700 dark:text-slate-300"
                    title="Delete session"
                    @click="openDeleteModal(session)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showFormModal" class="fixed inset-0 z-[1600] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h5 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ editingId ? 'Update Training Session' : 'Add Training Session' }}
            </h5>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showFormModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="saveSession">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Session Title</label>
              <input v-model="form.session_title" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Trainer</label>
                <select v-model="form.trainer_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                  <option disabled value="">Select trainer</option>
                  <option v-for="trainer in trainers" :key="trainer.id" :value="String(trainer.id)">{{ trainer.name }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Amount</label>
                <input v-model.number="form.amount" type="number" min="0" step="0.01" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Display Image</label>
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-cyan-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                @change="onImageSelected"
              />
              <p class="mt-1 text-xs text-slate-500">Upload JPG/PNG/WebP up to 5MB.</p>
            </div>

            <div v-if="form.display_image_preview || form.existing_image_url" class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/50">
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Image Preview</p>
              <img
                :src="form.display_image_preview || form.existing_image_url"
                alt="Session preview"
                class="h-36 w-full rounded-lg object-cover"
              />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Frequency</label>
              <input value="Monthly" disabled class="w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700 outline-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Description</label>
              <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"></textarea>
            </div>

            <div class="mt-1 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="showFormModal = false">Cancel</button>
              <button type="submit" class="rounded-lg bg-cyan-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                {{ editingId ? 'Update Session' : 'Create Session' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showViewModal && selectedSession" class="fixed inset-0 z-[1700] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h5 class="text-lg font-semibold text-slate-900 dark:text-white">Training Session Details</h5>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showViewModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <dl class="mt-4 space-y-3 text-sm">
            <div>
              <dt class="font-semibold text-slate-500">Title</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedSession.session_title }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Trainer</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedSession.trainer?.name || '-' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Amount</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatCurrency(selectedSession.amount) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Frequency</dt>
              <dd class="mt-0.5 capitalize text-slate-800 dark:text-slate-100">{{ selectedSession.frequency || 'monthly' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Display Image</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">
                <img
                  v-if="imageUrl(selectedSession)"
                  :src="imageUrl(selectedSession)"
                  alt="Session"
                  class="h-36 w-full rounded-lg object-cover"
                />
                <span v-else>-</span>
              </dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Description</dt>
              <dd class="mt-0.5 whitespace-pre-line text-slate-800 dark:text-slate-100">{{ selectedSession.description || '-' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Created</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatDate(selectedSession.created_at) }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </transition>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showDeleteModal && deletingSession" class="fixed inset-0 z-[1700] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <h5 class="text-lg font-semibold text-slate-900 dark:text-white">Delete Training Session</h5>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Delete <span class="font-semibold">{{ deletingSession.session_title }}</span>?
          </p>
          <div class="mt-4 flex justify-end gap-2">
            <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="showDeleteModal = false">Cancel</button>
            <button type="button" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-red-700" @click="deleteSession">Delete</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  facilityId: {
    type: [String, Number],
    required: true,
  },
})

const toast = useToast()

const loading = ref(false)
const sessions = ref([])
const trainers = ref([])
const showFormModal = ref(false)
const showViewModal = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const selectedSession = ref(null)
const deletingSession = ref(null)
const fileInputRef = ref(null)

const form = ref({
  session_title: '',
  trainer_id: '',
  description: '',
  amount: null,
  frequency: 'monthly',
  display_image_file: null,
  display_image_preview: '',
  existing_image_url: '',
})

const resetForm = () => {
  form.value = {
    session_title: '',
    trainer_id: '',
    description: '',
    amount: null,
    frequency: 'monthly',
    display_image_file: null,
    display_image_preview: '',
    existing_image_url: '',
  }

  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const imageUrl = (session) => {
  if (!session) return ''
  if (session.display_image_url) return session.display_image_url
  if (!session.display_image) return ''
  if (String(session.display_image).startsWith('http')) return session.display_image
  return `/storage/${session.display_image}`
}

const onImageSelected = (event) => {
  const file = event.target.files?.[0] || null
  form.value.display_image_file = file
  form.value.display_image_preview = file ? URL.createObjectURL(file) : ''
}

const fetchSessions = async () => {
  try {
    loading.value = true
    const res = await axios.get(`/api/facilities/${props.facilityId}/training-sessions`)
    sessions.value = Array.isArray(res.data.training_sessions) ? res.data.training_sessions : []
  } catch {
    toast.error('Failed to load training sessions')
  } finally {
    loading.value = false
  }
}

const fetchTrainers = async () => {
  try {
    const res = await axios.get('/api/users')
    trainers.value = Array.isArray(res.data.users)
      ? res.data.users.map((user) => ({ id: user.id, name: user.name }))
      : []
  } catch {
    toast.error('Failed to load trainers')
  }
}

const openCreateModal = () => {
  editingId.value = null
  resetForm()
  showFormModal.value = true
}

const openEditModal = (session) => {
  editingId.value = session.id
  form.value = {
    session_title: session.session_title || '',
    trainer_id: String(session.trainer_id || ''),
    description: session.description || '',
    amount: Number(session.amount || 0),
    frequency: session.frequency || 'monthly',
    display_image_file: null,
    display_image_preview: '',
    existing_image_url: imageUrl(session),
  }

  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }

  showFormModal.value = true
}

const openViewModal = async (session) => {
  try {
    const res = await axios.get(`/api/facilities/${props.facilityId}/training-sessions/${session.id}`)
    selectedSession.value = res.data.training_session || session
    showViewModal.value = true
  } catch {
    toast.error('Failed to load training session details')
  }
}

const openDeleteModal = (session) => {
  deletingSession.value = session
  showDeleteModal.value = true
}

const saveSession = async () => {
  const payload = new FormData()
  payload.append('session_title', form.value.session_title)
  payload.append('trainer_id', String(Number(form.value.trainer_id)))
  payload.append('description', form.value.description || '')
  payload.append('amount', String(Number(form.value.amount || 0)))
  payload.append('frequency', 'monthly')

  if (form.value.display_image_file) {
    payload.append('display_image', form.value.display_image_file)
  }

  try {
    if (editingId.value) {
      payload.append('_method', 'PUT')
      await axios.post(`/api/facilities/${props.facilityId}/training-sessions/${editingId.value}`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      toast.success('Training session updated successfully')
    } else {
      await axios.post(`/api/facilities/${props.facilityId}/training-sessions`, payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      toast.success('Training session created successfully')
    }

    showFormModal.value = false
    fetchSessions()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to save training session')
    }
  }
}

const deleteSession = async () => {
  if (!deletingSession.value) return

  try {
    await axios.delete(`/api/facilities/${props.facilityId}/training-sessions/${deletingSession.value.id}`)
    toast.success('Training session deleted successfully')
    showDeleteModal.value = false
    deletingSession.value = null
    fetchSessions()
  } catch {
    toast.error('Failed to delete training session')
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

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

onMounted(() => {
  fetchSessions()
  fetchTrainers()
})
</script>
