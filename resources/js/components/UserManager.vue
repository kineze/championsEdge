<template>
  <div class="p-3 w-full">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-bold dark:text-white">User Role Manager</h2>

      <form class="w-9/12" @submit.prevent>
        <div class="flex justify-end w-full">
          <!-- Role Dropdown -->
          <div class="relative">
            <button
              id="dropdown-button"
              type="button"
              @click="showRoleDropdown = !showRoleDropdown"
              class="shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center
                     text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200
                     focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700
                     dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600"
            >
              {{ selectedRoleLabel }}
              <svg class="w-2.5 h-2.5 ms-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m1 1 4 4 4-4" />
              </svg>
            </button>

            <!-- Dropdown List -->
            <div
              v-if="showRoleDropdown"
              class="absolute z-20 mt-1 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700"
            >
              <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                <li>
                  <button @click="selectRole('')" type="button"
                          class="inline-flex w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                    All Roles
                  </button>
                </li>
                <li v-for="role in roles" :key="role.id">
                  <button @click="selectRole(role.name)" type="button"
                          class="inline-flex w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                    {{ role.name }}
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <!-- Search Input -->
          <div class="relative w-96">
            <input
              v-model="search"
              @input="debounceSearch"
              type="search"
              id="search-users"
              placeholder="Search users by name or email..."
              class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-e-lg border-s-gray-50
                     border-s-2 border border-gray-300 focus:ring-teal-500 focus:border-teal-500
                     dark:bg-gray-700 dark:border-s-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                     dark:text-white dark:focus:border-teal-500"
            />
            <div class="absolute top-0 end-0 p-2.5 text-sm h-full text-teal-600 rounded-e-lg">
              <i class="fas fa-search"></i>
            </div>
          </div>

          <button
            @click="openDrawer"
            class="px-4 py-2 text-xs ml-3 font-bold uppercase text-white bg-teal-600 rounded-lg hover:bg-teal-700"
          >
            <i class="fas fa-plus mr-2"></i> New User
          </button>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="relative bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
        <thead class="text-xs text-gray-600 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th class="px-3 py-3">Name</th>
            <th class="px-3 py-3">Email</th>
            <th class="px-3 py-3">Role</th>
            <th class="px-3 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="users.length === 0">
            <td colspan="4" class="text-center py-4 text-gray-500">No users found</td>
          </tr>

          <tr v-for="user in users" :key="user.id" class="border-b dark:border-gray-700">
            <td class="px-3 py-3">{{ user.name }}</td>
            <td class="px-3 py-3">{{ user.email }}</td>
            <td class="px-3 py-3">
              <span
                v-if="user.roles.length"
                :class="[
                  'text-xs font-medium px-2.5 py-0.5 rounded capitalize',
                  user.roles[0].name === 'Admin'
                    ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                    : user.roles[0].name === 'Vendor'
                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                    : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                ]"
              >
                {{ user.roles[0].name }}
              </span>
              <span v-else class="text-gray-400 italic">No Role</span>
            </td>
            <td class="px-3 py-3 text-right space-x-3">
             <!-- <button @click="openBlockModal(user)" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-ban"></i>
              </button> -->
              <button @click="editUser(user)" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-edit"></i>
              </button>
            <button @click="openPasswordModal(user.id)" class="text-yellow-600 hover:text-yellow-800">
              <i class="fas fa-key"></i>
            </button>
              <button @click="openDeleteModal(user.id)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash-alt"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Drawer: Create/Edit User -->
    <transition name="fade">
      <div v-if="showDrawer" class="fixed inset-0 bg-black bg-opacity-50 flex justify-end z-[1300]">
        <div class="bg-white dark:bg-gray-900 w-full max-w-md p-6 shadow-xl overflow-y-auto">
          <h3 class="text-lg font-semibold dark:text-white mb-4">
            {{ editingId ? 'Edit User' : 'Create New User' }}
          </h3>
          <button @click="closeDrawer" class="absolute top-4 right-4 text-gray-500 dark:text-gray-300">✖</button>

            <form @submit.prevent="saveUser" class="space-y-5">
                <div class="relative z-0 w-full group">
                    <input type="text" v-model="form.name" required class="floating-input peer" placeholder=" " />
                    <label class="floating-label">Name</label>
                </div>

                <div class="relative z-0 w-full group">
                    <input type="email" v-model="form.email" required class="floating-input peer" placeholder=" " />
                    <label class="floating-label">Email</label>
                </div>

                <!-- Password Input + Info -->
                <div class="relative z-0 w-full group">
                    <input type="password" v-model="form.password" class="floating-input peer" placeholder=" " />
                    <label class="floating-label">Password</label>
                </div>
                <div
                    class="p-3 bg-teal-50 dark:bg-gray-800 border border-teal-200 dark:border-gray-700 rounded-lg text-sm text-teal-700 dark:text-teal-300"
                >
                    <i class="fas fa-info-circle mr-2"></i>
                    Leave the password field blank to auto-generate a secure password. It will be emailed to the user automatically.
                </div>

                <!-- Role Selection (custom radio list) -->
                <div>
                    <h4 class="text-sm font-semibold mb-2 dark:text-gray-300">Assign Role</h4>
                    <ul class="grid w-full gap-4">
                    <li v-for="role in roles" :key="role.id">
                        <input
                        type="radio"
                        :id="'role-' + role.id"
                        name="role"
                        :value="role.name"
                        v-model="form.role"
                        class="hidden peer"
                        />
                        <label
                        :for="'role-' + role.id"
                        class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer
                                dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-cyan-500
                                peer-checked:border-cyan-600 dark:peer-checked:border-cyan-600 peer-checked:text-cyan-600
                                hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 transition"
                        >
                        <div>
                            <div class="text-lg font-semibold capitalize">{{ role.name }}</div>
                            <div class="text-sm">System Role</div>
                        </div>
                        <i class="fa-solid fa-circle mt-0.5"></i>
                        </label>
                    </li>
                    </ul>
                </div>

                <button
                    type="submit"
                    class="w-full py-2 bg-teal-600 text-sm text-white rounded-lg hover:bg-teal-700"
                >
                    {{ editingId ? 'Update User' : 'Save User' }}
                </button>
            </form>

        </div>
      </div>
    </transition>
  </div>

  <!-- Delete Confirmation Modal -->
  <transition name="fade">
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-[1500] bg-black bg-opacity-50 flex items-center justify-center"
    >
      <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-full max-w-md shadow-xl">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">
          Confirm Delete
        </h3>
        <p class="text-gray-700 dark:text-gray-300 mb-6">
          Are you sure you want to delete this user? This action cannot be undone.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-400"
          >
            Cancel
          </button>

          <button
            @click="deleteUser"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Reset Password Modal -->
  <transition name="fade">
    <div
      v-if="showPasswordModal"
      class="fixed inset-0 z-[1500] bg-black bg-opacity-50 flex items-center justify-center"
    >
      <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-full max-w-md shadow-xl">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">
          Reset Password
        </h3>
        <p class="text-gray-700 dark:text-gray-300 mb-6">
          Are you sure you want to reset this user's password?  
          A new password will be generated and emailed to the user.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showPasswordModal = false"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg"
          >Cancel</button>

         <button
            @click="confirmResetPassword"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
          >Reset</button>

        </div>
      </div>
    </div>
  </transition>


  <!-- Block Confirmation Modal -->
  <transition name="fade">
    <div
      v-if="showBlockModal"
      class="fixed inset-0 z-[1500] bg-black bg-opacity-50 flex items-center justify-center"
    >
      <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-full max-w-md shadow-xl">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">
          {{ blockAction === 'block' ? 'Block User' : 'Unblock User' }}
        </h3>

        <p class="text-gray-700 dark:text-gray-300 mb-6">
          Are you sure you want to {{ blockAction }} this user?
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showBlockModal = false"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-400"
          >
            Cancel
          </button>

          <button
            @click="confirmBlockUser"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </transition>


