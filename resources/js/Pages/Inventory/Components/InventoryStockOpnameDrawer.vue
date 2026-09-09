<script setup>
import { CheckCircle2, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import { computed, ref } from 'vue'

const props = defineProps({
  item: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
const emit = defineEmits(['close'])
const search = ref('')
const expandedDetailId = ref(null)
const filteredDetails = computed(() => {
  const keyword = search.value.trim().toLocaleLowerCase('id-ID')
  const details = props.item.details ?? []
  if (!keyword) return details
  return details.filter((detail) =>
    `${detail.product} ${detail.sku}`.toLocaleLowerCase('id-ID').includes(keyword)
  )
})
const toggleDetail = (detailId) => {
  expandedDetailId.value = expandedDetailId.value === detailId ? null : detailId
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
            Detail Stock Opname
          </p>
          <h2 class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">
            {{ item.number }}
          </h2>
          <p class="mt-1 text-sm text-slate-500">
            {{ item.source_label }} · {{ item.location }} · {{ item.date }}
          </p>
        </div>
        <button
          type="button"
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
          aria-label="Tutup detail"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div class="space-y-6 p-5">
        <div
          v-if="loading"
          class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500 dark:bg-[#0a1b33]"
        >
          Memuat detail stock opname…
        </div>
        <div v-else-if="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
          {{ error }}
        </div>
        <template v-else>
          <section>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">
              Informasi Stock Opname
            </h3>
            <dl class="mt-3 grid gap-3 sm:grid-cols-2">
              <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
                <dt class="text-xs text-slate-400">Sumber</dt>
                <dd class="mt-1 font-semibold dark:text-white">{{ item.source_label }}</dd>
              </div>
              <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
                <dt class="text-xs text-slate-400">Lokasi</dt>
                <dd class="mt-1 font-semibold dark:text-white">{{ item.location }}</dd>
              </div>
              <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
                <dt class="text-xs text-slate-400">Status</dt>
                <dd class="mt-1">
                  <Badge :variant="item.variant">{{ item.status }}</Badge>
                </dd>
              </div>
              <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
                <dt class="text-xs text-slate-400">Tanggal</dt>
                <dd class="mt-1 font-semibold dark:text-white">{{ item.date }}</dd>
              </div>
            </dl>
          </section>
          <section>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Ringkasan</h3>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
              <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
                <p class="text-xs text-slate-400">Total Produk</p>
                <p class="mt-1 text-xl font-semibold dark:text-white">{{ item.total_products }}</p>
              </div>
              <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
                <p class="text-xs text-slate-400">Sudah Dihitung</p>
                <p class="mt-1 text-xl font-semibold text-emerald-600">
                  {{ item.checked_products }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
                <p class="text-xs text-slate-400">Total Selisih</p>
                <p class="mt-1 text-xl font-semibold dark:text-white">
                  {{ item.difference_label }}
                </p>
              </div>
            </div>
          </section>
          <section>
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
              <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">
                  Daftar Produk
                </h3>
                <span class="text-xs text-slate-500">{{ item.progress }}% selesai</span>
              </div>
              <SearchInput
                v-model="search"
                placeholder="Cari produk atau SKU..."
                aria-label="Cari produk pada detail opname"
              />
            </div>
            <div
              class="mt-3 max-h-[min(640px,60vh)] overflow-auto rounded-xl border border-slate-200 dark:border-[#29476b]"
            >
              <table class="min-w-full text-sm">
                <thead
                  class="sticky top-0 z-10 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 shadow-sm dark:bg-[#0a1b33] dark:text-slate-300"
                >
                  <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Sistem</th>
                    <th class="px-4 py-3">Fisik</th>
                    <th class="px-4 py-3">Selisih</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
                  <template v-for="detail in filteredDetails" :key="detail.id"
                    ><tr
                      class="cursor-pointer transition hover:bg-slate-50 dark:hover:bg-[#163354]"
                      :class="expandedDetailId === detail.id ? 'bg-slate-50 dark:bg-[#163354]' : ''"
                      tabindex="0"
                      @click="toggleDetail(detail.id)"
                      @keydown.enter="toggleDetail(detail.id)"
                    >
                      <td class="px-4 py-3 dark:text-white">
                        {{ detail.product
                        }}<span class="block text-xs text-slate-400">{{ detail.sku }}</span>
                      </td>
                      <td class="px-4 py-3">{{ detail.system_quantity }} {{ detail.unit }}</td>
                      <td class="px-4 py-3">
                        {{ detail.physical_quantity ?? '-' }}
                        {{ detail.physical_quantity !== null ? detail.unit : '' }}
                      </td>
                      <td class="px-4 py-3 font-semibold">{{ detail.difference ?? '-' }}</td>
                    </tr>
                    <tr
                      v-if="expandedDetailId === detail.id"
                      class="bg-slate-50/70 dark:bg-[#0a1b33]"
                    >
                      <td colspan="4" class="px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                          Catatan
                        </p>
                        <p
                          class="mt-1 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300"
                        >
                          {{ detail.note || 'Tidak ada catatan.' }}
                        </p>
                      </td>
                    </tr></template
                  >
                </tbody>
              </table>
            </div>
          </section>
          <div
            class="flex items-center gap-2 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300"
          >
            <CheckCircle2 :size="18" />Data detail diambil dari stock_opname_details.
          </div>
        </template>
      </div>
    </aside>
  </Teleport>
</template>
