<template>
  <section class="p-3 sm:p-4">
    <div v-if="props.mode !== 'approved'" class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-emerald-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Administration</p>
          <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Reservation Requests</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review reservation requests and approve or reject them.</p>
        </div>
        <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-3 text-white shadow-md shadow-emerald-500/20">
          <p class="text-[11px] uppercase tracking-[0.14em] text-emerald-100">Total Requests</p>
          <p class="text-2xl font-bold">{{ reservations.length }}</p>
        </div>
      </div>

      <div class="mt-5 flex items-center gap-3">
        <select
          v-model="statusFilter"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-emerald-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
          @change="fetchReservations"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="reserved">Reserved</option>
          <option value="active">Active</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900/80">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Facility</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Start</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Payment</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
              <tr v-if="loading">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Loading reservation requests...</td>
              </tr>
              <tr v-else-if="reservations.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No reservation requests found.</td>
              </tr>
              <tr v-for="reservation in reservations" :key="reservation.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
                <td class="px-4 py-3 text-slate-800 dark:text-slate-100">{{ reservation.name }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ reservation.facility?.title || '-' }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatDateTime(reservation.day_range?.start_at) }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="statusClass(reservation.status)">
                    {{ reservation.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="paymentStatusClass(reservation.payment_status)">
                    {{ formatPaymentStatus(reservation.payment_status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="inline-flex gap-1">
                    <button
                      class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-cyan-500/10 hover:text-cyan-700 dark:text-slate-300"
                      title="View"
                      @click="openDetails(reservation)"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <button
                      class="inline-grid h-8 w-8 place-items-center rounded-lg text-emerald-600 transition hover:bg-emerald-500/10 disabled:cursor-not-allowed disabled:opacity-50"
                      title="Approve"
                      :disabled="reservation.status !== 'draft' || actionLoadingId === reservation.id"
                      @click="updateStatus(reservation, 'reserved')"
                    >
                      <i class="fas fa-check"></i>
                    </button>
                    <button
                      class="inline-grid h-8 w-8 place-items-center rounded-lg text-red-600 transition hover:bg-red-500/10 disabled:cursor-not-allowed disabled:opacity-50"
                      title="Reject"
                      :disabled="reservation.status !== 'draft' || actionLoadingId === reservation.id"
                      @click="updateStatus(reservation, 'rejected')"
                    >
                      <i class="fas fa-xmark"></i>
                    </button>
                    <button
                      class="inline-grid h-8 w-8 place-items-center rounded-lg text-cyan-600 transition hover:bg-cyan-500/10 disabled:cursor-not-allowed disabled:opacity-50"
                      title="Add Payment"
                      :disabled="!isApprovedStatus(reservation.status)"
                      @click="openPaymentModal(reservation)"
                    >
                      <i class="fas fa-money-bill-wave"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="props.mode === 'approved'" class="mt-4 rounded-2xl border border-slate-200/70 bg-gradient-to-br from-cyan-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Payments</p>
          <h3 class="text-2xl font-bold text-slate-900 dark:text-white">Approved Reservations</h3>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage payments for approved reservations and track paid/partial balances.</p>
        </div>
        <button
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="fetchApprovedReservations"
        >
          Refresh
        </button>
      </div>

      <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900/80">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Facility</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Total</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Paid</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Balance</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Payment Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
              <tr v-if="approvedLoading">
                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">Loading approved reservations...</td>
              </tr>
              <tr v-else-if="approvedReservations.length === 0">
                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No approved reservations found.</td>
              </tr>
              <tr v-for="reservation in approvedReservations" :key="`approved_${reservation.id}`" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
                <td class="px-4 py-3 text-slate-800 dark:text-slate-100">{{ reservation.name }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ reservation.facility?.title || '-' }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatCurrency(normalizedTotal(reservation)) }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatCurrency(reservation.paid_amount) }}</td>
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ formatCurrency(remainingBalance(reservation)) }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="paymentStatusClass(reservation.payment_status)">
                    {{ formatPaymentStatus(reservation.payment_status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    class="rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="String(reservation.payment_status || '').toLowerCase() === 'paid'"
                    @click="openPaymentModal(reservation)"
                  >
                    Add Payment
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showDetails && selectedReservation" class="fixed inset-0 z-[1600] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reservation Request #{{ selectedReservation.id }}</h3>
            <button class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="showDetails = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <dl class="mt-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
            <div>
              <dt class="font-semibold text-slate-500">Name</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedReservation.name }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Phone</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedReservation.phone }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Email</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedReservation.email || '-' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Facility</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ selectedReservation.facility?.title || '-' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Price Plan</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ prettifyRangeType(selectedReservation.price_plan?.range_type) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Start Date Time</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatDateTime(selectedReservation.day_range?.start_at) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">End Date Time</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatDateTime(selectedReservation.day_range?.end_at) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Deposit Amount</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatCurrency(selectedReservation.deposit_amount) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Reservation Amount</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatCurrency(normalizedTotal(selectedReservation)) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Status</dt>
              <dd class="mt-0.5 capitalize text-slate-800 dark:text-slate-100">{{ selectedReservation.status }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Payment Status</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatPaymentStatus(selectedReservation.payment_status) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Requested At</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatDateTime(selectedReservation.created_at) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Paid Amount</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatCurrency(selectedReservation.paid_amount) }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-500">Remaining</dt>
              <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ formatCurrency(remainingBalance(selectedReservation)) }}</dd>
            </div>
          </dl>

          <div class="mt-4">
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Payment History</h4>
            <div v-if="!selectedReservation.payments || selectedReservation.payments.length === 0" class="mt-2 rounded-lg border border-dashed border-slate-300 px-3 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
              No payments recorded.
            </div>
            <div v-else class="mt-2 space-y-2">
              <div v-for="payment in selectedReservation.payments" :key="payment.id" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-950/40">
                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ formatCurrency(payment.amount) }} · {{ payment.payment_method }}</p>
                <p class="text-slate-500 dark:text-slate-400">{{ formatDateTime(payment.payment_date) }}<span v-if="payment.reference_no"> · Ref: {{ payment.reference_no }}</span></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showPaymentModal && paymentReservation" class="fixed inset-0 z-[1700] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Add Payment · #{{ paymentReservation.id }}</h3>
            <button class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="closePaymentModal">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 space-y-3" @submit.prevent="submitPayment">
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Payment Date</label>
              <input v-model="paymentForm.payment_date" type="date" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Payment Method</label>
              <select v-model="paymentForm.payment_method" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="online">Online</option>
              </select>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Amount</label>
              <input v-model.number="paymentForm.amount" type="number" step="0.01" min="0.01" :max="remainingBalance(paymentReservation)" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Reference No (Optional)</label>
              <input v-model.trim="paymentForm.reference_no" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Notes (Optional)</label>
              <textarea v-model.trim="paymentForm.notes" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-cyan-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"></textarea>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-300">
              Total: {{ formatCurrency(normalizedTotal(paymentReservation)) }} · Paid: {{ formatCurrency(paymentReservation.paid_amount) }} · Remaining: {{ formatCurrency(remainingBalance(paymentReservation)) }}
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" @click="closePaymentModal">Cancel</button>
              <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="paymentSubmitting">
                {{ paymentSubmitting ? 'Saving...' : 'Save Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  mode: {
    type: String,
    default: 'all',
  },
})