</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue"
import axios from "axios"
import { useToast } from "vue-toastification"

const toast = useToast()

// STATE
const users = ref([])
const roles = ref([])
const search = ref("")
const selectedRole = ref("")
const showRoleDropdown = ref(false)
const selectedRoleLabel = computed(() => selectedRole.value || "All Roles")

// Create/Edit drawer
const showDrawer = ref(false)
const form = ref({ name: "", email: "", password: "", role: "" })
const editingId = ref(null)

// Delete modal
const showDeleteModal = ref(false)
const deletingId = ref(null)

// Reset Password modal
const showPasswordModal = ref(false)
const passwordResetUserId = ref(null)

// Block modal
const showBlockModal = ref(false)
const blockUserId = ref(null)
const blockAction = ref("block")

let searchTimeout = null

// Load users + roles
const fetchUsers = async () => {
  try {
    const res = await axios.get("/api/users", {
      params: { search: search.value, role: selectedRole.value }
    })
    users.value = res.data.users
    roles.value = res.data.roles
  } catch (error) {
    toast.error("Failed to load users")
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchUsers, 400)
}

// Role filter
const selectRole = (roleName) => {
  selectedRole.value = roleName
  showRoleDropdown.value = false
  fetchUsers()
}

// ---------------------------
// DELETE LOGIC
// ---------------------------
const openDeleteModal = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const deleteUser = async () => {
  try {
    await axios.delete(`/api/users/${deletingId.value}`)
    toast.success("User deleted successfully!")
    showDeleteModal.value = false
    fetchUsers()
  } catch {
    toast.error("Failed to delete user.")
  }
}

