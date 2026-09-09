<script setup>
import { Link } from '@inertiajs/vue3'
import { Check, MessageCircle, Package } from 'lucide-vue-next'
import { resolveTheme } from './Themes/index.js'
import { formatRupiah, storeRoute } from '@/Utils/store'

const props = defineProps({
  store: { type: Object, required: true },
  order: { type: Object, required: true },
  whatsappUrl: { type: String, default: null },
})

const { StoreLayout } = resolveTheme(props.store.theme?.template)
</script>

<template>
  <StoreLayout :store="store">
    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center sm:p-10">
        <span
          class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-600 text-white"
        >
          <Check class="h-8 w-8" />
        </span>
        <h1 class="mt-5 text-2xl font-bold text-slate-900">Pesanan Berhasil Dibuat!</h1>
        <p class="mt-1 text-sm text-slate-500">Terima kasih, pesanan Anda telah kami terima.</p>

        <div class="mt-6 rounded-xl bg-slate-50 px-4 py-4">
          <p class="text-xs text-slate-500">Nomor Pesanan</p>
          <p class="mt-1 font-mono text-xl font-bold text-slate-900">{{ order.number }}</p>
          <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-200 pt-4 text-left">
            <div>
              <p class="text-xs text-slate-500">Total Pembayaran</p>
              <p class="mt-0.5 text-sm font-bold text-slate-900">{{ formatRupiah(order.total) }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Status</p>
              <span
                class="mt-0.5 inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700"
              >
                Pending
              </span>
            </div>
          </div>
        </div>

        <p class="mt-4 text-xs text-slate-400">
          Silakan chat admin melalui WhatsApp untuk konfirmasi pesanan Anda.
        </p>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
          <a
            v-if="whatsappUrl"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener"
            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
          >
            <MessageCircle class="h-4 w-4" />
            Chat Admin via WhatsApp
          </a>
          <Link
            :href="storeRoute(store, '/catalog')"
            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-400 hover:text-emerald-700"
          >
            <Package class="h-4 w-4" />
            Lihat Detail Pesanan
          </Link>
        </div>
      </div>
    </section>
  </StoreLayout>
</template>
