<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { Building2, MapPin, Warehouse, Percent, Pencil, ArrowUpRight } from 'lucide-vue-next'
import { useRoute } from '../../../../../vendor/tightenco/ziggy/src/js'

defineProps({
  data: { type: Object, required: true },
})

const page = usePage()
const route = useRoute(page.props.ziggy)

const details = [
  {
    key: 'lokasiUsaha',
    label: 'Lokasi Usaha',
    caption: 'Lihat semua lokasi',
    icon: MapPin,
    iconClass: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300',
    href: route('company.branches'),
  },
  {
    key: 'gudang',
    label: 'Gudang',
    caption: 'Lihat semua gudang',
    icon: Warehouse,
    iconClass: 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300',
    href: route('company.warehouses'),
  },
]
</script>

<template>
  <section
    class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <header
      class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700"
    >
      <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-950 dark:text-white">
        Bisnis Anda
      </h2>
      <Link
        :href="route('company.edit')"
        class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
      >
        Lihat Semua <ArrowUpRight class="h-4 w-4" />
      </Link>
    </header>

    <div class="flex flex-1 flex-col p-5">
      <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50">
        <div class="flex items-start gap-3">
          <div
            class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40"
          >
            <Building2 class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-base font-semibold leading-6 text-slate-950 dark:text-white">
                {{ data.nama }}
              </h3>
              <span
                class="rounded-full bg-emerald-100 px-2.5 py-1 text-[12px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
                >Perusahaan</span
              >
            </div>
            <p class="mt-1 text-sm font-medium leading-5 text-slate-500 dark:text-slate-400">
              {{ data.alamat }}
            </p>
          </div>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3">
        <Link
          v-for="detail in details"
          :key="detail.key"
          :href="detail.href"
          class="group rounded-xl border border-slate-200 p-3 transition duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50/40 hover:shadow-md dark:border-slate-700 dark:hover:border-emerald-700 dark:hover:bg-emerald-900/10"
        >
          <div class="flex items-center gap-3">
            <div
              :class="[
                'grid h-9 w-9 place-items-center rounded-lg transition group-hover:scale-105',
                detail.iconClass,
              ]"
            >
              <component :is="detail.icon" class="h-4 w-4" />
            </div>
            <p class="text-xl font-bold leading-7 tabular-nums text-slate-950 dark:text-white">
              {{ data[detail.key] }}
            </p>
          </div>
          <p class="mt-2 text-xs font-medium text-slate-700 dark:text-slate-300">
            {{ detail.label }}
          </p>
          <p
            class="mt-0.5 text-[12px] font-semibold text-slate-500 transition group-hover:text-emerald-600 dark:text-slate-400 dark:group-hover:text-emerald-400"
          >
            {{ detail.caption }} →
          </p>
        </Link>
      </div>

      <div
        class="mt-3 flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-700"
      >
        <div class="flex items-center gap-3">
          <div
            class="grid h-9 w-9 place-items-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300"
          >
            <Percent class="h-5 w-5" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-950 dark:text-white">PPN {{ data.ppn }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
              Pajak {{ data.statusPajak }}
            </p>
          </div>
        </div>
        <span
          class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
          >Aktif</span
        >
      </div>

      <Link
        :href="route('company.edit')"
        class="mt-5 flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700"
      >
        <Pencil class="h-4 w-4" /> Ubah Informasi Bisnis
      </Link>
    </div>
  </section>
</template>
