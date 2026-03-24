<script setup>
import { nextTick, ref } from 'vue'
import axios from 'axios'

const isOpen = ref(false)
const input = ref('')
const loading = ref(false)
const messages = ref([
  {
    role: 'assistant',
    content: 'Hi, I am the Champions Edge assistant. Ask me about facilities, sessions, members, or reservation stats.',
  },
])

const bodyRef = ref(null)

const toggle = async () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    await scrollToBottom()
  }
}

const scrollToBottom = async () => {
  await nextTick()
  if (bodyRef.value) {
    bodyRef.value.scrollTop = bodyRef.value.scrollHeight
  }
}

const sendMessage = async () => {
  const message = input.value.trim()
  if (!message || loading.value) return

  messages.value.push({ role: 'user', content: message })
  input.value = ''
  loading.value = true
  await scrollToBottom()

  try {
    const history = messages.value
      .slice(-10)
      .filter((item) => item.role === 'user' || item.role === 'assistant')
      .map((item) => ({
        role: item.role,
        content: item.content,
      }))

    const res = await axios.post('/api/public/assistant/chat', {
      message,
      history,
    })

    messages.value.push({
      role: 'assistant',
      content: res.data?.reply || 'No reply generated.',
    })
  } catch (error) {
    messages.value.push({
      role: 'assistant',
      content: error?.response?.data?.message || 'Assistant is currently unavailable.',
    })
  } finally {
    loading.value = false
    await scrollToBottom()
  }
}
</script>

<template>
  <div class="fixed bottom-6 right-6 z-[1100]">
    <button
      @click="toggle"
      class="flex h-14 w-14 items-center justify-center rounded-full bg-cyan-600 text-white shadow-lg transition hover:bg-cyan-700"
      aria-label="Toggle assistant"
    >
      <i class="fas fa-robot text-lg"></i>
    </button>

    <div
      v-if="isOpen"
      class="mt-3 flex h-[34rem] w-[22rem] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl sm:w-[25rem]"
    >
      <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
        <div>
          <p class="text-sm font-bold text-slate-900">Champions Edge Assistant</p>
          <p class="text-xs text-slate-500">Powered by Ollama</p>
        </div>
        <button @click="toggle" class="text-slate-500 transition hover:text-slate-700" aria-label="Close assistant">
          <i class="fas fa-xmark text-lg"></i>
        </button>
      </div>

      <div ref="bodyRef" class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4">
        <div
          v-for="(msg, idx) in messages"
          :key="idx"
          class="max-w-[85%] rounded-2xl px-3 py-2 text-sm leading-relaxed"
          :class="msg.role === 'user' ? 'ml-auto bg-cyan-600 text-white' : 'mr-auto bg-white text-slate-800 border border-slate-200'"
        >
          {{ msg.content }}
        </div>

        <div v-if="loading" class="mr-auto rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500">
          Thinking...
        </div>
      </div>

      <form @submit.prevent="sendMessage" class="border-t border-slate-200 bg-white p-3">
        <div class="flex items-center gap-2">
          <input
            v-model="input"
            type="text"
            placeholder="Ask about system data..."
            class="h-11 flex-1 rounded-xl border border-slate-300 px-3 text-sm text-slate-900 outline-none focus:border-cyan-500"
          />
          <button
            type="submit"
            :disabled="loading"
            class="h-11 rounded-xl bg-cyan-600 px-4 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
          >
            Send
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
