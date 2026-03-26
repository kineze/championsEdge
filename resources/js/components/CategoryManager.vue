<template>
  <div class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950/80">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Category Manager</h3>
        <p class="text-xs text-slate-500">Create and manage inventory categories.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-sky-600 px-3.5 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/20 transition hover:brightness-110"
        @click="openCreateModal"
      >
        <i class="fas fa-plus"></i>
        <span>Add Category</span>
      </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/80">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Slug</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Description</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/50">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Loading categories...</td>
            </tr>
            <tr v-else-if="categories.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No categories found.</td>
            </tr>
            <tr v-for="item in categories" :key="item.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40">
              <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ item.name }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.slug }}</td>
              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ item.description || '-' }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex gap-1">
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-indigo-500/10 hover:text-indigo-700 dark:text-slate-300"
                    title="Edit category"
                    @click="openEditModal(item)"
                  >
                    <i class="fas fa-pen"></i>
                  </button>
                  <button
                    type="button"
                    class="inline-grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-red-500/15 hover:text-red-700 dark:text-slate-300"
                    title="Delete category"
                    @click="deleteCategory(item.id)"
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
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ editingId ? 'Update Category' : 'Add Category' }}
            </h4>
            <button type="button" class="inline-grid h-8 w-8 place-items-center rounded-full text-slate-500 transition hover:bg-slate-200 dark:hover:bg-slate-800" @click="closeModal">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="saveCategory">
            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Category Name</label>
              <input
                v-model.trim="form.name"
                type="text"
                required
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                @input="syncSlugFromName"
              />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Slug</label>
              <input
                v-model.trim="form.slug"
                type="text"
                required
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                @input="slugManuallyEdited = true"
              />
            </div>

            <div>
              <label class="mb-1.5 inline-block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600 dark:text-slate-400">Description</label>
              <textarea
                v-model.trim="form.description"
                rows="4"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:ring-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
              />
            </div>

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

const categories = ref([])
const loading = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const slugManuallyEdited = ref(false)

const form = ref({
  name: '',
  slug: '',
  description: '',
})

const slugify = (value) =>
  String(value || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

const resetForm = () => {
  form.value = {
    name: '',
    slug: '',
    description: '',
  }
  slugManuallyEdited.value = false
}

const syncSlugFromName = () => {
  if (!slugManuallyEdited.value) {
    form.value.slug = slugify(form.value.name)
  }
}

const fetchCategories = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/categories')
    categories.value = Array.isArray(res.data.categories) ? res.data.categories : []
  } catch {
    toast.error('Failed to load categories')
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
    name: item.name ?? '',
    slug: item.slug ?? '',
    description: item.description ?? '',
  }
  slugManuallyEdited.value = true
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveCategory = async () => {
  try {
    const payload = {
      name: form.value.name,
      slug: form.value.slug,
      description: form.value.description || null,
    }

    if (editingId.value) {
      await axios.put(`/api/categories/${editingId.value}`, payload)
      toast.success('Category updated successfully')
    } else {
      await axios.post('/api/categories', payload)
      toast.success('Category created successfully')
    }
    closeModal()
    fetchCategories()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error('Failed to save category')
    }
  }
}

const deleteCategory = async (id) => {
  if (!confirm('Delete this category?')) return
  try {
    await axios.delete(`/api/categories/${id}`)
    toast.success('Category deleted successfully')
    fetchCategories()
  } catch {
    toast.error('Failed to delete category')
  }
}

onMounted(fetchCategories)
</script>