const toast = useToast()
const loading = ref(false)
const reservations = ref([])
const approvedReservations = ref([])
const statusFilter = ref('')
const approvedLoading = ref(false)
const showDetails = ref(false)
const selectedReservation = ref(null)
const actionLoadingId = ref(null)
const showPaymentModal = ref(false)
const paymentReservation = ref(null)
const paymentSubmitting = ref(false)
const paymentForm = ref({
  payment_date: '',
  payment_method: 'cash',
  amount: null,
  reference_no: '',
  notes: '',
})

const fetchReservations = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/reservations', {
      params: {
        status: statusFilter.value,
      },
    })
    reservations.value = Array.isArray(res.data.reservations) ? res.data.reservations : []
  } catch {
    toast.error('Failed to load reservation requests')
  } finally {
    loading.value = false
  }
}

const fetchApprovedReservations = async () => {
  try {
    approvedLoading.value = true
    const res = await axios.get('/api/reservations/approved')
    approvedReservations.value = Array.isArray(res.data.reservations) ? res.data.reservations : []
  } catch {
    toast.error('Failed to load approved reservations')
  } finally {
    approvedLoading.value = false
  }
}

const statusClass = (status) => {
  const map = {
    draft: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    reserved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    active: 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
  }
  return map[String(status || '').toLowerCase()] || 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const openDetails = (reservation) => {
  selectedReservation.value = reservation
  showDetails.value = true
}

const updateStatus = async (reservation, status) => {
  try {
    actionLoadingId.value = reservation.id
    const res = await axios.patch(`/api/reservations/${reservation.id}/status`, { status })
    const updated = res.data.reservation
    reservations.value = reservations.value.map((item) => (item.id === updated.id ? updated : item))
    if (selectedReservation.value?.id === updated.id) {
      selectedReservation.value = updated
    }
    await fetchApprovedReservations()
    toast.success(status === 'reserved' ? 'Reservation approved' : 'Reservation rejected')
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to update reservation status')
    }
  } finally {
    actionLoadingId.value = null
  }
}

