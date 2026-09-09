<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
  ArrowLeft,
  ArrowRight,
  BarChart3,
  BriefcaseBusiness,
  Building2,
  Check,
  CreditCard,
  FileText,
  HeartHandshake,
  Package,
  ReceiptText,
  Rocket,
  ShieldCheck,
  ShoppingBag,
  ShoppingCart,
  Store,
  TrendingUp,
  Users,
  Warehouse,
  Zap,
} from 'lucide-vue-next'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Input from '@/Components/UI/Input.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'

// ─── Tab State ───
const currentTab = ref(1)
const localError = ref('')

// ─── Form ───
const form = useForm({
  business_type: 'perusahaan',
  business: {
    logo: null,
    name: '',
    legal_name: '',
    phone: '',
    address: '',
    city: '',
    province: '',
    postal_code: '',
  },
  has_branches: false,
  branches: [{ name: '', code: 'CBG-01', address: '' }],
  has_warehouses: false,
  warehouses: [{ name: '', code: 'GDG-01', address: '' }],
  tax: { enabled: false, rate: 11, mode: 'inclusive', npwp: '' },
  features: [],
})

// ─── Features ───
const featureOptions = [
  {
    id: 'pos',
    name: 'Point of Sale',
    desc: 'Kasir & pembayaran',
    icon: ShoppingCart,
    color: 'bg-gradient-to-br from-orange-500 to-amber-400 text-white ring-orange-200',
    tooltip: 'Cocok untuk kamu yang ingin menggunakan kasir cepat dan praktis untuk toko, gerai, atau outlet Anda.',
  },
  {
    id: 'inventory',
    name: 'Manajemen Stok',
    desc: 'Stok & opname',
    icon: Package,
    color: 'bg-gradient-to-br from-emerald-500 to-teal-400 text-white ring-emerald-200',
    tooltip: 'Pantau stock barang, cek kekurangan stok, dan lakukan opname dengan lebih rapi tanpa ribet.',
  },
  {
    id: 'purchase',
    name: 'Pembelian',
    desc: 'PO & penerimaan',
    icon: ShoppingBag,
    color: 'bg-gradient-to-br from-violet-500 to-purple-400 text-white ring-violet-200',
    tooltip: 'Bantu tim pembelian mengelola pesanan supplier, penerimaan barang, dan catatan pembelian dengan lebih terstruktur.',
  },
  {
    id: 'sales',
    name: 'Penjualan',
    desc: 'Invoice & pengiriman',
    icon: ReceiptText,
    color: 'bg-gradient-to-br from-sky-500 to-cyan-400 text-white ring-sky-200',
    tooltip: 'Kelola transaksi penjualan, invoice, dan pengiriman dari satu tempat agar proses order makin lancar.',
  },
  {
    id: 'warehouse',
    name: 'Gudang',
    desc: 'Multi gudang',
    icon: Warehouse,
    color: 'bg-gradient-to-br from-stone-500 to-zinc-400 text-white ring-stone-200',
    tooltip: 'Ideal kalau bisnis Anda punya lebih dari satu gudang dan ingin kontrol stok tetap terorganisir tiap cabang.',
  },
  {
    id: 'finance',
    name: 'Keuangan',
    desc: 'Kas & jurnal',
    icon: CreditCard,
    color: 'bg-gradient-to-br from-rose-500 to-pink-400 text-white ring-rose-200',
    tooltip: 'Bikin pencatatan kas, bank, dan jurnal akuntansi lebih jelas untuk keputusan bisnis yang lebih cerdas.',
  },
  {
    id: 'hr',
    name: 'SDM / HR',
    desc: 'Karyawan & gaji',
    icon: BriefcaseBusiness,
    color: 'bg-gradient-to-br from-indigo-500 to-blue-400 text-white ring-indigo-200',
    tooltip: 'Mudah mengatur data karyawan, kehadiran, dan kebutuhan payroll tanpa perlu spreadsheet berantakan.',
  },
  {
    id: 'report',
    name: 'Laporan',
    desc: 'Dashboard & export',
    icon: BarChart3,
    color: 'bg-gradient-to-br from-fuchsia-500 to-pink-400 text-white ring-fuchsia-200',
    tooltip: 'Cocok untuk Anda yang ingin melihat insight bisnis, KPI, dan laporan cepat dalam satu dashboard menarik.',
  },
  {
    id: 'ecommerce',
    name: 'E-Commerce',
    desc: 'Toko online',
    icon: Store,
    color: 'bg-gradient-to-br from-cyan-500 to-teal-400 text-white ring-cyan-200',
    tooltip: 'Sempurna jika bisnis Anda punya toko online dan butuh sinkronisasi penjualan dengan operasional offline.',
  },
  {
    id: 'crm',
    name: 'CRM',
    desc: 'Pelanggan & follow up',
    icon: HeartHandshake,
    color: 'bg-gradient-to-br from-lime-500 to-green-400 text-white ring-lime-200',
    tooltip: 'Bantu tim sales dan customer service mengelola pelanggan, histori transaksi, dan follow-up dengan lebih teratur.',
  },
]

