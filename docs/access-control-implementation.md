# Management Akses User & Menu Berdasarkan Role - Dokumentasi Komprehensif

> **Senior Programmer Implementation** - Sistem access control yang proper, scalable, dan maintainable mengikuti AGENTS.md guidelines.

---

## 📋 Arsitektur Sistem

### Komponen Utama

```
┌─────────────────────────────────────────────────────────────┐
│ Frontend (Vue 3 + Inertia)                                  │
├─────────────────────────────────────────────────────────────┤
│ • useAccessControl() - Composable untuk permission checks   │
│ • MenuService (JS) - Filter menu berdasarkan role/permission│
│ • Reusable Components (PermissionGrid, RoleSelector, etc.)  │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│ Middleware (HandleInertiaRequests)                          │
├─────────────────────────────────────────────────────────────┤
│ • Share auth.permissions & auth.roles ke frontend           │
│ • Share filtered menus berdasarkan user permissions         │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│ Backend Services                                            │
├─────────────────────────────────────────────────────────────┤
│ • InertiaAuthorizationService - Cache role/permission       │
│ • MenuService - Centralized menu configuration & filtering  │
│ • RbacService - Bootstrap role/permission                   │
│ • UserService - User management dengan role sync            │
│ • RoleService - Role management dengan permission sync      │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│ Authorization Layer (Policies & Gates)                      │
├─────────────────────────────────────────────────────────────┤
│ • UserPolicy - User create/edit/delete/assign               │
│ • RolePolicy - Role create/edit/delete                      │
│ • PermissionPolicy - Permission view/create/edit/delete     │
│ • Gates - Custom authorization checks                       │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│ Database (Spatie Permission)                                │
├─────────────────────────────────────────────────────────────┤
│ • roles - store role definitions per company                │
│ • permissions - store permission definitions                │
│ • model_has_roles - assign role to user                     │
│ • model_has_permissions - assign permission direct to user  │
│ • role_has_permissions - assign permission to role          │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Role Structure

Sistem menggunakan **3 role tetap** (fixed roles):

```php
$roles = ['owner', 'admin', 'karyawan'];
```

### Deskripsi Role

| Role       | Akses                                   | Use Case               |
| ---------- | --------------------------------------- | ---------------------- |
| `owner`    | Semua permissions, bypass checks        | Pemilik bisnis/founder |
| `admin`    | Semua permissions (sesuai konfigurasi)  | Admin/manager          |
| `karyawan` | Limited operational (lihat permissions) | Staff/operator         |

### Permission Assignment

Permissions di-assign ke Role melalui:

```php
// RbacService::syncRolePermissions()
$role->syncPermissions($permissions);
```

Detail permission per role di-define di `RbacService::adminPermissions()` dan `RbacService::karyawanPermissions()`.

---

## 🔐 Permission System

### Permission Naming Convention

```
{module}.{action}

Contoh:
- users.view
- users.create
- users.edit
- users.delete
- users.assign_role
- users.assign_permission

- products.view
- products.create
- products.edit
- products.delete
- products.images
```

### Permission Catalog

Semua permissions terdaftar di `RbacService::permissionCatalog()`:

```php
'users.view', 'users.create', 'users.edit', 'users.delete',
'users.assign_role', 'users.assign_permission',
'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
'products.view', 'products.create', 'products.edit', 'products.delete',
// ... dll
```

### Permission Grouping (UI)

Permissions di-group di database untuk UI rendering:

```
group: 'Produk'
  subgroup: 'Produk'
    permissions: [products.view, products.create, products.edit, products.delete]
  subgroup: 'Kategori'
    permissions: [product_categories.view, product_categories.create, ...]
  subgroup: 'Brand'
    permissions: [product_brands.view, product_brands.create, ...]
  subgroup: 'Satuan'
    permissions: [units.view, units.create, ...]

group: 'Pengguna'
  subgroup: 'Pengguna'
    permissions: [users.view, users.create, users.edit, users.delete, ...]
