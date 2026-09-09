<script setup>
import { onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  ArrowDownLeft,
  ArrowUpRight,
  BookOpenCheck,
  CircleCheck,
  ExternalLink,
  Landmark,
  Link2,
  ReceiptText,
  StickyNote,
  X,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'

defineProps({
  transaction: { type: Object, required: true },
})
const emit = defineEmits(['close'])

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value)
const formatAmount = (value) => `${value > 0 ? '+' : '-'}${formatCurrency(Math.abs(value))}`
const closeOnEscape = (event) => {
  if (event.key === 'Escape') emit('close')
}

const proofUrl = (tx) =>
  tx?.proof_file_url ?? (tx?.proof_file_path ? `/storage/${tx.proof_file_path}` : null)
const proofFileName = (tx) => {
  const path = tx?.proof_file_url ?? tx?.proof_file_path ?? ''
  return path ? path.split('/').pop() : ''
}

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape)
  document.body.style.overflow = 'hidden'
})
onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 bg-slate-950/45 backdrop-blur-[2px]"
      @click="emit('close')"
    ></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      aria-label="Detail transaksi kas dan bank"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white/95 p-5 backdrop-blur dark:border-[#29476b] dark:bg-[#102542]/95 sm:p-6"
      >
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
            Detail Transaksi
          </p>
          <h2 class="mt-1 truncate text-xl font-semibold text-slate-950 dark:text-white">
            {{ transaction.description }}
          </h2>
          <p class="mt-1 font-mono text-sm font-medium text-slate-500">
            {{ transaction.reference }}
          </p>
        </div>
        <button
          class="ml-4 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-[#163354] dark:hover:text-white"
          aria-label="Tutup detail"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div class="space-y-7 p-5 sm:p-6">
        <section
          :class="[
            'rounded-2xl border p-5',
            transaction.amount > 0
              ? 'border-emerald-100 bg-emerald-50/70 dark:border-emerald-400/20 dark:bg-emerald-400/10'
              : 'border-red-100 bg-red-50/70 dark:border-red-400/20 dark:bg-red-400/10',
          ]"
        >
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nominal</p>
              <p
                :class="[
                  'mt-1 text-2xl font-bold tabular-nums',
                  transaction.amount > 0
                    ? 'text-emerald-700 dark:text-emerald-300'
                    : 'text-red-600 dark:text-red-300',
                ]"
              >
                {{ formatAmount(transaction.amount) }}
              </p>
            </div>
            <span
              :class="[
                'grid size-12 place-items-center rounded-2xl',
                transaction.amount > 0
                  ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300'
                  : 'bg-red-100 text-red-700 dark:bg-red-400/15 dark:text-red-300',
              ]"
            >
              <component :is="transaction.amount > 0 ? ArrowDownLeft : ArrowUpRight" :size="23" />
            </span>
          </div>
          <div class="mt-4 flex flex-wrap items-center gap-2">
            <Badge :variant="transaction.statusVariant">{{ transaction.status }}</Badge>
            <span class="text-xs font-medium text-slate-500"
              >{{ transaction.displayDate }} ? {{ transaction.time }}</span
            >
          </div>
        </section>

        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <ReceiptText :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Ringkasan</h3>
          </div>
          <dl class="mt-3 grid gap-3 sm:grid-cols-2">
            <div
              v-for="item in [
                ['Nomor Referensi', transaction.reference],
                ['Jenis Transaksi', transaction.transactionType],
                ['Nominal', formatCurrency(Math.abs(transaction.amount))],
                ['Status', transaction.status],
                ['Tanggal', transaction.date],
                ['Sumber', transaction.source],
              ]"
              :key="item[0]"
              class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
            >
              <dt class="text-xs font-medium text-slate-400">{{ item[0] }}</dt>
              <dd class="mt-1.5 text-sm font-semibold text-slate-900 dark:text-white">
                {{ item[1] }}
              </dd>
            </div>
          </dl>
        </section>

        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <Link2 :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Sumber</h3>
          </div>
          <div class="mt-3 rounded-2xl border border-slate-200 p-4 dark:border-[#29476b]">
            <p class="font-semibold text-slate-950 dark:text-white">
              {{ transaction.sourceLabel }}
            </p>
            <p class="mt-1 text-sm text-slate-500">{{ transaction.party }}</p>
            <Link
              v-if="transaction.sourceRoute"
              :href="route(transaction.sourceRoute)"
              class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 hover:text-emerald-800 dark:text-emerald-300"
            >
              Buka transaksi sumber<ExternalLink :size="14" />
            </Link>
            <p v-else class="mt-3 text-xs font-semibold text-slate-400">
              Dicatat langsung di Kas & Bank
            </p>
          </div>
        </section>

        <section v-if="transaction.note">
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <StickyNote :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Keterangan</h3>
          </div>
          <div class="mt-3 rounded-2xl border border-slate-200 p-4 dark:border-[#29476b]">
            <p class="text-sm text-slate-700 dark:text-slate-200">{{ transaction.note }}</p>
          </div>
        </section>

        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <ExternalLink :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">BUKTI TRANSAKSI</h3>
          </div>
          <div class="mt-3 rounded-2xl border border-slate-200 p-4 dark:border-[#29476b]">
            <div v-if="proofUrl(transaction)">
              <a
                :href="proofUrl(transaction)"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 hover:text-emerald-800 dark:text-emerald-300"
              >
                {{ proofFileName(transaction) }}
                <ExternalLink :size="14" />
              </a>
            </div>
            <p v-else class="text-sm text-slate-500">Belum ada bukti transaksi.</p>
          </div>
        </section>

        <section v-if="transaction.journal.length">
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <BookOpenCheck :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Jurnal Otomatis</h3>
          </div>
          <div
            class="mt-3 overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]"
          >
            <div
              v-for="entry in transaction.journal"
              :key="`${entry.position}-${entry.account}`"
              class="grid grid-cols-[36px_minmax(0,1fr)_auto] gap-3 border-b border-slate-100 px-4 py-3.5 last:border-b-0 dark:border-[#29476b]"
            >
              <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{
                entry.position
              }}</span>
              <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{
                entry.account
              }}</span>
              <span class="text-sm font-bold tabular-nums text-slate-900 dark:text-white">
                {{ formatCurrency(entry.amount) }}
              </span>
            </div>
          </div>
        </section>

        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <CircleCheck :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Timeline</h3>
          </div>
          <ol class="mt-4 space-y-0">
            <li
              v-for="(event, index) in transaction.timeline"
              :key="event.label"
              class="relative flex gap-4 pb-6 last:pb-0"
            >
              <span
                v-if="index < transaction.timeline.length - 1"
                class="absolute left-[7px] top-4 h-full w-px bg-emerald-200 dark:bg-emerald-400/25"
              ></span>
              <span
                class="relative mt-1.5 size-4 shrink-0 rounded-full border-4 border-emerald-100 bg-emerald-600 dark:border-emerald-400/20 dark:bg-emerald-400"
              ></span>
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                  {{ event.label }}
                </p>
                <p class="mt-1 text-xs text-slate-500">{{ event.time }}</p>
              </div>
            </li>
          </ol>
        </section>
      </div>
    </aside>
  </Teleport>
</template>
