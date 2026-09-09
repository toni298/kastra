<script setup>
import { X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'

defineProps({
  item: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
const emit = defineEmits(['close'])
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white p-6 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="flex items-start justify-between border-b border-slate-200 pb-5 dark:border-[#29476b]"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Detail Retur</p>
          <h2 class="mt-1 text-xl font-bold dark:text-white">{{ item.return_number }}</h2>
          <p class="mt-1 text-sm text-slate-500">
            {{ item.reference_number }} · {{ item.customer }}
          </p>
        </div>
        <button
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div class="space-y-6 py-6">
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Informasi Retur
          </h3>
          <dl class="mt-3 grid grid-cols-2 gap-4">
            <div>
              <dt class="text-xs text-slate-400">Tanggal</dt>
              <dd class="mt-1 font-semibold dark:text-white">{{ item.date }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Alasan</dt>
              <dd class="mt-1 font-semibold dark:text-white">{{ item.reason_label }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Penyelesaian</dt>
              <dd class="mt-1 font-semibold dark:text-white">{{ item.resolution_label ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Status</dt>
              <dd class="mt-1">
                <Badge :variant="item.status === 'completed' ? 'success' : 'neutral'">{{
                  item.status
                }}</Badge>
              </dd>
            </div>
          </dl>
        </section>

        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Produk Diretur
          </h3>
          <div
            class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-[#29476b] dark:border-[#29476b]"
          >
            <div
              v-for="detail in item.details"
              :key="detail.product_id"
              class="flex items-center justify-between p-3"
            >
              <div>
                <p class="font-medium dark:text-white">{{ detail.product }}</p>
                <p class="mt-0.5 text-xs text-slate-500">
                  {{ detail.quantity }} {{ detail.unit ?? 'Unit' }} × {{ money(detail.unit_price) }}
                </p>
              </div>
              <p class="font-semibold dark:text-white">{{ money(detail.subtotal) }}</p>
            </div>
          </div>
        </section>

        <section v-if="item.replacements?.length">
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Produk Pengganti
          </h3>
          <div
            class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-[#29476b] dark:border-[#29476b]"
          >
            <div
              v-for="rep in item.replacements"
              :key="rep.product_id"
              class="flex items-center justify-between p-3"
            >
              <div>
                <p class="font-medium dark:text-white">{{ rep.product }}</p>
                <p class="mt-0.5 text-xs text-slate-500">
                  {{ rep.quantity }} {{ rep.unit ?? 'Unit' }} × {{ money(rep.unit_price) }}
                </p>
              </div>
              <p class="font-semibold dark:text-white">{{ money(rep.subtotal) }}</p>
            </div>
          </div>
        </section>

        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Ringkasan Penyelesaian
          </h3>
          <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-slate-500">Total Nilai Retur</dt>
              <dd class="font-bold text-emerald-600">{{ money(item.total) }}</dd>
            </div>
            <div v-if="item.replacement_total > 0" class="flex justify-between">
              <dt class="text-slate-500">Total Produk Pengganti</dt>
              <dd class="font-bold dark:text-white">{{ money(item.replacement_total) }}</dd>
            </div>
            <div v-if="item.refund_amount > 0" class="flex justify-between">
              <dt class="text-slate-500">Refund Dibayar</dt>
              <dd class="font-bold text-orange-600">{{ money(item.refund_amount) }}</dd>
            </div>
            <div v-if="item.customer_credit_amount > 0" class="flex justify-between">
              <dt class="text-slate-500">Kredit Pelanggan</dt>
              <dd class="font-bold text-blue-600">{{ money(item.customer_credit_amount) }}</dd>
            </div>
            <div v-if="item.customer_pays_amount > 0" class="flex justify-between">
              <dt class="text-slate-500">Tagihan Pelanggan</dt>
              <dd class="font-bold text-red-600">{{ money(item.customer_pays_amount) }}</dd>
            </div>
          </dl>
        </section>
      </div>
    </aside>
  </Teleport>
</template>
