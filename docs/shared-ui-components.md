# Shared UI Components

Komponen di `resources/js/Components/UI/` adalah primitive resmi aplikasi. Gunakan komponen ini sebelum membuat input, tabel, async select, signature pad, atau file uploader baru.

## Aturan umum

- Data halaman dan navigasi menggunakan Inertia.
- Axios hanya diperbolehkan di `AsyncSelect` untuk endpoint berawalan `/search/`.
- Otorisasi dan scope `cabang_id`/`outlet_id` wajib diterapkan di backend.
- Teruskan error dari Inertia form melalui prop `error`.
- Jangan gunakan `v-html` untuk label, hint, nilai tabel, atau nama file.
- Tabel operasional memakai cursor pagination 10 baris secara default.
- Dataset di atas 100 baris harus memakai tampilan berbasis `vue-virtual-scroller`, bukan memperbesar page size tabel biasa.

## DataTable

`DataTable.vue` menangani toolbar, pencarian debounce 400 ms, sorting, skeleton loading, empty state, dan cursor navigation.

```vue
<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import DataTable from '@/Components/UI/DataTable.vue'

const props = defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const columns = [
  { key: 'nama', label: 'Nama', sortable: true },
  { key: 'cabang.nama', label: 'Cabang' },
  { key: 'stok', label: 'Stok', class: 'text-right' },
]

const search = ref(props.filters.search ?? '')
const sortKey = ref(props.filters.sort ?? '')
const sortDirection = ref(props.filters.direction ?? 'asc')

const filter = ({ search: value }) => {
  router.get(
    route('bahan.index'),
    { search: value, sort: sortKey.value, direction: sortDirection.value },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

const sort = ({ key, direction }) => {
  router.get(
    route('bahan.index'),
    { search: search.value, sort: key, direction },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

const navigate = (url) => {
  if (url) router.visit(url, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <DataTable
    v-model:search="search"
    v-model:sort-key="sortKey"
    v-model:sort-direction="sortDirection"
    :items="items.data"
    :columns="columns"
    :next-url="items.next_page_url"
    :previous-url="items.prev_page_url"
    searchable
    @filter="filter"
    @sort="sort"
    @navigate="navigate"
  >
    <template #cell-stok="{ value }">
      <span class="font-semibold">{{ value }}</span>
    </template>
  </DataTable>
</template>
```

### Kontrak utama

| Prop/event                | Tipe                 | Keterangan                                        |
| ------------------------- | -------------------- | ------------------------------------------------- |
| `items`                   | `Array`              | Baris pada halaman cursor aktif                   |
| `columns`                 | `Array`              | `{ key, label, sortable?, class?, headerClass? }` |
| `loading`                 | `Boolean`            | Menampilkan skeleton                              |
| `searchable`              | `Boolean`            | Menampilkan input pencarian                       |
| `nextUrl` / `previousUrl` | `String \| null`     | URL cursor dari Inertia resource                  |
| `@filter`                 | `{ search }`         | Dikirim setelah debounce                          |
| `@sort`                   | `{ key, direction }` | Sorting harus dijalankan backend                  |
| `@navigate`               | `url`                | Navigasikan dengan Inertia router                 |

Gunakan slot `cell-{key}` untuk format khusus dan slot `filters`/`actions` untuk toolbar. Jangan menampilkan seluruh koleksi dengan `::all()`.

## CurrencyInput

`CurrencyInput.vue` menampilkan Rupiah locale Indonesia dan selalu menghasilkan integer melalui `v-model`.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'

const form = useForm({ harga: 0 })
</script>

<template>
  <CurrencyInput
    v-model="form.harga"
    label="Harga beli"
    name="harga"
    :min="0"
    :error="form.errors.harga"
    required
  />
</template>
```

Nilai `125000` ditampilkan sebagai `Rp 125.000`; nilai yang dikirim ke form tetap `125000`. Pecahan dan angka negatif tidak didukung.

## AsyncSelect

`AsyncSelect.vue` digunakan untuk pilihan yang dicari dari server. Komponen memakai Select2 dengan dukungan Arrow/Enter dan fallback `<select>` saat SSR/hydration.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'

const form = useForm({ outlet_id: null })
</script>

<template>
  <AsyncSelect
    v-model="form.outlet_id"
    :endpoint="route('search.outlet')"
    :initial-options="[]"
    label="Outlet"
    placeholder="Cari outlet..."
    :error="form.errors.outlet_id"
    required
  />
</template>
```

Endpoint wajib berada dalam grup `/search/`, memakai Form Request/otorisasi, dan tetap membatasi hasil berdasarkan akses cabang/outlet pengguna.

Format respons:

```json
{
  "results": [{ "id": 10, "text": "Outlet Sudirman" }],
  "next_cursor": "encoded-cursor",
  "pagination": {
    "more": true
  }
}
```

Prop `initialOptions` memakai bentuk `{ id, text }` dan harus memuat nilai terpilih ketika membuka form edit.

## SignaturePad

`SignaturePad.vue` adalah canvas native untuk mouse, touch, dan stylus. Nilainya berupa PNG data URL.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import SignaturePad from '@/Components/UI/SignaturePad.vue'

const form = useForm({ tanda_tangan: '' })
</script>

<template>
  <SignaturePad
    v-model="form.tanda_tangan"
    label="Tanda tangan penerima"
    :error="form.errors.tanda_tangan"
    required
  />
</template>
```

Backend wajib membatasi ukuran payload, memvalidasi format gambar, melakukan decode secara aman, dan menyimpan dengan nama UUID. Jangan mempercayai MIME/data URL dari browser.

## FileUpload

`FileUpload.vue` mendukung klik, drag-and-drop, validasi tipe/ukuran, preview, single/multiple file, dan reset.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import FileUpload from '@/Components/UI/FileUpload.vue'

const form = useForm({ bukti: null })
</script>

<template>
  <FileUpload
    v-model="form.bukti"
    accept=".pdf,.jpg,.jpeg,.png"
    label="Bukti pembayaran"
    :max-size="5"
    :error="form.errors.bukti"
    required
  />
</template>
```

Untuk beberapa file:

```vue
<FileUpload
  v-model="form.lampiran"
  accept=".pdf,image/*"
  label="Lampiran"
  :max-size="5"
  :max-files="5"
  multiple
/>
```

`maxSize` dinyatakan dalam MB per file. Event lama `@select` tetap tersedia untuk kompatibilitas, tetapi implementasi baru harus memakai `v-model`.

Backend tetap wajib menggunakan Form Request dengan MIME allowlist dan batas ukuran. Nama file penyimpanan harus UUID; nama asli hanya boleh menjadi metadata yang sudah disanitasi.

## Checklist sebelum membuat UI baru

1. Cari primitive yang sesuai di `Components/UI/`.
2. Gunakan props dan event yang sudah tersedia.
3. Teruskan loading, empty state, hint, disabled, required, dan error.
4. Gunakan Inertia untuk submit/navigasi.
5. Pastikan backend menerapkan Policy/Gate serta scope organisasi.
6. Pecah file Vue sebelum mencapai 300 baris.
