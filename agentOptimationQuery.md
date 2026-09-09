# Page Query Optimization Rules

Panduan ini melengkapi `AGENTS.md`. Gunakan untuk setiap halaman daftar data besar, terutama transaksi, stok, dan jurnal.

## Default Decision

- Gunakan `cursorPaginate()` untuk daftar yang dapat tumbuh besar.
- Gunakan pengurutan yang deterministik: kolom bisnis yang stabil lalu `id` sebagai tie-breaker.
- Pilih hanya kolom yang dibutuhkan tabel dan eager load semua relasi yang ditampilkan.
- Scope query selalu dengan `company_id` dan scope organisasi lain yang relevan.
- Jangan gunakan `paginate()` hanya untuk menampilkan nomor halaman. Cursor pagination tidak mendukung nomor halaman absolut.

```php
return PurchaseTransaction::query()
    ->select(['id', 'company_id', 'supplier_id', 'transaction_date', 'transaction_number'])
    ->with(['supplier:id,name'])
    ->where('company_id', $companyId)
    ->orderByDesc('transaction_date')
    ->orderByDesc('id')
    ->cursorPaginate($filters['per_page'] ?? 10)
    ->withQueryString();
```

## Cursor Requirements

- Seluruh kolom pada `orderBy` wajib ada pada hasil query (`select`).
- Urutan wajib konsisten dan unik secara gabungan; contoh: `transaction_date DESC, id DESC`.
- Jangan transform item CursorPaginator dengan `through()` menjadi array sebelum cursor dibentuk.
- Bungkus paginator menggunakan `JsonResource::collection($paginator)`, bukan `through(fn () => Resource::make()->resolve())`.
- Jangan merakit atau menebak cursor di frontend. Gunakan URL `next` yang dikirim Laravel.

## Resource Pagination Contract

Saat `JsonResource::collection()` membungkus CursorPaginator, frontend membaca:

```js
const nextUrl = pagination?.links?.next ?? null
const previousUrl = pagination?.links?.prev ?? null
const perPage = pagination?.meta?.per_page ?? 10
```

Jangan mengandalkan format paginator mentah berikut pada Resource Collection:

```js
pagination?.next_page_url
pagination?.per_page
```

Jika komponen UI lama membutuhkan format mentah, normalisasi sekali di batas komponen:

```js
const pagination = computed(() => ({
  ...props.pagination,
  next_page_url: props.pagination?.links?.next ?? null,
  prev_page_url: props.pagination?.links?.prev ?? null,
  per_page: props.pagination?.meta?.per_page ?? 10,
}))
```

## Infinite Scroll

- Infinite scroll digunakan bersama cursor pagination, bukan nomor halaman.
- Pakai `IntersectionObserver`; jangan gunakan listener `scroll` jika observer cukup.
- Observer harus mempunyai `root` berupa container tabel yang scrollable, bukan selalu `window`.
- Gunakan `rootMargin` sekitar `160px` sampai `240px` untuk prefetch batch berikutnya.
- Cegah request paralel dengan state `loadingMore`.
- Request halaman berikutnya memakai Inertia partial reload dan hanya prop daftar terkait.
- Gabungkan `data` lama dan baru di state halaman. Ganti penuh data ketika filter, pencarian, atau rows per page berubah.

```js
const loadMore = (url) => {
  if (!url || loadingMore.value) return

  loadingMore.value = true
  router.get(url, {}, {
    only: ['items'],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { loadingMore.value = false },
  })
}
```

- URL `links.next` Laravel sudah membawa parameter cursor dan `per_page`; jangan menggantinya dengan URL baru tanpa parameter tersebut.
- Jika user memilih 100 rows per page, load awal dan tiap batch infinite scroll harus sama-sama mengambil 100 data.
- Putuskan observer pada `onBeforeUnmount`.

## List Page UX

- Untuk list infinite yang panjang, batasi scroll di dalam card tabel agar halaman browser tidak terus memanjang.
- Ringkasan bisnis tetap di atas card tabel.
- Toolbar tabel (rows per page, pencarian, filter) harus sticky di atas container scroll.
- Tinggi container harus responsif dan berbasis viewport, misalnya `max-h-[55dvh]` untuk mobile dan `lg:max-h-[calc(100dvh-15rem)]` untuk desktop.
- Tampilkan status jelas di bawah tabel: `Scroll untuk memuat...` dan `Memuat...`.
- Pertahankan empty state, skeleton loading, dan feedback error.

## Query and Index Checklist

- Gunakan Eloquent, tidak `DB::table()` dan tidak raw SQL concatenation.
- Gunakan `$request->validated()` melalui Form Request.
- Filter teks menggunakan binding Eloquent (`where(..., 'like', "%{$search}%")`).
- Eager load relasi agar tidak terjadi N+1.
- Tambahkan indeks yang mengikuti query utama, umumnya scope lalu order:

```php
$table->index(['company_id', 'transaction_date', 'id']);
```

- Validasi indeks dengan `EXPLAIN` pada database nyata sebelum menambah indeks baru.
- Jangan menambah indeks hanya berdasarkan dugaan; indeks mempercepat baca tetapi menambah biaya tulis dan storage.

## Verification Checklist

- Pastikan jumlah item awal sama dengan pilihan Rows Per Page.
- Pastikan `links.next` ada saat masih tersedia batch berikutnya.
- Pastikan infinite scroll menambahkan, bukan mengganti, item yang sudah tampil.
- Uji pilihan 10 dan 100: setiap batch berikutnya harus sesuai nilai terpilih.
- Uji pencarian dan setiap filter: list harus reset ke batch pertama dan scroll container kembali ke atas bila diperlukan.
- Uji akhir data: sentinel hilang ketika `links.next` bernilai `null`.
- Jalankan formatter dan `npm run build` termasuk SSR build setelah perubahan frontend.
