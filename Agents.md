# AGENTS.md — Project Rules & AI Code Generation Guidelines

> **This file is the single source of truth for all AI-generated code in this project.**
> Every rule here is non-negotiable. Read everything before writing a single line of code.
> If a task is unclear, ask for clarification — never assume.

---

## Stack

| Layer         | Technology                                 |
| ------------- | ------------------------------------------ |
| Backend       | Laravel (latest stable)                    |
| Frontend      | Vue 3 + Inertia.js (Full SSR, no REST API) |
| Styling       | TailwindCSS                                |
| State         | Pinia                                      |
| Authorization | Spatie Laravel Permission                  |
| Billing       | Not applicable (non-SaaS)                  |
| Date Picker   | @vuepic/vue-datepicker                     |
| Scroll        | vue-virtual-scroller                       |

---

## Business Concept

This system is **not SaaS** and **not multi-tenant by subscription**.
The system is designed for **one business entity** that can manage:

- multiple **cabang**
- each **cabang** can have multiple **outlet**
- operational data such as **bahan**, stock, transactions, and users are scoped by the company structure

### Hierarchy

```
Perusahaan
└── Cabang
    └── Outlet
```

### Access Roles

Only these application roles are valid:

- `owner`
- `admin`
- `karyawan`

Do not invent additional roles unless explicitly requested.

### Access Scope

- `owner` → full access to all cabang, all outlet, all master data, reports, and user management
- `admin` → access based on assigned cabang / outlet and permitted modules
- `karyawan` → limited operational access only to assigned outlet / cabang according to permissions

> Even though roles are fixed, authorization must still use **Policies / Gates / Permissions**, not manual role checks inside controllers or components.

---

## ⚠️ Critical Pre-Generation Checklist

Before writing **any** code, verify every item:

- [ ] Read this entire file from top to bottom
- [ ] Folder structure matches "Project Architecture" section
- [ ] Existing components reused — no new patterns unless discussed
- [ ] Every query on business data includes proper company scope (`cabang_id`, `outlet_id`) where applicable
- [ ] No hardcoded IDs, credentials, or env values
- [ ] No axios/fetch for page data — Inertia only
- [ ] Frontend Vue file does not exceed **300 lines**
- [ ] No `v-html` on user-generated content
- [ ] No `DB::table()` — Eloquent only
- [ ] No inline validation — Form Request classes only
- [ ] No role checks — Policies/Gates only
- [ ] No raw SQL string concatenation — Eloquent bindings only
- [ ] No `$request->all()` — always `$request->validated()`

---

## Project Architecture

### Backend

```
app/
├── Http/
│   ├── Controllers/      # Thin — max 300 lines, no business logic
│   ├── Middleware/       # Auth, security headers, rate limit, branch/outlet context
│   ├── Requests/         # All form validation — never inline
│   └── Resources/        # Resource transformers
├── Models/               # Eloquent only — define fillable/guarded
├── Services/             # Business logic — injected into controllers
├── Repositories/         # Data access — queries live here
├── Actions/              # Single-responsibility action classes
└── Exceptions/           # Custom exception handlers
```

### Frontend (`resources/js/`)

```
resources/js/
├── Pages/                # Inertia pages — grouped by module
├── Components/
│   └── UI/               # Shared primitives — always use, never recreate
├── Layouts/              # Layout wrappers
├── Composables/          # Reusable Vue logic
├── Stores/               # Pinia global state
└── Utils/                # Pure helper functions
```

### File Naming

| Type        | Convention     | Example             |
| ----------- | -------------- | ------------------- |
| Pages       | PascalCase.vue | `UserIndex.vue`     |
| Components  | PascalCase.vue | `UserTable.vue`     |
| Composables | camelCase.js   | `useUserFilters.js` |
| Stores      | camelCase.js   | `useAuthStore.js`   |
| Utils       | camelCase.js   | `formatCurrency.js` |

### Module Structure (modules with > 5 components)

```
Pages/Inventory/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Components/
│   ├── InventoryTable.vue
│   ├── InventoryForm.vue
│   ├── InventoryFilters.vue
│   ├── InventoryDeleteModal.vue
│   └── InventoryStockBadge.vue
└── Composables/
    ├── useInventoryForm.js
    └── useInventoryFilters.js
```

