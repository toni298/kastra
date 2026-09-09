<script setup>
import {
  ArrowUpRight,
  Boxes,
  Building2,
  CheckCircle2,
  ReceiptText,
  ShoppingBag,
  Warehouse,
} from '@lucide/vue'

const metrics = [
  {
    label: 'Penjualan hari ini',
    value: 'Rp24,8 jt',
    change: '+12,4%',
    icon: ShoppingBag,
    tone: 'text-blue-600 bg-blue-50 dark:bg-blue-500/10 dark:text-blue-300',
  },
  {
    label: 'Transaksi',
    value: '186',
    change: '+8,2%',
    icon: ReceiptText,
    tone: 'text-violet-600 bg-violet-50 dark:bg-violet-500/10 dark:text-violet-300',
  },
  {
    label: 'Stok menipis',
    value: '8 produk',
    change: 'Perlu tindakan',
    icon: Boxes,
    tone: 'text-amber-600 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-300',
  },
]

const chartBars = [42, 57, 46, 68, 61, 84, 76, 94, 82, 100, 89, 108]
</script>

<template>
  <div class="relative mx-auto w-full max-w-[610px] lg:mx-0">
    <div
      class="absolute -inset-10 -z-10 rounded-full bg-blue-300/20 blur-3xl dark:bg-blue-500/10"
      aria-hidden="true"
    ></div>
    <div
      class="absolute -right-5 -top-5 -z-10 size-28 rounded-full border border-blue-200 dark:border-blue-500/20"
      aria-hidden="true"
    ></div>

    <div
      class="overflow-hidden rounded-[26px] border border-slate-200/90 bg-white shadow-[0_30px_80px_-28px_rgba(15,23,42,0.32)] transition-colors duration-300 dark:border-slate-700/80 dark:bg-slate-900 dark:shadow-black/40"
    >
      <div
        class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5 sm:px-5 dark:border-slate-800"
      >
        <div class="flex items-center gap-3">
          <div
            class="grid size-9 place-items-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/20"
          >
            <Building2 :size="17" aria-hidden="true" />
          </div>
          <div>
            <p class="text-xs font-medium text-slate-900 sm:text-sm dark:text-white">
              Kastra Dashboard
            </p>
            <p class="text-[11px] text-slate-500 sm:text-[12px] dark:text-slate-400">
              Cabang Jakarta · Semua outlet
            </p>
          </div>
        </div>
        <span
          class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
        >
          <span class="size-1.5 rounded-full bg-emerald-500"></span>
          Live
        </span>
      </div>

      <div class="space-y-3.5 bg-slate-50/80 p-3.5 sm:p-5 dark:bg-[#0b1628]">
        <div class="grid grid-cols-3 gap-2 sm:gap-3">
          <article
            v-for="metric in metrics"
            :key="metric.label"
            class="min-w-0 rounded-xl border border-slate-100 bg-white p-2.5 sm:rounded-2xl sm:p-3.5 dark:border-slate-800 dark:bg-slate-900"
          >
            <div :class="['grid size-7 place-items-center rounded-lg sm:size-8', metric.tone]">
              <component :is="metric.icon" :size="14" aria-hidden="true" />
            </div>
            <p class="mt-2 truncate text-[10px] text-slate-500 sm:text-[12px] dark:text-slate-400">
              {{ metric.label }}
            </p>
            <p
              class="mt-1 truncate text-xs font-semibold text-slate-900 sm:text-base dark:text-white"
            >
              {{ metric.value }}
            </p>
            <p
              class="mt-0.5 truncate text-[9px] font-medium text-emerald-600 sm:text-[11px] dark:text-emerald-400"
            >
              {{ metric.change }}
            </p>
          </article>
        </div>

        <div class="grid gap-3 sm:grid-cols-[1.55fr_0.85fr]">
          <article
            class="rounded-2xl border border-slate-100 bg-white p-3.5 sm:p-4 dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between">
              <div>
                <p class="text-xs font-medium text-slate-900 dark:text-white">Performa penjualan</p>
                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                  12 bulan terakhir
                </p>
              </div>
              <span
                class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400"
              >
                <ArrowUpRight :size="13" aria-hidden="true" />
                18,6%
              </span>
            </div>
            <div
              class="relative mt-5 flex h-28 items-end gap-1.5 border-b border-slate-100 dark:border-slate-800"
              aria-label="Grafik penjualan meningkat"
              role="img"
            >
              <span
                class="pointer-events-none absolute inset-x-0 top-1/3 border-t border-dashed border-slate-200 dark:border-slate-700"
              ></span>
              <span
                class="pointer-events-none absolute inset-x-0 top-2/3 border-t border-dashed border-slate-200 dark:border-slate-700"
              ></span>
              <span
                v-for="(height, index) in chartBars"
                :key="`${height}-${index}`"
                class="relative flex-1 rounded-t bg-blue-200 transition-colors first:bg-blue-100 last:bg-blue-600 dark:bg-blue-500/35 dark:first:bg-blue-500/20 dark:last:bg-blue-400"
                :style="{ height: `${height / 1.2}%` }"
              ></span>
            </div>
          </article>

          <div class="grid grid-cols-2 gap-2 sm:grid-cols-1">
            <article
              class="flex items-center gap-2.5 rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900"
            >
              <span
                class="grid size-8 shrink-0 place-items-center rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-300"
              >
                <Warehouse :size="15" aria-hidden="true" />
              </span>
              <div class="min-w-0">
                <p
                  class="truncate text-[11px] font-medium text-slate-800 sm:text-xs dark:text-white"
                >
                  Stok tersinkron
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">4 gudang</p>
              </div>
            </article>
            <article
              class="flex items-center gap-2.5 rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900"
            >
              <span
                class="grid size-8 shrink-0 place-items-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"
              >
                <ReceiptText :size="15" aria-hidden="true" />
              </span>
              <div class="min-w-0">
                <p
                  class="truncate text-[11px] font-medium text-slate-800 sm:text-xs dark:text-white"
                >
                  Jurnal otomatis
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">186 transaksi</p>
              </div>
            </article>
            <article
              class="col-span-2 flex items-center gap-2.5 rounded-xl border border-slate-100 bg-white p-3 sm:col-span-1 dark:border-slate-800 dark:bg-slate-900"
            >
              <span
                class="grid size-8 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"
              >
                <Boxes :size="15" aria-hidden="true" />
              </span>
              <div class="min-w-0">
                <p
                  class="truncate text-[11px] font-medium text-slate-800 sm:text-xs dark:text-white"
                >
                  Inventory sehat
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">98,7% akurat</p>
              </div>
            </article>
          </div>
        </div>

        <div
          class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-[11px] font-semibold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
        >
          <CheckCircle2 :size="14" class="shrink-0" aria-hidden="true" />
          Semua sistem operasional berjalan normal
        </div>
      </div>
    </div>

    <div
      class="decor-float absolute -bottom-6 -left-4 hidden items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl sm:flex dark:border-slate-700 dark:bg-slate-900"
    >
      <span
        class="grid size-9 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"
      >
        <CheckCircle2 :size="17" aria-hidden="true" />
      </span>
      <div>
        <p class="text-[11px] font-medium text-slate-900 dark:text-white">Data tersinkron</p>
        <p class="text-[10px] text-slate-500 dark:text-slate-400">Baru saja diperbarui</p>
      </div>
    </div>
  </div>
</template>
