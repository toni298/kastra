<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import { Delete, LoaderCircle, LogIn, ShieldCheck } from 'lucide-vue-next'
import { useToastify } from '@/Composables/useToastify'

const pin = ref('')
const input = ref(null)
const result = ref(null)
const loading = ref(false)
const toast = useToastify()
const props = defineProps({
  company: { type: Object, default: () => ({ name: 'Kastra', logo_url: null }) },
})
let resetTimer
const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'clear', '0', 'backspace']
const add = (value) => {
  if (loading.value || pin.value.length >= 6) return
  pin.value += value
  if (pin.value.length === 6) submit()
}
const clear = () => {
  pin.value = ''
}
const submit = async () => {
  if (pin.value.length !== 6 || loading.value) return
  loading.value = true
  try {
    const response = await axios.post(route('api.kiosk.clock'), { pin: pin.value })
    result.value = response.data.data
    clear()
    clearTimeout(resetTimer)
    resetTimer = setTimeout(() => {
      result.value = null
    }, 3000)
  } catch (error) {
    toast.error(error.response?.data?.message || 'PIN tidak ditemukan.')
    clear()
  } finally {
    loading.value = false
    await nextTick()
    input.value?.focus()
  }
}
const keydown = (event) => {
  if (/^\d$/.test(event.key)) add(event.key)
  else if (event.key === 'Backspace') pin.value = pin.value.slice(0, -1)
  else if (event.key === 'Enter') submit()
}
onMounted(() => {
  input.value?.focus()
  window.addEventListener('keydown', keydown)
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', keydown)
  clearTimeout(resetTimer)
})
</script>

<template>
  <Head :title="`${props.company.name} - Kiosk Absensi`" />
  <main class="min-h-screen bg-[#071426] px-5 py-8 text-white sm:px-10">
    <div
      class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-5xl flex-col justify-center gap-8 lg:flex-row lg:items-center"
    >
      <section class="flex-1">
        <div class="mb-8 flex items-center gap-3">
          <div
            class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-emerald-400 text-[#071426]"
          >
            <img
              v-if="props.company.logo_url"
              :src="props.company.logo_url"
              :alt="`Logo ${props.company.name}`"
              class="h-full w-full object-contain p-1"
            /><ShieldCheck v-else :size="26" />
          </div>
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">
              {{ props.company.name }}
            </p>
            <h1 class="text-3xl font-semibold">Kiosk Absensi</h1>
          </div>
        </div>
        <p class="max-w-md text-lg text-slate-300">
          Masukkan PIN 6 digit untuk mencatat jam masuk atau jam pulang.
        </p>
        <div class="mt-8 flex gap-3">
          <span
            v-for="index in 6"
            :key="index"
            class="h-3 w-3 rounded-full"
            :class="index <= pin.length ? 'bg-emerald-300' : 'bg-slate-700'"
          ></span>
        </div>
        <input
          ref="input"
          v-model="pin"
          inputmode="numeric"
          maxlength="6"
          class="sr-only"
          aria-label="PIN absensi"
          @keyup.enter="submit"
        />
      </section>
      <section
        class="w-full max-w-md rounded-[2rem] border border-slate-700 bg-[#102542] p-5 shadow-2xl"
      >
        <div
          v-if="loading"
          class="mb-5 flex min-h-[126px] flex-col items-center justify-center rounded-2xl bg-[#183557] text-center"
          role="status"
          aria-live="polite"
        >
          <LoaderCircle :size="28" class="animate-spin text-emerald-300" />
          <p class="mt-3 font-medium">Memproses absensi...</p>
          <p class="mt-1 text-xs text-slate-400">Mohon tunggu sebentar</p>
        </div>
        <div v-else-if="result" class="mb-5 rounded-2xl bg-emerald-400 p-5 text-[#071426]">
          <div class="flex items-center gap-2 font-semibold">
            <LogIn :size="18" />{{
              result.action === 'clock_in' ? 'Clock in berhasil' : 'Clock out berhasil'
            }}
          </div>
          <p class="mt-2 text-2xl font-bold">{{ result.employee_name }}</p>
          <p class="mt-1">{{ result.time }} · {{ result.status }}</p>
        </div>
        <div v-else class="mb-5 rounded-2xl bg-[#183557] p-5 text-center">
          <p class="text-sm text-slate-300">PIN karyawan</p>
          <p class="mt-2 text-3xl font-semibold tracking-[0.5em]">
            {{ pin ? '•'.repeat(pin.length) : '------' }}
          </p>
        </div>
        <div class="grid grid-cols-3 gap-3">
          <button
            v-for="key in keys"
            :key="key"
            type="button"
            class="grid h-16 place-items-center rounded-2xl bg-[#183557] text-xl font-semibold transition hover:bg-emerald-400 hover:text-[#071426] disabled:opacity-50"
            :disabled="loading"
            @click="
              key === 'clear' ? clear() : key === 'backspace' ? (pin = pin.slice(0, -1)) : add(key)
            "
          >
            <Delete v-if="key === 'backspace'" :size="22" /><span
              v-else-if="key === 'clear'"
              class="text-sm"
              >Hapus</span
            ><span v-else>{{ key }}</span>
          </button>
        </div>
      </section>
    </div>
  </main>
</template>