---

## 📏 Frontend File Size Rule — STRICT

**Every Vue file (Pages and Components) must not exceed 300 lines.**

This enforces readability, testability, and reliable AI code generation.

### Split strategy

| File exceeds 300 lines because of...      | Extract into...                        |
| ----------------------------------------- | -------------------------------------- |
| Complex form logic / validation           | `Composables/useXxxForm.js`            |
| Long template with distinct sections      | Child components (`XxxSection.vue`)    |
| Many computed / watchers                  | Pinia store or dedicated composable    |
| Repeated UI patterns                      | `Components/UI/` shared component      |
| Multiple responsibilities (god component) | Separate sibling components            |
| Modal logic mixed into page               | `XxxModal.vue` with its own composable |

### AI generation rule

> Before generating a Vue file, estimate the total line count.
> If it would exceed 300 lines — **stop, plan the split, then generate each file separately.**
> Never output a single file that handles: data fetching + filtering + table + modals + actions.

---

## Vue 3 Code Style Rules

### `<script setup>` block order — always follow this sequence

```vue
<script setup>
// 1. Imports
import { ref, computed, watch, onMounted } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import ComponentName from '@/Components/...'

// 2. Props & emits
const props = defineProps({ ... })
const emit  = defineEmits([...])

// 3. Composables / stores
const page  = usePage()
const store = useXxxStore()

// 4. Reactive state
const search  = ref('')
const loading = ref(false)

// 5. Computed
const filteredItems = computed(() => ...)

// 6. Methods / handlers
const handleSubmit = () => { ... }

// 7. Watchers
watch(search, useDebounceFn(() => { ... }, 400))

// 8. Lifecycle hooks
onMounted(() => { ... })
</script>
```

### `defineProps` must always include types and defaults

```js
const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    canCreate: { type: Boolean, default: false },
});
```

---

## Inertia.js Rules

### Use Inertia router for all navigation and form submission

```js
import { router, useForm } from "@inertiajs/vue3";

router.get(route("bahan.index"));

const form = useForm({
    nama: "",
    satuan_id: null,
    outlet_id: null,
});

const submit = () => {
    form.post(route("bahan.store"), {
        onSuccess: () => toast.success("Data berhasil disimpan."),
        onError: () => toast.error("Periksa kembali form Anda."),
    });
};
```

### FORBIDDEN — never use axios/fetch for page data

```js
axios.get('/bahan').then(...)
fetch('/bahan').then(...)
```

### Shared data → always via `usePage()`

```js
import { usePage } from "@inertiajs/vue3";
const { props } = usePage();
const user = computed(() => props.auth.user);
const permissions = computed(() => props.permissions || []);
const cabangAktif = computed(() => props.context?.cabang);
const outletAktif = computed(() => props.context?.outlet);
```

### HandleInertiaRequests — share only what's needed globally

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user()?->only('id', 'name', 'email'),
        ],
        'context' => [
            'cabang' => $request->attributes->get('current_cabang'),
            'outlet' => $request->attributes->get('current_outlet'),
        ],
        'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
        'flash' => [
            'success' => session('success'),
            'error' => session('error'),
        ],
    ]);
}
```

> Never share sensitive fields (passwords, tokens, full model data) via shared props.

### Controllers return Inertia responses — never JSON for page rendering

```php
return Inertia::render('Bahan/Index', [
    'items' => BahanResource::collection(
        $this->bahanRepository->paginateWithFilters($request->validated())
    ),
    'filters' => $request->only('search', 'cabang_id', 'outlet_id'),
]);
```

---

## Backend Architecture Rules

### Controllers — thin, max 300 lines, zero business logic

```php
class BahanController extends Controller
{
    public function __construct(
        private BahanService $bahanService,
        private BahanRepository $bahanRepository,
    ) {}