```

Grouping di-maintain di `PermissionGroupSeeder`.

---

## 🛣️ Menu System

### MenuService - Centralized Configuration

Menu structure **didefinisikan sekali** di `MenuService::MENUS`:

```php
[
    [
        'id' => 'dashboard',
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'LayoutDashboard',
        'permission' => null, // Semua orang bisa lihat
    ],
    [
        'id' => 'master-data',
        'label' => 'Data Master',
        'section' => true,
        'items' => [
            [
                'id' => 'products',
                'label' => 'Produk',
                'route' => 'products.index',
                'icon' => 'Package',
                'permission' => 'products.view',
                'children' => [
                    // submenu items
                ]
            ],
            // ... other items
        ]
    ],
    // ... other sections
]
```

### Menu Filtering

Frontend hanya menerima menu yang **sudah di-filter** berdasarkan permissions:

```php
// Backend (HandleInertiaRequests)
$menus = app(MenuService::class)->forUser($user, $permissions, $roles);
// return [filtered menus only]

// Frontend (Inertia props)
page.props.menus = [filtered menus]
```

### Menu Structure

```php
// Section (group)
[
    'section' => true,
    'label' => 'Transaksi',
    'items' => [
        // items di dalam section
    ]
]

// Item dengan children (submenu)
[
    'id' => 'products',
    'label' => 'Produk',
    'route' => 'products.index',
    'permission' => 'products.view',
    'children' => [
        ['label' => 'Kategori', 'route' => 'products.master.index', 'params' => ['product_categories']],
        ['label' => 'Brand', 'route' => 'products.master.index', 'params' => ['product_brands']],
    ]
]
```

---

## 🎨 Frontend Implementation

### useAccessControl() Composable

```javascript
import { useAccessControl } from '@/Composables/useAccessControl'

export default {
  setup() {
    const { can, permissions, roles, menus } = useAccessControl()

    return {
      can, // can(permission) - boolean
      permissions, // list of user permissions
      roles, // list of user roles
      menus, // filtered menu items
    }
  },
}
```

### Permission Checks di Components

```vue
<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'
const { can, canAny, canAll } = useAccessControl()
</script>

<template>
  <!-- Single permission check -->
  <button v-if="can('users.create')">Tambah User</button>

  <!-- Any of multiple permissions -->
  <button v-if="canAny(['users.edit', 'users.create'])">Edit/Buat</button>

  <!-- All permissions required -->
  <button v-if="canAll(['users.view', 'users.delete'])">View & Delete</button>

  <!-- No permission required = always visible -->
  <button v-if="can(null)">Always visible</button>
</template>
```

### Menu Usage

```vue
<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'
const { menus } = useAccessControl()
</script>

<template>
  <!-- Render filtered menus -->
  <nav>
    <template v-for="menu in menus" :key="menu.id">
      <section v-if="menu.section">
        <h3>{{ menu.label }}</h3>
        <template v-for="item in menu.items" :key="item.id">
          <Link :href="route(item.route)">{{ item.label }}</Link>
        </template>
      </section>
    </template>
  </nav>
</template>
```

### Reusable Components

#### PermissionGrid.vue

```vue
<PermissionGrid
  :permissions="allPermissions"
  :selected-permissions="user.permissions"
  @update:selected="updateUserPermissions"
