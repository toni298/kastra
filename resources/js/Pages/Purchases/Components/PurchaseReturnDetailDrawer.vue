<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { CalendarDays, FileText, Package, Undo2, UserRound, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import {
  activateOverlayLayer,
  deactivateOverlayLayer,
  isTopOverlayLayer,
  lockOverlayBodyScroll,
  unlockOverlayBodyScroll,
} from '@/Composables/useModalLayer'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  purchaseReturn: { type: Object, required: true },
})

const emit = defineEmits(['close', 'load-items'])
const itemsPanel = ref(null)
const items = computed(() => props.purchaseReturn.items?.data ?? [])
const nextCursor = computed(() => props.purchaseReturn.items?.next_cursor ?? null)
const loadingMore = ref(false)
const loadMore = () => { const panel = itemsPanel.value; if (loadingMore.value || !nextCursor.value || !panel || panel.scrollTop + panel.clientHeight < panel.scrollHeight - 80) return; loadingMore.value = true; emit('load-items', nextCursor.value, () => { loadingMore.value = false }) }

const drawerToken = Symbol('purchase-return-detail-drawer')

const closeDrawer = () => {
  emit('close')
}

const closeOnEscape = (event) => {
  if (event.key !== 'Escape' || !isTopOverlayLayer(drawerToken)) return

  event.preventDefault()
  event.stopImmediatePropagation()
  closeDrawer()
}

onMounted(() => {
  lockOverlayBodyScroll()
  activateOverlayLayer(drawerToken)
  document.addEventListener('keydown', closeOnEscape, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', closeOnEscape, true)
  deactivateOverlayLayer(drawerToken)
  unlockOverlayBodyScroll()
})
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-[2px]"
      aria-hidden="true"
      @click="closeDrawer"
    ></div>

    <aside
      class="fixed inset-y-0 right-0 z-50 flex w-full max-w-4xl flex-col border-l border-slate-200 bg-slate-50 shadow-2xl dark:border-[#29476b] dark:bg-[#071426]"
      role="dialog"
      aria-modal="true"
      aria-labelledby="purchase-return-detail-title"
    >
      <header
        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#102542] sm:px-6"
      >
        <div class="min-w-0">
          <p
            class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-300"
          >
            Detail Retur
          </p>
          <h2
            id="purchase-return-detail-title"
            class="mt-1 truncate font-mono text-xl font-semibold text-slate-950 dark:text-white"
          >
            {{ purchaseReturn.number }}
          </h2>
          <p class="mt-1 truncate text-sm font-medium text-slate-600 dark:text-slate-300">
            {{ purchaseReturn.supplier }}
          </p>
          <div class="mt-3">
            <Badge :variant="purchaseReturn.statusVariant">{{ purchaseReturn.status }}</Badge>
          </div>
        </div>

        <IconButton label="Tutup detail retur" @click="closeDrawer">
          <X :size="20" />
        </IconButton>
      </header>

      <div class="flex-1 overflow-y-auto p-4 sm:p-6">
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
          <main class="space-y-5">
            <section
              class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#102542]"
            >
              <header
                class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b]"
              >
                <Undo2 :size="19" class="text-emerald-600" />
                <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Retur</h3>
              </header>
              <dl class="grid gap-5 p-5 sm:grid-cols-2">
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Referensi Pembelian
                  </dt>
                  <dd class="mt-1 font-mono font-medium text-slate-900 dark:text-white">
                    {{ purchaseReturn.reference }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Tanggal Retur
                  </dt>
                  <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                    {{ purchaseReturn.date }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Alasan Retur
                  </dt>
                  <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                    {{ purchaseReturn.reason }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Penyelesaian
                  </dt>
                  <dd class="mt-1 font-medium text-slate-900 dark:text-white">
                    {{ purchaseReturn.resolution }}
                  </dd>
                </div>
                <div class="sm:col-span-2">
                  <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Catatan
                  </dt>
                  <dd class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-300">
                    {{ purchaseReturn.note || '-' }}
                  </dd>
                </div>
              </dl>
            </section>

            <section
              class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#102542]"
            >
              <header
                class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b]"
              >
                <div class="flex items-center gap-3">
                  <Package :size="19" class="text-emerald-600" />
                  <h3 class="font-semibold text-slate-900 dark:text-white">Barang Diretur</h3>
                </div>
                <span class="text-xs font-medium text-slate-500">
                   {{ purchaseReturn.details_count ?? items.length }} item
                </span>
              </header>
              <div ref="itemsPanel" class="h-80 overflow-auto" @scroll="loadMore">
                <table class="min-w-full text-left text-sm">
                  <thead
                    class="bg-slate-50 text-[12px] font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]"
                  >
                    <tr>
                      <th class="px-5 py-3">Barang</th>
                      <th class="px-5 py-3 text-right">Qty Retur</th>
                      <th class="px-5 py-3 text-right">Nilai</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
                    <tr v-for="item in items" :key="item.id">
                      <td class="px-5 py-4 font-medium text-slate-900 dark:text-white">
                        {{ item.name }}
                      </td>
                      <td class="whitespace-nowrap px-5 py-4 text-right dark:text-slate-200">
                        {{ item.quantity }} {{ item.unit }}
                      </td>
                      <td
                        class="whitespace-nowrap px-5 py-4 text-right font-medium dark:text-slate-200"
                      >
                        {{ formatCurrency(item.subtotal) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="bg-slate-50/70 dark:bg-[#0a1b33]/60">
                      <td
                        colspan="2"
                        class="px-5 py-4 font-semibold text-slate-900 dark:text-white"
                      >
                        Total Nilai Retur
                      </td>
                      <td
                        class="whitespace-nowrap px-5 py-4 text-right font-semibold text-emerald-700 dark:text-emerald-300"
                      >
                        {{ formatCurrency(purchaseReturn.totalValue) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
                <p v-if="loadingMore" class="p-3 text-center text-xs text-slate-400">Memuat barang…</p>
              </div>
            </section>
          </main>

          <aside class="space-y-5 xl:sticky xl:top-0">
            <section
              class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
            >
              <div class="flex items-center gap-3">
                <UserRound :size="19" class="text-emerald-600" />
                <h3 class="font-semibold text-slate-900 dark:text-white">Supplier</h3>
              </div>
              <p class="mt-4 font-medium text-slate-900 dark:text-white">
                {{ purchaseReturn.supplier }}
              </p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ purchaseReturn.supplierPhone }}
              </p>
            </section>

            <section
              class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
            >
              <div class="flex items-center gap-3">
                <FileText :size="19" class="text-emerald-600" />
                <h3 class="font-semibold text-slate-900 dark:text-white">Dibuat Oleh</h3>
              </div>
              <p class="mt-4 text-sm font-medium text-slate-800 dark:text-slate-200">
                {{ purchaseReturn.createdBy }}
              </p>
            </section>
          </aside>
        </div>
      </div>
    </aside>
  </Teleport>
</template>