// ---------------------------
// RESET PASSWORD LOGIC
// ---------------------------
const openPasswordModal = (id) => {
  passwordResetUserId.value = id
  showPasswordModal.value = true
}

const confirmResetPassword = async () => {
  try {
    await axios.post(`/api/users/${passwordResetUserId.value}/reset-password`)
    toast.success("Password reset — Email sent via Brevo!")
    showPasswordModal.value = false
  } catch {
    toast.error("Failed to reset password.")
  }
}

// ---------------------------
// BLOCK / UNBLOCK LOGIC
// ---------------------------
const openBlockModal = (user) => {
  blockUserId.value = user.id
  blockAction.value = user.is_blocked ? "unblock" : "block"
  showBlockModal.value = true
}

const confirmBlock = async () => {
  try {
    await axios.post(`/api/users/${blockUserId.value}/block`, {
      action: blockAction.value
    })
    toast.success(`User ${blockAction.value}ed successfully!`)
    showBlockModal.value = false
    fetchUsers()
  } catch {
    toast.error("Failed to update user status.")
  }
}

// ---------------------------
// CREATE / UPDATE USER
// ---------------------------
const saveUser = async () => {
  try {
    if (editingId.value) {
      // Update user
      await axios.put(`/api/users/${editingId.value}`, form.value)
      toast.success("User updated successfully!")
    } else {
      // Create user (Brevo will send password)
      await axios.post("/api/users", form.value)
      toast.success("User created! Login details emailed.")
    }

    closeDrawer()
    fetchUsers()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error("Error saving user.")
    }
  }
}

const openDrawer = () => {
  form.value = { name: "", email: "", password: "", role: "" }
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
    role: user.roles[0]?.name || ""
  }
  editingId.value = user.id
  showDrawer.value = true
}

// Close dropdown on outside click
const handleClickOutside = (e) => {
  const dropdown = document.getElementById("dropdown-button")
  if (dropdown && !dropdown.contains(e.target)) {
    showRoleDropdown.value = false
  }
}

onMounted(() => {
  fetchUsers()
  document.addEventListener("click", handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside)
})
</script>


<style scoped>
.floating-input {
  @apply block py-2.5 px-0 w-full text-sm bg-transparent border-0 border-b-2 border-gray-300 focus:outline-none focus:ring-0 focus:border-gray-600 dark:text-white dark:border-gray-600;
}
.floating-label {
  @apply absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-gray-600 peer-focus:dark:text-gray-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
