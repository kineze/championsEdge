<template>
  <div class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/80">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Age Groups</h3>
        <p class="text-xs text-slate-500">Manage age ranges used in the system.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/20 transition hover:brightness-110"
        @click="openCreateModal"
      >
        <i class="fas fa-plus"></i>
        <span>Add Group</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Group Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Age Range</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Status</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Loading age groups...</td>
            </tr>
            <tr v-else-if="ageGroups.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No age groups found.</td>
            </tr>
            <tr v-for="group in ageGroups" :key="group.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ group.group_name }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ group.age_start }} - {{ group.age_end }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="group.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'"
                >
                  {{ group.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-indigo-500/10 hover:text-indigo-700 dark:text-slate-300"
                    title="Edit group"
                    @click="openEditModal(group)"
                  >
                    <i class="fas fa-pen"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-red-500/15 hover:text-red-700 dark:text-slate-300"
                    title="Delete group"
                    @click="deleteGroup(group.id)"
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
      <div v-if="showModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ editingId ? 'Update Age Group' : 'Add Age Group' }}
            </h4>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="closeModal">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="saveGroup">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Group Name</label>
              <input v-model="form.group_name" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Age Start</label>
                <input v-model.number="form.age_start" type="number" min="0" max="120" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Age End</label>
                <input v-model.number="form.age_end" type="number" min="0" max="120" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
              <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
              Active
            </label>

            <div class="mt-1 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="closeModal">Cancel</button>
              <button type="submit" class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                {{ editingId ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const ageGroups = ref([])
const loading = ref(false)
const showModal = ref(false)
const editingId = ref(null)

const form = ref({
  group_name: '',
  age_start: null,
  age_end: null,
  is_active: true,
})

const resetForm = () => {
  form.value = {
    group_name: '',
    age_start: null,
    age_end: null,
    is_active: true,
  }
}

const fetchAgeGroups = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/age-groups')
    ageGroups.value = Array.isArray(res.data.age_groups) ? res.data.age_groups : []
  } catch {
    toast.error('Failed to load age groups')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingId.value = null
  resetForm()
  showModal.value = true
}

const openEditModal = (group) => {
  editingId.value = group.id
  form.value = {
    group_name: group.group_name ?? '',
    age_start: Number(group.age_start ?? 0),
    age_end: Number(group.age_end ?? 0),
    is_active: Boolean(group.is_active),
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveGroup = async () => {
  try {
    if (editingId.value) {
      await axios.put(`/api/age-groups/${editingId.value}`, form.value)
      toast.success('Age group updated successfully')
    } else {
      await axios.post('/api/age-groups', form.value)
      toast.success('Age group created successfully')
    }
    closeModal()
    fetchAgeGroups()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to save age group')
    }
  }
}

const deleteGroup = async (id) => {
  if (!confirm('Delete this age group?')) return
  try {
    await axios.delete(`/api/age-groups/${id}`)
    toast.success('Age group deleted successfully')
    fetchAgeGroups()
  } catch {
    toast.error('Failed to delete age group')
  }
}

onMounted(fetchAgeGroups)
</script>
