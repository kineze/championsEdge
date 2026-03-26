<template>
  <div class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/80">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Inventory Manager</h3>
        <p class="text-xs text-slate-500">Manage inventory items and quantity usage.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-zinc-600 to-slate-700 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-zinc-500/20 transition hover:brightness-110"
        @click="openCreateModal"
      >
        <i class="fas fa-plus"></i>
        <span>Add Item</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Item</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Available Qty</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Used Qty</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Damaged Qty</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Description</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Loading inventory...</td>
            </tr>
            <tr v-else-if="inventories.length === 0">
              <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No inventory items found.</td>
            </tr>
            <tr v-for="item in inventories" :key="item.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ item.item_name }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.available_qty }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.used_qty }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.damaged_qty }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.description || '-' }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-zinc-500/10 hover:text-zinc-700 dark:text-slate-300"
                    title="Edit item"
                    @click="openEditModal(item)"
                  >
                    <i class="fas fa-pen"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-red-500/15 hover:text-red-700 dark:text-slate-300"
                    title="Delete item"
                    @click="deleteInventory(item.id)"
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
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ editingId ? 'Update Inventory Item' : 'Add Inventory Item' }}
            </h4>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="closeModal">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="saveInventory">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Item Name</label>
              <input v-model.trim="form.item_name" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-zinc-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Available Qty</label>
                <input v-model.number="form.available_qty" type="number" min="0" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-zinc-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Used Qty</label>
                <input v-model.number="form.used_qty" type="number" min="0" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-zinc-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
              <div>
                <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Damaged Qty</label>
                <input v-model.number="form.damaged_qty" type="number" min="0" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-zinc-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
              </div>
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Description</label>
              <textarea v-model.trim="form.description" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-zinc-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div class="mt-1 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="closeModal">Cancel</button>
              <button type="submit" class="rounded-lg bg-zinc-700 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800">
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

const inventories = ref([])
const loading = ref(false)
const showModal = ref(false)
const editingId = ref(null)

const form = ref({
  item_name: '',
  available_qty: 0,
  used_qty: 0,
  damaged_qty: 0,
  description: '',
})

const resetForm = () => {
  form.value = {
    item_name: '',
    available_qty: 0,
    used_qty: 0,
    damaged_qty: 0,
    description: '',
  }
}

const fetchInventory = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/inventories')
    inventories.value = Array.isArray(res.data.inventories) ? res.data.inventories : []
  } catch {
    toast.error('Failed to load inventory')
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
    item_name: item.item_name ?? '',
    available_qty: Number(item.available_qty ?? 0),
    used_qty: Number(item.used_qty ?? 0),
    damaged_qty: Number(item.damaged_qty ?? 0),
    description: item.description ?? '',
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveInventory = async () => {
  try {
    const payload = {
      item_name: form.value.item_name,
      available_qty: Number(form.value.available_qty ?? 0),
      used_qty: Number(form.value.used_qty ?? 0),
      damaged_qty: Number(form.value.damaged_qty ?? 0),
      description: form.value.description || null,
    }

    if (editingId.value) {
      await axios.put(`/api/inventories/${editingId.value}`, payload)
      toast.success('Inventory item updated successfully')
    } else {
      await axios.post('/api/inventories', payload)
      toast.success('Inventory item created successfully')
    }

    closeModal()
    fetchInventory()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to save inventory item')
    }
  }
}

const deleteInventory = async (id) => {
  if (!confirm('Delete this inventory item?')) return

  try {
    await axios.delete(`/api/inventories/${id}`)
    toast.success('Inventory item deleted successfully')
    fetchInventory()
  } catch {
    toast.error('Failed to delete inventory item')
  }
}

onMounted(fetchInventory)
</script>