/>
```

#### RoleSelector.vue

```vue
<RoleSelector v-model="selectedRole" :roles="availableRoles" label="Pilih Role" />
```

#### PermissionBadges.vue

```vue
<PermissionBadges :permissions="user.permissions" :max="3" size="md" />
```

---

## 🔧 Backend Implementation

### Middleware: HandleInertiaRequests

```php
public function share(Request $request): array
{
    $user = $request->user();
    $authorization = $user
        ? app(InertiaAuthorizationService::class)->for($user)
        : ['roles' => [], 'permissions' => []];

    $menus = $user && app()->has(MenuService::class)
        ? app(MenuService::class)->forUser($user, $authorization['permissions'], $authorization['roles'])
        : [];

    return [
        ...parent::share($request),
        'auth' => [
            'user' => $user?->only('id', 'name', 'email', 'company_id'),
            'roles' => $authorization['roles'],
            'permissions' => $authorization['permissions'],
        ],
        'menus' => fn () => $menus,
        // ... other shared props
    ];
}
```

### Authorization: Policies

```php
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('users.edit')
            && $user->company_id === $target->company_id;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isNot($target)
            && $user->can('users.delete')
            && $user->company_id === $target->company_id;
    }
}
```

### Controllers - Permission Checks

```php
class UserController extends Controller
{
    public function index(IndexUserRequest $request): Response
    {
        // Middleware + FormRequest sudah handle authorization
        $this->authorize('viewAny', User::class);

        return Inertia::render('Users/Index', [
            'users' => $this->repository->paginate(...),
            'roles' => $this->repository->roles(...),
            'permissions' => $this->repository->permissions(),
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $this->service->delete($user);
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
```

### Services

```php
class UserService
{
    public function create(string $companyId, array $data): User
    {
        return DB::transaction(function () use ($companyId, $data) {
            $user = User::create([
                ...Arr::except($data, 'role'),
                'company_id' => $companyId,
                'onboarding_completed_at' => now(),
            ]);
            $user->assignRole($this->role($companyId, $data['role']));
            return $user;
        });
    }

    public function syncPermissions(User $user, array $permissions): void
    {
        $user->syncPermissions($permissions);
    }
}
```

---

## 🚀 Setup & Configuration

### 1. Bootstrap Permissions & Roles

```bash
php artisan db:seed --class=PermissionGroupSeeder
```

Ini akan:

- Create semua permissions dari `RbacService::permissionCatalog()`
- Create 3 role: `owner`, `admin`, `karyawan`
- Assign permissions ke masing-masing role
- Update grouping di database

### 2. Register MenuService Provider

MenuService sudah otomatis available via service container.

### 3. Update Routes

Routes untuk user/role/permission management sudah ada:

```php
Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('permissions', PermissionController::class)->only(['index', 'store']);
```

---

## ✅ Checklist: Implementing Access Control

- [x] MenuService centralized configuration
- [x] useAccessControl() composable di Vue
- [x] HandleInertiaRequests share menu & permissions
- [x] Reusable UI components (PermissionGrid, RoleSelector, PermissionBadges)
- [x] Updated PermissionController dengan search & grouped endpoints
- [x] Policies untuk User, Role, Permission
- [x] UserService dengan role & permission sync
- [x] RbacService bootstrap permissions
- [x] PermissionGroupSeeder untuk grouping
- [x] Routes untuk management

### Untuk Implementasi Selanjutnya:

- [ ] Update AppSidebar.vue untuk use MenuService
- [ ] Create/Update User Pages (Index, Create, Edit)
- [ ] Create/Update Role Pages (Index, Create, Edit)
- [ ] Create/Update Permission Pages
- [ ] Add breadcrumb generation from MenuService
- [ ] Add route-level middleware untuk authorization
- [ ] Add tests untuk permissions & policies
- [ ] Add audit logging untuk user actions

---

## 🎓 Best Practices

### 1. Always Check Authorization in Controllers

```php
$this->authorize('update', $user);
```

### 2. Use Policies, Never Hardcode Role Checks

```php
// ✅ Good
if ($user->can('users.edit')) { ... }
$this->authorize('update', $user);

// ❌ Bad
if ($user->hasRole('admin')) { ... }
if ($user->role === 'owner') { ... }
```

### 3. Frontend Permission Checks are UI Only

```vue
<!-- Ini hanya UI, backend HARUS check juga -->
<button v-if="can('users.delete')">Delete</button>
```

### 4. Cache adalah Key untuk Performance

- Permissions di-cache di `InertiaAuthorizationService` (300 detik)
- Menus di-compute di middleware (per-request, tapi cache permission dulu)
- Jangan compute permissions di setiap component

### 5. Use MenuService untuk Consistency

Jangan hardcode menu di komponen. Selalu gunakan MenuService yang sudah di-share via props.

### 6. Company Scoping

Semua data user/role/permission harus scoped ke company:

```php
// ✅ Good
User::where('company_id', $companyId)
    ->where('id', $userId)
    ->first();

// ❌ Bad
User::find($userId);
```

---

## 📊 Example: Create New User dengan Role & Permission

### Backend

```php
// UserController::store()
public function store(StoreUserRequest $request): RedirectResponse
{
    $this->authorize('create', User::class);

    $this->service->create(
        $this->companyContext->id(),
        $request->validated()
    );

    return redirect()->route('users.index')
        ->with('success', 'Pengguna berhasil ditambahkan.');
}

// UserService::create()
public function create(string $companyId, array $data): User
{
    return DB::transaction(function () use ($companyId, $data) {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'company_id' => $companyId,
            'onboarding_completed_at' => now(),
        ]);

        $user->assignRole(
            Role::where('company_id', $companyId)
                ->where('name', $data['role'])
                ->firstOrFail()
        );

        return $user->refresh();
    });
}
```

### Frontend

```vue
<script setup>
import { router, useForm } from '@inertiajs/vue3'
import RoleSelector from '@/Components/UI/RoleSelector.vue'

const props = defineProps({
  roles: { type: Array, required: true },
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
})

const submit = () => {
  form.post(route('users.store'), {
    onSuccess: () => {
      // Success flash message handled by middleware
    },
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <Input v-model="form.name" label="Nama" />
    <Input v-model="form.email" label="Email" type="email" />
    <Input v-model="form.password" label="Password" type="password" />
    <Input v-model="form.password_confirmation" label="Konfirmasi Password" type="password" />
    <RoleSelector v-model="form.role" :roles="roles" />

    <Button type="submit">Simpan</Button>
  </form>
</template>
```

---

## 🐛 Troubleshooting

### Permission tidak muncul di menu

1. Check `MenuService::MENUS` - permission key sudah benar?
2. Check user role & permission di database
3. Check cache di Redis/File (clear cache: `php artisan cache:clear`)
4. Check browser console untuk props.menus value

### Policy tidak di-trigger

1. Make sure controller menggunakan `$this->authorize()`
2. Check AppServiceProvider untuk Gate::policy() registration
3. Check user company_id vs resource company_id

### Permission assignment tidak bekerja

1. Check `SyncUserPermissionsRequest` validation
2. Check `UserService::syncPermissions()` implementation
3. Ensure permission ID/name sudah terdaftar di database
4. Clear cache setelah update

---

## 📝 Files Created/Modified

### Created

- `app/Services/MenuService.php` - Menu configuration & filtering
- `resources/js/Composables/useAccessControl.js` - Permission & menu composable
- `resources/js/Components/UI/PermissionGrid.vue` - Permission matrix component
- `resources/js/Components/UI/RoleSelector.vue` - Role selector component
- `resources/js/Components/UI/PermissionBadges.vue` - Permission display component
- `resources/js/Components/UI/AccessControlTable.vue` - Table with access control
- `resources/js/Layouts/Components/AppSidebarNew.vue` - New sidebar using MenuService

### Modified

- `app/Http/Middleware/HandleInertiaRequests.php` - Added menus prop sharing
- `app/Http/Controllers/PermissionController.php` - Added search & grouped endpoints

---

## 🔗 Related Files

- `app/Services/InertiaAuthorizationService.php` - Permission caching
- `app/Services/RbacService.php` - Role & permission bootstrap
- `app/Services/UserService.php` - User management
- `app/Services/RoleService.php` - Role management
- `database/seeders/PermissionGroupSeeder.php` - Permission grouping
- `app/Policies/*Policy.php` - Authorization policies
- `routes/web.php` - Route definitions
- `AGENTS.md` - Project guidelines
