<script setup>
import { CheckCircle2, Clock3, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  item: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
  canReceive: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'receive'])
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
            Detail Transfer
          </p>
          <h2 class="mt-1 text-xl font-semibold dark:text-white">{{ item.number }}</h2>
          <p class="mt-1 text-sm text-slate-500">{{ item.source }} ke {{ item.destination }}</p>
        </div>
        <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-100" @click="emit('close')">
          <X :size="20" />
        </button>
      </header>
      <div class="space-y-6 p-5">
        <div
          v-if="loading"
          class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500 dark:bg-[#0a1b33]"
        >
          Memuat detail transfer…
        </div>
        <div v-else-if="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
          {{ error }}
        </div>
        <template v-else>
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Status</span
            ><Badge :variant="item.variant">{{ item.status }}</Badge>
          </div>
          <section>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">
              Daftar Barang
            </h3>
            <div
              class="mt-3 divide-y divide-slate-100 rounded-xl border dark:divide-[#29476b] dark:border-[#29476b]"
            >
              <div
                v-for="detail in item.details"
                :key="detail.detail_id"
                class="flex justify-between gap-4 p-4"
              >
                <div>
                  <p class="font-medium dark:text-white">{{ detail.product }}</p>
                  <p class="text-xs text-slate-500">{{ detail.sku }}</p>
                </div>
                <p class="text-sm font-semibold dark:text-white">
                  {{ detail.quantity }} {{ detail.unit
                  }}<span
                    v-if="item.workflow_status !== 'in_transit'"
                    class="block text-right text-xs text-emerald-600"
                    >Diterima {{ detail.received_quantity }}</span
                  >
                </p>
              </div>
            </div>
          </section>
          <section>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Timeline</h3>
            <ol class="mt-3 space-y-4">
              <li
                v-for="entry in item.timelines"
                :key="`${entry.event}-${entry.date}`"
                class="flex gap-3"
              >
                <span
                  class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"
                  ><CheckCircle2 :size="16"
                /></span>
                <div>
                  <p class="text-sm font-medium dark:text-white">{{ entry.note }}</p>
                  <p class="text-xs text-slate-500">{{ entry.date }} · {{ entry.user }}</p>
                </div>
              </li>
            </ol>
          </section>
          <div
            v-if="item.workflow_status === 'in_transit'"
            class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800"
          >
            <Clock3 :size="16" class="mr-2 inline" />Transfer menunggu konfirmasi penerimaan.
          </div>
        </template>
      </div>
      <footer
        class="sticky bottom-0 flex justify-end gap-2 border-t bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <Button variant="secondary" @click="emit('close')">Tutup</Button
        ><Button
          v-if="canReceive && !loading && !error && item.workflow_status === 'in_transit'"
          @click="emit('receive')"
          >Terima Transfer</Button
        >
      </footer>
    </aside>
  </Teleport>
</template>
