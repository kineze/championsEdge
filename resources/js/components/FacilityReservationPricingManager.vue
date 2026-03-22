<template>
  <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/70">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h4 class="text-base font-bold text-slate-900 dark:text-white">Reservation Pricing Plans</h4>
        <p class="text-xs text-slate-500">Configure reservation rates for this facility.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition hover:brightness-110"
        @click="openCreateModal"
      >
        <i class="fas fa-plus"></i>
        <span>Add Pricing Plan</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Range Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Price</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Deposit</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Loading reservation prices...</td>
            </tr>
            <tr v-else-if="plans.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No reservation pricing plans found.</td>
            </tr>
            <tr v-for="plan in plans" :key="plan.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3 font-semibold text-slate-800 capitalize dark:text-slate-100">{{ prettifyRangeType(plan.range_type) }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatCurrency(plan.price) }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                <span v-if="plan.is_deposit_required">Yes ({{ formatCurrency(plan.deposit_amount) }})</span>
                <span v-else>No</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-slate-300"
                    title="View details"
                    @click="openViewModal(plan)"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-indigo-500/10 hover:text-indigo-700 dark:text-slate-300"
                    title="Edit plan"
                    @click="openEditModal(plan)"
                  >
                    <i class="fas fa-pen"></i>
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
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h5 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ editingId ? 'Update Reservation Pricing' : 'Add Reservation Pricing' }}
            </h5>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showFormModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="savePlan">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Range Type</label>
              <input
                type="text"
                value="Per Hour"
                disabled
                class="w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-700 outline-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
              />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Price</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
              <input v-model="form.is_deposit_required" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
              Deposit Required
            </label>

            <div v-if="form.is_deposit_required">
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Deposit Amount</label>
              <input v-model.number="form.deposit_amount" type="number" min="0" step="0.01" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div class="mt-1 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" @click="showFormModal = false">Cancel</button>
              <button type="submit" class="rounded-lg bg-cyan-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                {{ editingId ? 'Update Plan' : 'Create Plan' }}
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
      <div v-if="showViewModal && selectedPlan" class="fixed inset-0 z-[1700] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h5 class="text-lg font-semibold text-slate-900 dark:text-white">Reservation Plan Details</h5>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showViewModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <dl class="mt-4 space-y-3 text-sm">
            <div>
              <dt class="font-semibold text-slate-500">Range Type</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ prettifyRangeType(selectedPlan.range_type) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Price</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatCurrency(selectedPlan.price) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Deposit Required</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedPlan.is_deposit_required ? 'Yes' : 'No' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Deposit Amount</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedPlan.deposit_amount ? formatCurrency(selectedPlan.deposit_amount) : '-' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Created</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatDate(selectedPlan.created_at) }}</dd>
            </div>
          </dl>
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
const plans = ref([])
const showFormModal = ref(false)
const showViewModal = ref(false)
const editingId = ref(null)
const selectedPlan = ref(null)

const form = ref({
  range_type: 'per_hour',
  price: null,
  is_deposit_required: false,
  deposit_amount: null,
})

const resetForm = () => {
  form.value = {
    range_type: 'per_hour',
    price: null,
    is_deposit_required: false,
    deposit_amount: null,
  }
}

const fetchPlans = async () => {
  try {
    loading.value = true
    const res = await axios.get(`/api/facilities/${props.facilityId}/reservation-prices`)
    plans.value = Array.isArray(res.data.reservation_prices) ? res.data.reservation_prices : []
  } catch {
    toast.error('Failed to load reservation pricing')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingId.value = null
  resetForm()
  showFormModal.value = true
}

const openEditModal = (plan) => {
  editingId.value = plan.id
  form.value = {
    range_type: plan.range_type || 'per_hour',
    price: Number(plan.price),
    is_deposit_required: Boolean(plan.is_deposit_required),
    deposit_amount: plan.deposit_amount !== null ? Number(plan.deposit_amount) : null,
  }
  showFormModal.value = true
}

const openViewModal = async (plan) => {
  try {
    const res = await axios.get(`/api/facilities/${props.facilityId}/reservation-prices/${plan.id}`)
    selectedPlan.value = res.data.reservation_price || plan
    showViewModal.value = true
  } catch {
    toast.error('Failed to load reservation pricing details')
  }
}

const savePlan = async () => {
  const payload = {
    range_type: 'per_hour',
    price: Number(form.value.price),
    is_deposit_required: Boolean(form.value.is_deposit_required),
    deposit_amount: form.value.is_deposit_required ? Number(form.value.deposit_amount) : null,
  }

  try {
    if (editingId.value) {
      await axios.put(`/api/facilities/${props.facilityId}/reservation-prices/${editingId.value}`, payload)
      toast.success('Reservation pricing updated successfully')
    } else {
      await axios.post(`/api/facilities/${props.facilityId}/reservation-prices`, payload)
      toast.success('Reservation pricing created successfully')
    }

    showFormModal.value = false
    fetchPlans()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to save reservation pricing')
    }
  }
}

const prettifyRangeType = (type) => {
  return type === 'per_hour' ? 'Per Hour' : type
}

const formatPrice = (value) => {
  const num = Number(value || 0)
  return num.toFixed(2)
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

onMounted(fetchPlans)
</script>
