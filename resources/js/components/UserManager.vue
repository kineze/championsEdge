<template>
  <section class="p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-sky-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Administration</p>
          <h2 class="text-3xl font-bold text-slate-900 dark:text-white">System Users</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage team accounts, roles, and secure access.</p>
        </div>

        <div class="rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20">
          <p class="text-[11px] uppercase tracking-[0.14em] text-sky-100">Total Users</p>
          <p class="text-2xl font-bold">{{ users.length }}</p>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap items-center gap-3">
        <select
          id="roleFilter"
          v-model="selectedRole"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
          @change="fetchUsers"
        >
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
        </select>

        <div class="relative min-w-[220px] flex-1">
          <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
          <input
            v-model="search"
            type="search"
            class="w-full rounded-xl border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
            placeholder="Search by name or email"
            @input="debounceSearch"
          />
        </div>

        <button
          @click="openDrawer"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition hover:brightness-110"
        >
          <i class="fas fa-plus" aria-hidden="true"></i>
          <span>New User</span>
        </button>
      </div>

      <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200 bg-white/90 dark:border-slate-700 dark:bg-slate-950/80">
        <table class="w-full min-w-[680px] text-left text-sm">
          <thead>
            <tr class="border-b border-slate-200 dark:border-slate-700">
              <th class="px-4 py-3 text-[11px] uppercase tracking-[0.15em] text-slate-500">Name</th>
              <th class="px-4 py-3 text-[11px] uppercase tracking-[0.15em] text-slate-500">Email</th>
              <th class="px-4 py-3 text-[11px] uppercase tracking-[0.15em] text-slate-500">Role</th>
              <th class="px-4 py-3 text-right text-[11px] uppercase tracking-[0.15em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="users.length === 0">
              <td colspan="4" class="px-4 py-6 text-center italic text-slate-500">No users found</td>
            </tr>

            <tr v-for="user in users" :key="user.id" class="border-b border-slate-200/80 last:border-b-0 dark:border-slate-700/70">
              <td class="px-4 py-3 text-slate-800 dark:text-slate-200">
                <div class="inline-flex items-center gap-2.5">
                  <span class="inline-grid h-7 w-7 place-items-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 text-[11px] font-bold text-white">
                    {{ initials(user.name) }}
                  </span>
                  <span class="font-semibold">{{ user.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span
                  v-if="user.roles.length"
                  class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="roleBadgeClass(user.roles[0].name)"
                >
                  {{ user.roles[0].name }}
                </span>
                <span v-else class="italic text-slate-400">No Role</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button @click="editUser(user)" class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-sky-500/10 hover:text-sky-700 dark:text-slate-300" title="Edit user">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button @click="openPasswordModal(user.id)" class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-amber-500/15 hover:text-amber-700 dark:text-slate-300" title="Reset password">
                    <i class="fas fa-key"></i>
                  </button>
                  <button @click="openDeleteModal(user.id)" class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-red-500/15 hover:text-red-700 dark:text-slate-300" title="Delete user">
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
      <div v-if="showDrawer" class="fixed inset-0 z-[1300] bg-black/50">
        <div class="absolute right-0 top-0 h-full w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-950">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingId ? 'Update User' : 'Create User' }}</h3>
            <button @click="closeDrawer" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" aria-label="Close drawer">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="saveUser" class="mt-4 grid gap-4">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400" for="name">Name</label>
              <input id="name" v-model="form.name" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400" for="email">Email</label>
              <input id="email" v-model="form.email" type="email" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400" for="password">Password</label>
              <input id="password" v-model="form.password" type="password" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" placeholder="Optional" />
              <p class="mt-1 text-xs text-slate-500">Leave blank to auto-generate and email credentials.</p>
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400" for="role">Role</label>
              <select id="role" v-model="form.role" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-sky-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                <option value="" disabled>Select role</option>
                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
              </select>
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition hover:brightness-110">
              {{ editingId ? 'Save Changes' : 'Create User' }}
            </button>
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
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete User</h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">This action cannot be undone. Delete this user account?</p>
          <div class="mt-5 flex justify-end gap-2">
            <button @click="showDeleteModal = false" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            <button @click="deleteUser" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Delete</button>
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
      <div v-if="showPasswordModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reset Password</h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">A new password will be generated and emailed to the user.</p>
          <div class="mt-5 flex justify-end gap-2">
            <button @click="showPasswordModal = false" class="rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            <button @click="confirmResetPassword" class="rounded-lg bg-amber-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">Reset</button>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const users = ref([])
const roles = ref([])
const search = ref('')
const selectedRole = ref('')

const showDrawer = ref(false)
const form = ref({ name: '', email: '', password: '', role: '' })
const editingId = ref(null)

const showDeleteModal = ref(false)
const deletingId = ref(null)

const showPasswordModal = ref(false)
const passwordResetUserId = ref(null)

let searchTimeout = null

const initials = (name) => {
  return String(name || '')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() || '')
    .join('')
}

const roleBadgeClass = (roleName) => {
  const name = String(roleName || '').toLowerCase()
  if (name === 'admin') return 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300'
  if (name === 'vendor') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
}

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/users', {
      params: { search: search.value, role: selectedRole.value }
    })
    users.value = res.data.users
    roles.value = res.data.roles
  } catch {
    toast.error('Failed to load users')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchUsers, 350)
}

const openDeleteModal = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const deleteUser = async () => {
  try {
    await axios.delete(`/api/users/${deletingId.value}`)
    toast.success('User deleted successfully')
    showDeleteModal.value = false
    fetchUsers()
  } catch {
    toast.error('Failed to delete user')
  }
}

const openPasswordModal = (id) => {
  passwordResetUserId.value = id
  showPasswordModal.value = true
}

const confirmResetPassword = async () => {
  try {
    await axios.post(`/api/users/${passwordResetUserId.value}/reset-password`)
    toast.success('Password reset email sent')
    showPasswordModal.value = false
  } catch {
    toast.error('Failed to reset password')
  }
}

const saveUser = async () => {
  try {
    if (editingId.value) {
      await axios.put(`/api/users/${editingId.value}`, form.value)
      toast.success('User updated successfully')
    } else {
      await axios.post('/api/users', form.value)
      toast.success('User created successfully')
    }

    closeDrawer()
    fetchUsers()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Error saving user')
    }
  }
}

const openDrawer = () => {
  form.value = { name: '', email: '', password: '', role: '' }
  editingId.value = null
  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
}

const editUser = (user) => {
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.roles[0]?.name || ''
  }
  editingId.value = user.id
  showDrawer.value = true
}

onMounted(() => {
  fetchUsers()
})
</script>
