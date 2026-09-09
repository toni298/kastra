<script setup>
import Button from '@/Components/UI/Button.vue'
import {
  BadgeCheck,
  Building2,
  Check,
  Mail,
  MapPin,
  Percent,
  Phone,
  ReceiptText,
  Store,
  Warehouse,
} from 'lucide-vue-next'

const props = defineProps({
  form: { type: Object, required: true },
})
const emit = defineEmits(['edit'])

const businessTypeLabel = props.form.business_type === 'perusahaan' ? 'Perusahaan' : 'Perorangan'
const taxModeLabel =
  props.form.tax.mode === 'inclusive' ? 'Gross (Termasuk PPN)' : 'Net (Belum termasuk PPN)'
</script>

<template>
  <section class="mx-auto max-w-4xl">
    <div class="text-center">
      <h1 class="text-3xl font-semibold text-slate-950">Tinjau & Selesaikan Setup</h1>
      <p class="mt-2 text-sm font-medium text-slate-500">
        Pastikan seluruh informasi bisnis sudah benar sebelum data dibuat.
      </p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-start gap-4">
          <span
            class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"
          >
            <Store class="h-6 w-6" />
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
                  Jenis Usaha
                </p>
                <h2 class="mt-1 text-xl font-semibold text-slate-950">{{ businessTypeLabel }}</h2>
              </div>
              <Button
                variant="ghost"
                aria-label="Edit jenis usaha"
                class="shrink-0 !px-2 !py-1 text-sm !text-emerald-600"
                @click="emit('edit', 1)"
              >
                Edit
              </Button>
            </div>
            <p class="mt-3 text-sm leading-6 text-slate-500">
              {{
                form.business_type === 'perusahaan'
                  ? 'Struktur bisnis disiapkan untuk badan usaha dengan pengelolaan operasional yang terorganisir.'
                  : 'Struktur bisnis disiapkan untuk usaha milik pribadi dan operasional mandiri.'
              }}
            </p>
          </div>
        </div>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-start gap-4">
          <span
            class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"
          >
            <Building2 class="h-6 w-6" />
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
                  Informasi Perusahaan
                </p>
                <h2 class="mt-1 text-lg font-semibold text-slate-950">{{ form.business.name }}</h2>
                <p v-if="form.business.legal_name" class="mt-0.5 text-sm text-slate-500">
                  {{ form.business.legal_name }}
                </p>
              </div>
              <Button
                variant="ghost"
                aria-label="Edit informasi perusahaan"
                class="shrink-0 !px-2 !py-1 text-sm !text-emerald-600"
                @click="emit('edit', 2)"
              >
                Edit
              </Button>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
              <p class="flex items-start gap-2.5 text-sm leading-6 text-slate-600 sm:col-span-2">
                <MapPin class="mt-1 h-4 w-4 shrink-0 text-emerald-600" />
                <span
                  >{{ form.business.address }}, {{ form.business.city
                  }}<template v-if="form.business.province"
                    >, {{ form.business.province }}</template
                  ></span
                >
              </p>
              <p class="flex items-center gap-2.5 text-sm text-slate-600">
                <Phone class="h-4 w-4 shrink-0 text-emerald-600" /> {{ form.business.phone }}
              </p>
              <p class="flex items-center gap-2.5 text-sm text-slate-600">
                <Mail class="h-4 w-4 shrink-0 text-emerald-600" />
                {{ form.business.postal_code || 'Kode pos belum diisi' }}
              </p>
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-start gap-4">
          <span
            class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"
            ><MapPin class="h-6 w-6"
          /></span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
                  Lokasi Usaha (Cabang)
                </p>
                <h2 class="mt-1 text-lg font-semibold text-slate-950">
                  {{
                    form.has_branches ? `${form.branches.length} Lokasi Usaha` : 'Tidak Ada Cabang'
                  }}
                </h2>
              </div>
              <Button
                variant="ghost"
                aria-label="Edit lokasi usaha"
                class="shrink-0 !px-2 !py-1 text-sm !text-emerald-600"
                @click="emit('edit', 3)"
              >
                Edit
              </Button>
            </div>
            <template v-if="form.has_branches">
              <p class="mt-2 text-sm text-slate-500">Berikut lokasi usaha Anda:</p>
              <ol class="mt-4 space-y-3 sm:-ml-16 sm:w-[calc(100%+4rem)]">
                <li
                  v-for="(branch, index) in form.branches"
                  :key="branch.code"
                  class="flex gap-3 rounded-xl bg-slate-50 p-3.5"
                >
                  <span
                    class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700"
                    >{{ index + 1 }}</span
                  >
                  <div>
                    <p class="text-sm font-medium text-slate-900">
                      {{ branch.name }}
                      <span v-if="index === 0" class="text-emerald-600">(Utama)</span>
                    </p>
                    <p class="mt-1 text-sm leading-5 text-slate-500">{{ branch.address }}</p>
                  </div>
                </li>
              </ol>
            </template>
            <p v-else class="mt-2 text-sm text-slate-500">
              Lokasi cabang dapat ditambahkan nanti melalui menu Pengaturan.
            </p>
          </div>
        </div>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-start gap-4">
          <span
            class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"
            ><Warehouse class="h-6 w-6"
          /></span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Gudang</p>
                <h2 class="mt-1 text-lg font-semibold text-slate-950">
                  {{
                    form.has_warehouses
                      ? `${form.warehouses.length} Lokasi Gudang`
                      : 'Tidak Ada Gudang'
                  }}
                </h2>
              </div>
              <Button
                variant="ghost"
                aria-label="Edit gudang"
                class="shrink-0 !px-2 !py-1 text-sm !text-emerald-600"
                @click="emit('edit', 4)"
              >
                Edit
              </Button>
            </div>
            <template v-if="form.has_warehouses">
              <p class="mt-2 text-sm text-slate-500">Berikut lokasi gudang Anda:</p>
              <ol class="mt-4 space-y-3 sm:-ml-16 sm:w-[calc(100%+4rem)]">
                <li
                  v-for="(warehouse, index) in form.warehouses"
                  :key="warehouse.code"
                  class="flex gap-3 rounded-xl bg-slate-50 p-3.5"
                >
                  <span
                    class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700"
                    >{{ index + 1 }}</span
                  >
                  <div>
                    <p class="text-sm font-medium text-slate-900">
                      {{ warehouse.name }}
                      <span v-if="index === 0" class="text-emerald-600">(Utama)</span>
                    </p>
                    <p class="mt-1 text-sm leading-5 text-slate-500">{{ warehouse.address }}</p>
                  </div>
                </li>
              </ol>
            </template>
            <p v-else class="mt-2 text-sm text-slate-500">
              Lokasi gudang dapat ditambahkan nanti melalui menu Pengaturan.
            </p>
          </div>
        </div>
      </article>

      <article
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 sm:p-6"
      >
        <div class="flex items-start gap-4">
          <span
            class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"
            ><Percent class="h-6 w-6"
          /></span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
                  Pengaturan Pajak
                </p>
                <h2 class="mt-1 text-lg font-semibold text-slate-950">
                  {{ form.tax.enabled ? 'Pajak Aktif' : 'Tidak Menggunakan Pajak' }}
                </h2>
              </div>
              <Button
                variant="ghost"
                aria-label="Edit pengaturan pajak"
                class="shrink-0 !px-2 !py-1 text-sm !text-emerald-600"
                @click="emit('edit', 5)"
              >
                Edit
              </Button>
            </div>
            <div v-if="form.tax.enabled" class="mt-5 grid gap-3 sm:grid-cols-2">
              <p class="flex items-start gap-2.5 text-sm text-slate-600">
                <BadgeCheck class="h-4 w-4 shrink-0 text-emerald-600" />
                <span
                  ><strong class="block text-slate-900">Pajak yang digunakan</strong
                  ><span class="mt-1 block">PPN (Pajak Pertambahan Nilai)</span></span
                >
              </p>
              <p class="flex items-start gap-2.5 text-sm text-slate-600">
                <Check class="h-4 w-4 shrink-0 text-emerald-600" />
                <span
                  ><strong class="block text-slate-900">Tarif PPN</strong
                  ><span class="mt-1 block">{{ form.tax.rate }}%</span></span
                >
              </p>
              <p class="flex items-start gap-2.5 text-sm text-slate-600">
                <ReceiptText class="h-4 w-4 shrink-0 text-emerald-600" />
                <span
                  ><strong class="block text-slate-900">Metode Pencatatan</strong
                  ><span class="mt-1 block">{{ taxModeLabel }}</span></span
                >
              </p>
              <p class="flex items-start gap-2.5 text-sm text-slate-600">
                <Building2 class="h-4 w-4 shrink-0 text-emerald-600" />
                <span
                  ><strong class="block text-slate-900">NPWP</strong
                  ><span class="mt-1 block">{{ form.tax.npwp || 'Belum diisi' }}</span></span
                >
              </p>
            </div>
            <p v-else class="mt-2 text-sm text-slate-500">
              Pengaturan pajak dapat diaktifkan nanti melalui menu Pengaturan.
            </p>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
