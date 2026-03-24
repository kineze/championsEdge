<template>
  <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/70">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h4 class="text-base font-bold text-slate-900 dark:text-white">Facility Extra Items</h4>
        <p class="text-xs text-slate-500">Manage add-on items for this facility.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition hover:brightness-110"
        @click="openCreateModal"
      >
        <i class="fas fa-plus"></i>
        <span>Add Extra Item</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Unit Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Price / Unit</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Loading extra items...</td>
            </tr>
            <tr v-else-if="items.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No extra items found.</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ item.name }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.unit_type }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatCurrency(item.price_per_unit) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-indigo-500/10 hover:text-indigo-700 dark:text-slate-300"
                    title="Edit item"
                    @click="openEditModal(item)"
                  >
                    <i class="fas fa-pen"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-rose-500/10 hover:text-rose-700 dark:text-slate-300"
                    title="Delete item"
                    @click="deleteItem(item)"
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
      <div v-if="showModal" class="fixed inset-0 z-[1600] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h5 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ editingId ? 'Update Extra Item' : 'Add Extra Item' }}
            </h5>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="saveItem">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Name</label>
              <input v-model.trim="form.name" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Unit Type</label>
              <input v-model.trim="form.unit_type" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Price Per Unit</label>
              <input v-model.number="form.price_per_unit" type="number" min="0" step="0.01" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div class="mt-1 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="showModal = false">Cancel</button>
              <button type="submit" class="rounded-lg bg-cyan-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                {{ editingId ? 'Update Item' : 'Create Item' }}
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

const props = defineProps({
  facilityId: {
    type: [String, Number],
    required: true
  }
})

const toast = useToast()
const loading = ref(false)
const items = ref([])
const showModal = ref(false)
const editingId = ref(null)
const form = ref({
  name: '',
  unit_type: '',
  price_per_unit: null,
})

const resetForm = () => {
  form.value = {
    name: '',
    unit_type: '',
    price_per_unit: null,
  }
}

const fetchItems = async () => {
  try {
    loading.value = true
    const res = await axios.get(`/api/facilities/${props.facilityId}/extra-items`)
    items.value = Array.isArray(res.data.extra_items) ? res.data.extra_items : []
  } catch {
    toast.error('Failed to load extra items.')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingId.value = null
  resetForm()
  showModal.value = true
}

const openEditModal = (item) => {
  editingId.value = item.id
  form.value = {
    name: item.name || '',
    unit_type: item.unit_type || '',
    price_per_unit: Number(item.price_per_unit || 0),
  }
  showModal.value = true
}

const saveItem = async () => {
  const payload = {
    name: form.value.name,
    unit_type: form.value.unit_type,
    price_per_unit: Number(form.value.price_per_unit || 0),
  }

  try {
    if (editingId.value) {
      await axios.put(`/api/facilities/${props.facilityId}/extra-items/${editingId.value}`, payload)
      toast.success('Extra item updated successfully.')
    } else {
      await axios.post(`/api/facilities/${props.facilityId}/extra-items`, payload)
      toast.success('Extra item created successfully.')
    }
    showModal.value = false
    await fetchItems()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to save extra item.')
    }
  }
}

const deleteItem = async (item) => {
  if (!window.confirm(`Delete "${item.name}"?`)) return
  try {
    await axios.delete(`/api/facilities/${props.facilityId}/extra-items/${item.id}`)
    toast.success('Extra item deleted successfully.')
    await fetchItems()
  } catch {
    toast.error('Failed to delete extra item.')
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

onMounted(fetchItems)
</script>