const isApprovedStatus = (status) => {
  return ['reserved', 'active'].includes(String(status || '').toLowerCase())
}

const formatPaymentStatus = (status) => {
  const value = String(status || 'pending').toLowerCase()
  if (value === 'partially_paid') return 'Partially Paid'
  if (value === 'paid') return 'Paid'
  return 'Pending'
}

const paymentStatusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'paid') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'partially_paid') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const normalizedTotal = (reservation) => {
  return Math.abs(Number(reservation?.reservation_amount || 0))
}

const remainingBalance = (reservation) => {
  const total = normalizedTotal(reservation)
  const paid = Number(reservation?.paid_amount || 0)
  return Math.max(0, total - paid)
}

const openPaymentModal = (reservation) => {
  paymentReservation.value = reservation
  paymentForm.value = {
    payment_date: new Date().toISOString().slice(0, 10),
    payment_method: 'cash',
    amount: null,
    reference_no: '',
    notes: '',
  }
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  paymentReservation.value = null
}

const submitPayment = async () => {
  if (!paymentReservation.value?.id) return
  const remaining = remainingBalance(paymentReservation.value)
  const amount = Number(paymentForm.value.amount || 0)

  if (remaining <= 0) {
    toast.error('This reservation is already fully paid.')
    return
  }

  if (amount <= 0) {
    toast.error('Please enter a valid payment amount.')
    return
  }

  if (amount > remaining) {
    toast.error(`Payment amount cannot exceed remaining balance (${formatCurrency(remaining)}).`)
    return
  }

  try {
    paymentSubmitting.value = true
    const payload = {
      payment_date: paymentForm.value.payment_date,
      payment_method: paymentForm.value.payment_method,
      amount: paymentForm.value.amount,
      reference_no: paymentForm.value.reference_no || null,
      notes: paymentForm.value.notes || null,
    }
    const res = await axios.post(`/api/reservations/${paymentReservation.value.id}/payments`, payload)
    const updated = res.data.reservation

    reservations.value = reservations.value.map((item) => (item.id === updated.id ? updated : item))
    approvedReservations.value = approvedReservations.value.map((item) => (item.id === updated.id ? updated : item))
    if (selectedReservation.value?.id === updated.id) {
      selectedReservation.value = updated
    }

    toast.success('Payment added successfully')
    closePaymentModal()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
      if (!err.response.data.errors) {
        toast.error(err.response?.data?.message || 'Unable to save payment')
      }
    } else {
      toast.error(err.response?.data?.message || 'Unable to save payment')
    }
  } finally {
    paymentSubmitting.value = false
  }
}

const prettifyRangeType = (type) => {
  if (type === 'per_day') return 'Per Day'
  if (type === 'per_hour') return 'Per Hour'
  return '-'
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))
}

const formatDateTime = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

onMounted(() => {
  if (props.mode !== 'approved') {
    fetchReservations()
    return
  }
  fetchApprovedReservations()
})
</script>