// ─── Validation & Navigation ───
const validateTab1 = () => {
  const valid = Boolean(
    form.business.name && form.business.phone && form.business.address && form.business.city
  )
  localError.value = valid ? '' : 'Lengkapi semua data wajib.'
  return valid
}

const nextTab = () => { if (currentTab.value === 1 && validateTab1()) currentTab.value = 2 }
const prevTab = () => { localError.value = ''; currentTab.value = 1 }

const toggleFeature = (id) => {
  const idx = form.features.indexOf(id)
  idx > -1 ? form.features.splice(idx, 1) : form.features.push(id)
}

const submit = () => form.post(route('onboarding.store'), { preserveScroll: true })
</script>

<template>
  <Head title="Setup Bisnis" />

  <div class="min-h-screen text-zinc-900 antialiased relative overflow-hidden" style="background: linear-gradient(135deg, #fafaf9 0%, #f5f5f4 25%, #f0f4f8 50%, #faf5f0 75%, #fafaf9 100%);">

    <!-- ─── Background Decorations ─── -->
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
      <!-- Dot grid pattern -->
      <div class="absolute inset-0" style="background-image: radial-gradient(circle, #d4d4d8 1px, transparent 1px); background-size: 24px 24px; opacity: 0.25;"></div>

      <!-- Soft gradient orbs -->
      <div class="absolute -top-32 -left-32 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-indigo-100/50 via-blue-50/30 to-transparent blur-3xl"></div>
      <div class="absolute -top-20 -right-24 h-96 w-96 rounded-full bg-gradient-to-bl from-sky-100/40 via-cyan-50/20 to-transparent blur-3xl"></div>
      <div class="absolute -bottom-40 -left-20 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-amber-50/50 via-orange-50/30 to-transparent blur-3xl"></div>
      <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-gradient-to-tl from-rose-50/40 via-pink-50/20 to-transparent blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[600px] w-[800px] rounded-full bg-gradient-to-b from-white/60 to-transparent blur-3xl"></div>

      <!-- Subtle diagonal lines -->
      <div class="absolute top-0 right-0 h-full w-1/3 opacity-[0.02]" style="background-image: repeating-linear-gradient(-45deg, #000 0, #000 1px, transparent 0, transparent 50%); background-size: 12px 12px;"></div>
    </div>

    <!-- ─── Minimal Header ─── -->
    <header class="sticky top-0 z-30 border-b border-zinc-100 bg-white/80 backdrop-blur-md">
      <div class="flex h-16 items-center justify-between px-6 lg:px-10">
        <Link href="/" class="group flex items-center gap-1.5 text-sm text-zinc-400 transition-colors hover:text-zinc-600">
          <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" />
          Kembali
        </Link>
        <!-- Logo di header hanya tampil di mobile (sidebar tersembunyi) -->
        <div class="flex items-center gap-2 lg:hidden">
          <img src="/favicon.webp" alt="Kastra" class="h-5 w-5 rounded-md object-cover" />
          <span class="text-sm font-semibold tracking-tight text-zinc-800">Kastra</span>
        </div>
        <div class="hidden lg:block"></div>
      </div>
    </header>

    <!-- ─── Main Layout: Side Panel + Content ─── -->
    <div class="relative z-10">

      <!-- ═══════════ LEFT: Branding / Value Panel (FIXED, full height) ═══════════ -->
      <aside class="hidden lg:flex lg:w-[420px] xl:w-[480px] fixed top-0 left-0 bottom-0 z-20 flex-col justify-between overflow-hidden border-r border-zinc-200/60 bg-white/70 px-10 pt-24 pb-10 backdrop-blur-sm xl:px-14">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.18),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.14),_transparent_30%),linear-gradient(135deg,_rgba(255,255,255,0.9),_rgba(248,250,252,0.75))]"></div>
        <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-zinc-200 to-transparent"></div>
        <div class="absolute -left-16 top-24 h-52 w-52 rounded-full bg-gradient-to-br from-violet-200/60 via-indigo-100/50 to-transparent blur-3xl"></div>
        <div class="absolute -right-10 bottom-12 h-56 w-56 rounded-full bg-gradient-to-br from-cyan-200/50 via-sky-100/40 to-transparent blur-3xl"></div>

        <div class="relative z-10">
          <!-- Top: Logo + Tagline -->
          <div>
            <div class="flex items-center gap-2.5">
              <img src="/images/logo.webp" alt="Kastra" class="h-10 w-10 rounded-lg object-cover dark:hidden" />
              <img src="/images/logo-white.webp" alt="Kastra" class="hidden h-10 w-10 rounded-lg object-cover dark:block" />
              <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-white">Kastra</span>
            </div>
            <p class="mt-1.5 text-[13px] font-medium tracking-[0.2em] text-zinc-400 uppercase">Enterprise Resource Planning</p>
          </div>

          <!-- Middle: Value Props -->
          <div class="mt-10 space-y-8">
            <div>
              <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-violet-700">
                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                Built for growth
              </div>
              <h2 class="text-[28px] font-bold leading-tight tracking-tight text-zinc-900">
                Kelola bisnis<br />dalam satu platform.
              </h2>
              <p class="mt-3 text-[15px] leading-relaxed text-zinc-500">
                Setup hanya butuh 2 menit. Mulai kelola penjualan, stok, keuangan, dan tim Anda dari satu dashboard.
              </p>
            </div>

            <!-- Feature Highlights -->
            <div class="space-y-4">
              <div class="group flex items-start gap-3.5 rounded-2xl border border-white/60 bg-white/70 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)] backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(99,102,241,0.08)]">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-400 shadow-md shadow-orange-200/80">
                  <Zap class="h-4 w-4 text-white" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-zinc-800">Setup Cepat</p>
                  <p class="mt-0.5 text-[13px] leading-relaxed text-zinc-500">Hanya 2 langkah — isi data perusahaan, pilih fitur, dan langsung gunakan.</p>
                </div>
              </div>
              <div class="group flex items-start gap-3.5 rounded-2xl border border-white/60 bg-white/70 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)] backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(14,165,233,0.08)]">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-400 shadow-md shadow-sky-200/80">
                  <BarChart3 class="h-4 w-4 text-white" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-zinc-800">Laporan Real-time</p>
                  <p class="mt-0.5 text-[13px] leading-relaxed text-zinc-500">Pantau performa bisnis dengan dashboard dan laporan yang selalu update.</p>
                </div>
              </div>
              <div class="group flex items-start gap-3.5 rounded-2xl border border-white/60 bg-white/70 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)] backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(16,185,129,0.08)]">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 shadow-md shadow-emerald-200/80">
                  <Users class="h-4 w-4 text-white" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-zinc-800">Multi Cabang & Tim</p>
                  <p class="mt-0.5 text-[13px] leading-relaxed text-zinc-500">Kelola banyak cabang, gudang, dan tim dengan role-based access control.</p>
                </div>
              </div>
              <div class="group flex items-start gap-3.5 rounded-2xl border border-white/60 bg-white/70 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)] backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(244,114,182,0.08)]">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-400 shadow-md shadow-pink-200/80">
                  <ShieldCheck class="h-4 w-4 text-white" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-zinc-800">Data Aman</p>
                  <p class="mt-0.5 text-[13px] leading-relaxed text-zinc-500">Enkripsi end-to-end dan backup otomatis untuk melindungi data bisnis Anda.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom: Social Proof -->
        <div class="relative z-10 mt-8 space-y-4 rounded-2xl border border-white/70 bg-white/70 p-4 shadow-[0_12px_28px_rgba(15,23,42,0.05)] backdrop-blur-sm">
          <div class="flex items-center gap-3">
            <div class="flex -space-x-2">
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 ring-2 ring-white"></div>
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 ring-2 ring-white"></div>
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 ring-2 ring-white"></div>
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-[10px] font-bold text-zinc-600 ring-2 ring-white">+99</div>
            </div>
            <p class="text-[13px] text-zinc-600">Dipercaya oleh <span class="font-semibold text-zinc-800">500+</span> bisnis</p>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="flex gap-0.5">
              <svg v-for="i in 5" :key="i" class="h-3.5 w-3.5 fill-amber-400" viewBox="0 0 20 20"><path d="M10 1l2.39 6.36H19l-5.19 4.18L15.56 18 10 14.18 4.44 18l1.75-6.46L1 7.36h6.61z"/></svg>
            </div>
            <span class="text-xs text-zinc-500">4.9/5 dari 200+ review</span>
          </div>
        </div>
      </aside>

      <!-- ═══════════ RIGHT: Form Content (SCROLLABLE) ═══════════ -->
      <main class="flex-1 lg:ml-[420px] xl:ml-[480px] flex items-start justify-center px-6 pt-6 pb-10 sm:px-10 lg:pt-8 min-h-[calc(100vh-64px)]">
        <div class="w-full max-w-xl">

      <!-- ─── Progress Indicator ─── -->
      <div class="pb-6">
        <div class="flex items-center justify-center gap-0">
          <div :class="[
            'flex h-9 w-9 items-center justify-center rounded-full text-[13px] font-semibold transition-all duration-300',
            currentTab >= 1 ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400',
          ]">
            <Check v-if="currentTab > 1" class="h-4 w-4" />
            <span v-else>1</span>
          </div>
          <div :class="['h-[2px] w-16 rounded-full transition-colors duration-300', currentTab > 1 ? 'bg-zinc-900' : 'bg-zinc-200']"></div>
          <div :class="[
            'flex h-9 w-9 items-center justify-center rounded-full text-[13px] font-semibold transition-all duration-300',
            currentTab >= 2 ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400',
          ]">
            <span>2</span>
          </div>
        </div>
        <div class="mt-3 flex justify-center gap-16">
          <span :class="['text-xs font-medium transition-colors', currentTab >= 1 ? 'text-zinc-700' : 'text-zinc-300']">Perusahaan</span>
          <span :class="['text-xs font-medium transition-colors', currentTab >= 2 ? 'text-zinc-700' : 'text-zinc-300']">Fitur</span>
        </div>
      </div>

      <!-- ═══════════ TAB 1 ═══════════ -->
      <div v-if="currentTab === 1" class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8">
        <div class="mb-6 text-center">
          <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Informasi Perusahaan</h1>
          <p class="mt-2 text-[15px] text-zinc-400">Lengkapi identitas bisnis Anda.</p>
        </div>

        <div class="space-y-8">
          <!-- Logo -->
          <div class="flex justify-center">
            <FileUpload
              id="business-logo"
              :model-value="form.business.logo"
              accept=".jpg,.jpeg,.png,.webp"
              label=""
              hint="JPG, PNG, WebP. Maks 2 MB."
              :error="form.errors['business.logo']"
              :max-size="2"
              class="w-32"
              @update:model-value="form.business.logo = $event"
            />
          </div>

          <!-- Fields -->
          <div class="space-y-5">
            <Input
              id="business-name"
              v-model="form.business.name"
              label="Nama Bisnis"
              placeholder="Kastra Nusantara"
              :error="form.errors['business.name']"
              required
            />
            <Input
              id="business-legal-name"
              v-model="form.business.legal_name"
              label="Nama Legal"
              placeholder="PT Kastra Nusantara Indonesia"
              :error="form.errors['business.legal_name']"
            />
            <div class="grid gap-5 sm:grid-cols-2">
              <Input
                id="business-phone"
                v-model="form.business.phone"
                type="tel"
                label="Telepon"
                placeholder="0812 3456 7890"
                :error="form.errors['business.phone']"
                required
              />
              <Input
                id="business-city"
                v-model="form.business.city"
                label="Kota"
                placeholder="Jakarta Pusat"
                :error="form.errors['business.city']"
                required
              />
            </div>
            <div>
              <label for="business-address" class="block">
                <span class="mb-2 block text-sm font-medium text-zinc-700">
                  Alamat <span class="text-red-400">*</span>
                </span>
                <textarea
                  id="business-address"
                  v-model="form.business.address"
                  rows="3"
                  placeholder="Jl. Merdeka No. 10, Kecamatan Gambir"
                  :class="[
                    'w-full resize-none rounded-xl border px-4 py-3 text-[15px] text-zinc-900 outline-none transition placeholder:text-zinc-300',
                    form.errors['business.address']
                      ? 'border-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-50'
                      : 'border-zinc-200 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100',
                  ]"
                ></textarea>
                <p v-if="form.errors['business.address']" class="mt-1.5 text-sm text-red-400">
                  {{ form.errors['business.address'] }}
                </p>
              </label>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
              <Input
                id="business-province"
                v-model="form.business.province"
                label="Provinsi"
                placeholder="DKI Jakarta"
                :error="form.errors['business.province']"
              />
              <Input
                id="business-postal-code"
                v-model="form.business.postal_code"
                label="Kode Pos"
                placeholder="10110"
                :error="form.errors['business.postal_code']"
                inputmode="numeric"
              />
            </div>
          </div>

          <!-- Error -->
          <p v-if="localError" class="text-center text-sm font-medium text-red-400">{{ localError }}</p>

          <!-- CTA -->
          <div class="pt-4">
            <button
              type="button"
              class="group flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 py-3.5 text-[15px] font-medium text-white transition-all hover:bg-zinc-800 active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-zinc-200"
              @click="nextTab"
            >
              Lanjutkan
              <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- ═══════════ TAB 2 ═══════════ -->
      <div v-if="currentTab === 2" class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8">
        <div class="mb-6 text-center">
          <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Pilih Fitur</h1>
          <p class="mt-2 text-[15px] text-zinc-400">Pilih modul yang ingin digunakan. Bisa diubah kapan saja.</p>
        </div>

        <!-- Feature Grid -->
        <div class="grid gap-3 sm:grid-cols-2">
          <div v-for="feature in featureOptions" :key="feature.id" class="group relative">
            <button
              type="button"
              :title="feature.tooltip"
              :class="[
                'flex w-full items-center gap-4 rounded-xl border p-4 text-left transition-all duration-200',
                form.features.includes(feature.id)
                  ? 'border-zinc-300 bg-zinc-50 shadow-sm shadow-zinc-200/60'
                  : 'border-zinc-100 hover:border-zinc-200 hover:bg-zinc-50',
              ]"
              @click="toggleFeature(feature.id)"
            >
              <!-- Icon -->
              <div :class="['flex h-11 w-11 items-center justify-center rounded-xl ring-1 shadow-sm', feature.color]">
                <component :is="feature.icon" class="h-5 w-5" />
              </div>

              <!-- Text -->
              <div class="min-w-0 flex-1">
                <p :class="['text-sm font-semibold transition-colors', form.features.includes(feature.id) ? 'text-zinc-900' : 'text-zinc-700']">
                  {{ feature.name }}
                </p>
                <p class="mt-0.5 text-xs text-zinc-400">{{ feature.desc }}</p>
              </div>

              <!-- Checkbox -->
              <div :class="[
                'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-all duration-200',
                form.features.includes(feature.id)
                  ? 'border-zinc-900 bg-zinc-900'
                  : 'border-zinc-200 group-hover:border-zinc-300',
              ]">
                <Check v-if="form.features.includes(feature.id)" class="h-3 w-3 text-white" />
              </div>
            </button>

            <div
              class="pointer-events-none absolute left-1/2 top-0 z-20 w-[280px] -translate-x-1/2 -translate-y-[calc(100%+12px)] rounded-xl border border-zinc-300 bg-white/95 px-3.5 py-2.5 text-left text-[12px] font-medium leading-relaxed text-zinc-700 opacity-0 shadow-xl shadow-zinc-200/80 ring-1 ring-zinc-100 transition-all duration-200 group-hover:opacity-100"
            >
              <div class="mb-1 flex items-center gap-1.5 text-sm font-bold text-zinc-900">
                <span>{{ feature.name }}</span>
              </div>
              <p>{{ feature.tooltip }}</p>
              <div class="absolute -bottom-1.5 left-1/2 h-3.5 w-3.5 -translate-x-1/2 rotate-45 border-b border-r border-zinc-300 bg-white/95"></div>
            </div>
          </div>
        </div>

        <!-- Count -->
        <p class="mt-6 text-center text-sm text-zinc-400">
          <span class="font-semibold text-zinc-700">{{ form.features.length }}</span> fitur dipilih
        </p>

        <!-- CTA -->
        <div class="mt-8 space-y-3">
          <button
            type="button"
            :disabled="form.processing"
            class="group flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 py-3.5 text-[15px] font-medium text-white transition-all hover:bg-zinc-800 active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-zinc-200 disabled:opacity-50"
            @click="submit"
          >
            <Rocket class="h-4 w-4" />
            {{ form.processing ? 'Memproses...' : 'Selesaikan & Buat Data' }}
          </button>
          <button
            type="button"
            class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 py-3 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-700 focus:outline-none"
            @click="prevTab"
          >
            <ArrowLeft class="h-4 w-4" />
            Kembali
          </button>
        </div>
      </div>

        </div>
      </main>
    </div>
  </div>
</template>