    public function index(IndexBahanRequest $request): Response
    {
        $this->authorize('viewAny', Bahan::class);

        return Inertia::render('Bahan/Index', [
            'items' => BahanResource::collection(
                $this->bahanRepository->paginateWithFilters($request->validated())
            ),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreBahanRequest $request): RedirectResponse
    {
        $this->bahanService->create($request->validated());

        return redirect()
            ->route('bahan.index')
            ->with('success', 'Data bahan berhasil dibuat.');
    }
}
```

### Services — all business logic lives here

```php
class BahanService
{
    public function create(array $data): Bahan
    {
        return DB::transaction(function () use ($data) {
            return Bahan::create($data);
        });
    }
}
```

### Repositories — all queries live here

```php
class BahanRepository
{
    public function paginateWithFilters(array $filters): CursorPaginator
    {
        return Bahan::query()
            ->with(['cabang', 'outlet', 'kategori', 'satuan'])
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where('nama', 'like', "%{$s}%")
            )
            ->when($filters['cabang_id'] ?? null, fn ($q, $id) =>
                $q->where('cabang_id', $id)
            )
            ->when($filters['outlet_id'] ?? null, fn ($q, $id) =>
                $q->where('outlet_id', $id)
            )
            ->latest()
            ->cursorPaginate(10);
    }
}
```

### Form Requests — all validation, never inline

```php
class StoreBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bahan.create');
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:100'],
            'kategori_id' => ['required', 'exists:kategori_bahan,id'],
            'satuan_id' => ['required', 'exists:satuan,id'],
            'cabang_id' => ['required', 'exists:cabang,id'],
            'outlet_id' => ['nullable', 'exists:outlet,id'],
            'stok_minimum' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

### Always eager load — zero N+1 tolerance

```php
Bahan::with(['cabang', 'outlet', 'kategori', 'satuan'])->cursorPaginate(10);
```

### Always define `fillable` — never `guarded = []`

```php
protected $fillable = [
    'nama',
    'kode',
    'kategori_id',
    'satuan_id',
    'cabang_id',
    'outlet_id',
    'stok_minimum',
];
```

---

## Security Rules — Production Ready

Security rules remain the same and are **still mandatory**.

### 1. CSRF Protection

Laravel CSRF middleware must always be active for web routes. Never disable it.

### 2. SQL Injection Prevention

Never concatenate user input into queries.

### 3. XSS Prevention

Never use `v-html` for user-generated content.

### 4. Mass Assignment Protection

Always use `$request->validated()` and explicit `$fillable`.

### 5. Password Hashing

Always hash passwords with Laravel `Hash::make()`.

### 6. File Upload Security

Use mime validation, size limits, UUID file names, and safe storage paths.

### 7. Security Headers Middleware

Security headers middleware must stay enabled.

### 8. Sensitive Data in Logs

Never log passwords, tokens, or confidential raw payloads.

### 9. Environment Variables — Production Checklist

```ini
APP_ENV=production
APP_DEBUG=false
APP_KEY=
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_DRIVER=database
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
```

### 10. Exception Handling

Never expose raw exceptions to end users in production.

### 11. Authorization — Policies, never manual role checks

```php
$this->authorize('update', $bahan);
Gate::authorize('bahan.export');
```

```php
if ($user->hasRole('admin')) { ... }
if ($user->role === 'owner') { ... }
```

---

## Company Structure Rules

This project uses **organizational scoping**, not SaaS tenancy.

### Core Data Structure

Every operational record must be attached to the appropriate business structure:

- `cabang_id` for branch-level data
- `outlet_id` for outlet-level data when needed

### Scope Rules

- Master data that applies globally may omit `outlet_id` but must follow the agreed business scope
- Data bahan, stok, transaksi, pembelian, penjualan, dan operasional outlet must always be scoped correctly
- Never query cross-cabang or cross-outlet data without explicit authorization
- Owner may access all scopes, but code must still go through Policies / Gates

### Example model relationships

```php
class Cabang extends Model
{
    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }
}

class Outlet extends Model
{
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}

class Bahan extends Model
{
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
```

### Query scoping must be explicit

```php
Bahan::query()
    ->when($user->can('viewAllCabang', Bahan::class) === false, function ($query) use ($user) {
        $query->whereIn('cabang_id', $user->accessibleCabangIds());
    })
    ->when($user->can('viewAllOutlet', Bahan::class) === false, function ($query) use ($user) {
        $query->whereIn('outlet_id', $user->accessibleOutletIds());
    });
```

Do not rely on frontend filtering alone.

### Logging must include scope context

```php
Log::info('Bahan created', [
    'bahan_id' => $bahan->id,
    'user_id' => auth()->id(),
    'cabang_id' => $bahan->cabang_id,
    'outlet_id' => $bahan->outlet_id,
]);
```

### Cache keys must be scope-aware when needed

```php
Cache::remember("permissions.user.{$userId}", 3600, fn () => $permissions);
Cache::remember("dashboard.cabang.{$cabangId}", 300, fn () => $this->buildCabangStats($cabangId));
Cache::remember("dashboard.outlet.{$outletId}", 300, fn () => $this->buildOutletStats($outletId));
```

---

## Role & Permission Rules

### Roles allowed in the system

Only these roles are valid:

- `owner`
- `admin`
- `karyawan`

### Always check permissions, never roles inline

```php
$this->authorize('create', Bahan::class);
Gate::allows('bahan.edit');
```

```php
if ($user->hasRole('owner')) { ... }
if ($user->hasRole('admin')) { ... }
```

### Permission naming: `{module}.{action}`

```
bahan.view       bahan.create       bahan.edit       bahan.delete
stok.view        stok.adjust        stok.opname      stok.transfer
pembelian.view   pembelian.create   pembelian.edit   pembelian.delete
penjualan.view   penjualan.create   penjualan.edit   penjualan.delete
users.view       users.create       users.edit       users.delete
cabang.view      cabang.create      cabang.edit      cabang.delete
outlet.view      outlet.create      outlet.edit      outlet.delete
laporan.view     laporan.export
```

### Role mapping guidance

- `owner` typically receives the broadest permission set
- `admin` receives management permissions according to assigned cabang / outlet
- `karyawan` receives only operational permissions needed for daily work

Permission assignment still comes from DB and authorization layer, never hardcoded in controllers.

---

## Database Rules

### Required structural tables

At minimum, system design should support these tables:

- `users`
- `roles`
- `permissions`
- `cabang`
- `outlet`
- `bahan`
- supporting master tables such as `kategori_bahan`, `satuan`, and stock movement tables as needed

### Relationship guidance

- one `cabang` has many `outlet`
- one `outlet` belongs to one `cabang`
- `users` may be assigned to one or more cabang / outlet according to business rules
- `bahan` may be branch-level or outlet-level depending on module design

### Indexing rules

- All foreign keys: indexed
- Columns used in `WHERE` / `ORDER BY` for frequent queries: indexed
- Add composite indexes for common scope queries like `['cabang_id', 'created_at']` or `['outlet_id', 'created_at']`
- Never index boolean columns alone

---

## Pagination Rules

### Default: cursor-based, 10 per page

```php
Bahan::query()
    ->with(['cabang', 'outlet'])
    ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('nama', 'like', "%{$s}%"))
    ->latest()
    ->cursorPaginate(10);
```

### Bulk: always chunk — never `::all()`

```php
Bahan::chunk(500, function ($items) {
    foreach ($items as $item) {
        ProcessBahan::dispatch($item);
    }
});
```

### Exports: queued job, never in HTTP request

```php
GenerateBahanExport::dispatch($filters, auth()->user());
return back()->with('success', 'Export sedang diproses. Anda akan mendapat notifikasi.');
```

---

## Queue Rules

Heavy operations must always be queued: emails, exports, reports, recalculation, bulk ops.

---

## Shared UI Component Library

**Always use — never recreate:**

| Component           | Usage                                          |
| ------------------- | ---------------------------------------------- |
| `DataTable.vue`     | All paginated data tables                      |
| `DatePicker.vue`    | All date inputs (wraps @vuepic/vue-datepicker) |
| `CurrencyInput.vue` | All Rupiah inputs                              |
| `AsyncSelect.vue`   | Searchable select with server data             |
| `SignaturePad.vue`  | All digital signature fields                   |
| `FileUpload.vue`    | All file upload inputs                         |

---

## Stock Quantity Rules

- Store in **base unit** in DB — always integer
- Display as **major + minor**: `20 Krtn + 3 Pack`
- No conversion: `203 Pack`
- Signed difference: `+1 Krtn + 2 Pack` or `-4 Pack`
- Inputs: integer only, no negatives on entry forms
- Backend must validate and normalize to base unit before persisting
- Conversion source: `product_unit_conversions` table
- Unit label in UI/detail must use **nama satuan** (e.g. `Pcs`), never kode satuan (e.g. `PCS`)

---

## Data Bahan Rules

### Scope

- Data bahan must support use across multiple cabang
- One cabang can have many outlet
- Bahan can be shared at cabang level or assigned specifically to outlet depending on the module requirement
- Stock calculations must always use the correct scope source

### Minimal recommended columns for `bahan`

```php
Schema::create('bahan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cabang_id')->constrained('cabang')->cascadeOnDelete();
    $table->foreignId('outlet_id')->nullable()->constrained('outlet')->nullOnDelete();
    $table->foreignId('kategori_id')->constrained('kategori_bahan');
    $table->foreignId('satuan_id')->constrained('satuan');
    $table->string('nama');
    $table->string('kode')->nullable();
    $table->integer('stok_minimum')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->index(['cabang_id', 'created_at']);
    $table->index(['outlet_id', 'created_at']);
});
```

### Validation principle

- `outlet_id` must belong to the selected `cabang_id`
- user must only create/update data inside their allowed scope
- duplicate material codes must be validated according to the agreed scope policy

---

## UX & Frontend Rules

| Rule            | Requirement                                       |
| --------------- | ------------------------------------------------- |
| Search debounce | 300–500ms via `useDebounceFn` from VueUse         |
| Loading state   | Skeleton UI — never blank, never spinner-only     |
| Empty state     | Always show `"Tidak ada data"` — never blank UI   |
| User feedback   | Toast on every action: success / error / warning  |
| Pagination      | Cursor-based via `DataTable.vue`                  |
| Virtual scroll  | Required for 100+ rows                            |
| Keyboard nav    | AsyncSelect / Dropdown must support arrow + Enter |
| No v-html       | Never render user content as raw HTML             |

---

## Route Rules

Routes map only — no closures, no queries, no business logic.

```php
Route::middleware(['auth', 'verified', 'throttle:60,1'])
    ->group(function () {
        Route::resource('bahan', BahanController::class);
        Route::resource('cabang', CabangController::class);
        Route::resource('outlet', OutletController::class);
        Route::resource('users', UserController::class);
    });

Route::middleware(['auth'])
    ->prefix('search')->name('search.')
    ->group(function () {
        Route::get('bahan', [SearchController::class, 'bahan'])->name('bahan');
        Route::get('outlet', [SearchController::class, 'outlet'])->name('outlet');
    });
```

---

## AsyncSelect Exception

Axios is allowed **only** for `/search/` endpoints powering `AsyncSelect.vue`.

---

## Environment Variable Rule

```php
$key = config('services.payment.secret');
```

Never hardcode credentials, internal URLs, or IDs.

---

## Summary: What AI Must Always Do

1. **Read this entire file** before generating any code
2. **Never exceed 300 lines** per Vue file — plan the split before writing
3. **Follow `<script setup>` order**: imports → props → composables → state → computed → methods → watchers → lifecycle
4. **Always type `defineProps`** with types and defaults
5. **Never axios/fetch** for page data — Inertia router only
6. **Never `$request->all()`** — always `$request->validated()`
7. **Never `DB::table()`** — Eloquent only
8. **Never inline validation** — Form Request classes only
9. **Never role checks in controllers/components** — Policies and Gates only
10. **Never `v-html`** on user-generated content
11. **Never hardcode** credentials, IDs, or URLs
12. **Never raw SQL concatenation** — always Eloquent bindings
13. **Always scope data** correctly by `cabang_id` and `outlet_id` when applicable
14. **Always eager load** — zero N+1 tolerance
15. **Always queue** emails, exports, reports, bulk operations
16. **Always use** `Components/UI/` shared components — never recreate
17. **Always use** Indonesian locale: date `dd/MM/yyyy`, currency IDR integer
18. **Always show** skeleton loading, empty state, and toast feedback
19. **Always set** `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true` for production
20. **Always validate** file uploads with mime type allowlist and UUID filename
21. **Always follow fixed role model**: `owner`, `admin`, `karyawan`
22. **Always treat this project as non-SaaS** with multi-cabang and multi-outlet structure
23. **Always display unit as unit name** (nama satuan), never unit code, in table/modal/detail views
