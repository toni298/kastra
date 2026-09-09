<script setup>
import { BookOpenText, CircleCheck, Landmark, Pencil, ShieldCheck, Trash2 } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

defineProps({ account: { type: Object, default: null } })
const emit = defineEmits(['edit', 'delete'])
</script>

<template>
  <aside
    class="min-h-[520px] border-t border-slate-200 p-6 dark:border-[#29476b] lg:border-l lg:border-t-0"
  >
    <div v-if="!account" class="grid min-h-[460px] place-items-center text-center">
      <div class="max-w-xs">
        <span
          class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10"
          ><BookOpenText :size="25"
        /></span>
        <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Pilih akun</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">
          Klik akun pada struktur di kiri untuk melihat detail dan penggunaannya.
        </p>
      </div>
    </div>
    <template v-else>
      <div class="flex items-start justify-between gap-4">
        <span
          class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10"
          ><Landmark :size="22"
        /></span>
        <div class="flex gap-2">
          <Button
            v-if="!account.system"
            variant="ghost"
            size="sm"
            aria-label="Hapus akun"
            @click="emit('delete', account)"
            ><Trash2 :size="16" class="text-red-600" /></Button
          ><Button variant="secondary" size="sm" @click="emit('edit', account)"
            ><Pencil :size="15" class="mr-2" />Edit</Button
          >
        </div>
      </div>
      <div class="mt-5">
        <div class="flex flex-wrap items-center gap-2">
          <Badge :variant="account.status === 'Aktif' ? 'success' : 'error'">{{
            account.status
          }}</Badge
          ><Badge v-if="account.system" variant="info">Akun Sistem</Badge>
        </div>
        <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">
          {{ account.name }}
        </h3>
        <p class="mt-1 font-mono text-sm font-medium text-emerald-700 dark:text-emerald-300">
          {{ account.code }}
        </p>
      </div>
      <dl class="mt-7 space-y-3">
        <div
          v-for="item in [
            ['Parent', account.parent],
            ['Tipe Akun', account.type],
            ['Saldo Normal', account.normal],
            ['Digunakan Oleh', account.used],
          ]"
          :key="item[0]"
          class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]/60"
        >
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ item[0] }}</dt>
          <dd class="mt-1.5 text-sm font-medium text-slate-800 dark:text-white">{{ item[1] }}</dd>
        </div>
      </dl>
      <div
        class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 dark:border-emerald-500/20 dark:bg-emerald-400/5"
      >
        <div class="flex gap-3">
          <ShieldCheck class="mt-0.5 shrink-0 text-emerald-600" :size="18" />
          <div>
            <p class="text-sm font-medium text-slate-800 dark:text-white">
              Terhubung ke pembukuan otomatis
            </p>
            <p class="mt-1 text-xs leading-5 text-slate-500">
              Perubahan akun memengaruhi pemetaan transaksi terkait.
            </p>
          </div>
        </div>
      </div>
      <p class="mt-5 flex items-center gap-2 text-xs text-slate-400">
        <CircleCheck :size="14" />Terakhir diperbarui 21/07/2026
      </p>
    </template>
  </aside>
</template>
