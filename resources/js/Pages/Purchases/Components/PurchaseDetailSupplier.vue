<script setup>
import { Building2, Mail, MapPin, Phone } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'

defineProps({
  transaction: { type: Object, required: true },
})
</script>

<template>
  <DataPanel>
    <header class="border-b border-slate-100 px-5 py-4 dark:border-[#29476b]">
      <div class="flex items-center gap-3">
        <span
          class="grid size-9 place-items-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300"
        >
          <Building2 :size="18" />
        </span>
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Informasi Supplier</h3>
          <p class="mt-0.5 text-xs text-slate-500">Kontak supplier transaksi.</p>
        </div>
      </div>
    </header>
    <div class="p-5">
      <p class="text-base font-semibold text-slate-950 dark:text-white">
        {{ transaction.supplier }}
      </p>
      <dl class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="flex items-start gap-3 text-sm">
          <Phone :size="16" class="mt-0.5 shrink-0 text-slate-400" />
          <div>
            <dt class="text-xs font-medium text-slate-400">Telepon</dt>
            <dd class="mt-1 font-medium text-slate-700 dark:text-slate-200">
              {{ transaction.supplierPhone }}
            </dd>
          </div>
        </div>
        <div class="flex min-w-0 items-start gap-3 text-sm">
          <Mail :size="16" class="mt-0.5 shrink-0 text-slate-400" />
          <div class="min-w-0">
            <dt class="text-xs font-medium text-slate-400">Email</dt>
            <dd class="mt-1 break-all font-medium text-slate-700 dark:text-slate-200">
              {{ transaction.supplierEmail }}
            </dd>
          </div>
        </div>
        <div class="flex items-start gap-3 text-sm sm:col-span-2">
          <MapPin :size="16" class="mt-0.5 shrink-0 text-slate-400" />
          <div>
            <dt class="text-xs font-medium text-slate-400">Alamat</dt>
            <dd class="mt-1 leading-6 font-medium text-slate-700 dark:text-slate-200">
              {{ transaction.supplierAddress }}
            </dd>
          </div>
        </div>
      </dl>
    </div>
  </DataPanel>
</template>
