# Access Control System - Quick Start Guide

> Panduan cepat untuk developer yang ingin menggunakan sistem access control.

---

## 🚀 Quick Start

### 1. Check Permission di Frontend

```vue
<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'

const { can, canAny, canAll, permissions, roles } = useAccessControl()
</script>

<template>
  <!-- Single permission -->
  <button v-if="can('users.create')">Tambah User</button>

  <!-- Multiple permissions (ANY) -->
  <div v-if="canAny(['users.edit', 'users.create'])">Edit atau Buat User</div>

  <!-- All permissions required -->
  <div v-if="canAll(['users.view', 'users.delete'])">User dapat dihapus</div>

  <!-- Display current permissions -->
  <div>Permissions: {{ permissions }}</div>
</template>
```

### 2. Check Permission di Backend

```php
// Di Controller
public function store(Request $request): RedirectResponse
{
    // Using Policy
    $this->authorize('create', User::class);

    // Or using Gate
    if (!auth()->user()->can('users.create')) {
        abort(403);
    }

    // Your logic here
}
```

### 3. Add New Permission

1. Add ke `RbacService::permissionCatalog()`:

```php
public function permissionCatalog(): array
{
    return [
        // ... existing
        'custom.view',      // ← New permission
        'custom.create',
        'custom.edit',
    ];
}
```

2. Update seeder jika perlu grouping:

```php
// PermissionGroupSeeder
[
    'group' => 'Custom Module',
    'subgroup' => 'Custom',
    'sort' => 99,
    'permissions' => ['custom.view', 'custom.create', 'custom.edit'],
],
```

3. Run seeder:

```bash
php artisan db:seed --class=PermissionGroupSeeder
```

### 4. Add New Menu Item

Edit `MenuService::MENUS` constant:

```php
private const MENUS = [
    // ... existing items
    [
        'id' => 'custom',
        'label' => 'Custom Module',
        'route' => 'custom.index',
        'icon' => 'Package',
        'permission' => 'custom.view', // User harus punya ini untuk lihat menu
        'activePattern' => ['custom.*'],
    ],
];
```

Menu otomatis:

- Ter-filter berdasarkan user permission
- Di-share ke frontend via middleware
- Accessible di komponen via `useAccessControl().menus`

### 5. Create New Role

```php
// Backend
$role = Role::create([
    'company_id' => auth()->user()->company_id,
    'name' => 'supervisor',
    'guard_name' => 'web',
]);

// Assign permissions
$role->syncPermissions([
    'users.view',
    'users.create',
    'users.edit',
    'products.view',
]);
```

Or via UI di Roles page.

### 6. Assign Role ke User

```php
// Via UserService
app(UserService::class)->create($companyId, [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret',
    'role' => 'admin', // Role name
]);

// Or direct
$user->assignRole('admin');
$user->syncRoles(['admin', 'supervisor']);
```

### 7. Assign Permission Direct ke User

```php
// Via UserService
app(UserService::class)->syncPermissions($user, [
    'users.view',
    'users.create',
]);

// Or direct
$user->syncPermissions(['users.view', 'users.create']);
```

---

## 📋 Common Patterns

### Pattern 1: Check Permission + Display Action

```vue
<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'

const { can } = useAccessControl()
</script>

<template>
  <table>
    <tr v-for="user in users" :key="user.id">
      <td>{{ user.name }}</td>
      <td v-if="can('users.edit')">
        <button @click="editUser(user)">Edit</button>
      </td>
      <td v-if="can('users.delete')">
        <button @click="deleteUser(user)">Delete</button>
      </td>
    </tr>
  </table>
</template>
```

### Pattern 2: Use Reusable Components

```vue
<script setup>
import PermissionGrid from '@/Components/UI/PermissionGrid.vue'
import RoleSelector from '@/Components/UI/RoleSelector.vue'

const selectedRole = ref('admin')
const selectedPermissions = ref(['users.view', 'users.create'])
</script>

<template>
  <div>
    <RoleSelector v-model="selectedRole" :roles="roles" />

    <PermissionGrid
      :permissions="allPermissions"
      :selected-permissions="selectedPermissions"
      @update:selected="handlePermissionChange"
    />
  </div>
</template>
```

### Pattern 3: Conditional Navigation

```vue
<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'
import { Link } from '@inertiajs/vue3'

const { menus } = useAccessControl()
</script>

<template>
  <!-- Automatically filtered to show only accessible menus -->
  <nav>
    <Link v-for="menu in menus" :key="menu.id" :href="route(menu.route)">
      {{ menu.label }}
    </Link>
  </nav>
</template>
```

### Pattern 4: Backend Authorization

```php
// 1. Using Policy
public function update(User $user): RedirectResponse
{
    $this->authorize('update', $user);
    // ... update logic
}

// 2. Using Gate
public function destroy(User $user): RedirectResponse
{
    if (!auth()->user()->can('users.delete')) {
        abort(403);
    }
    // ... delete logic
}

// 3. In FormRequest
public function authorize(): bool
{
    return $this->user()->can('users.edit');
}
```

---

## 🔑 Key Concepts

### 1. Permissions vs Roles

- **Permission**: Granular action (e.g., `users.create`)
- **Role**: Group of permissions (e.g., `admin` = all permissions)

### 2. Frontend is UI Only

Frontend permission checks hanya untuk **visibility**. Backend HARUS selalu check juga!

```vue
<!-- Frontend: Show/hide button -->
<button v-if="can('users.delete')">Delete</button>
```

```php
// Backend: Always check, tidak peduli frontend
public function destroy(User $user): RedirectResponse
{
    $this->authorize('delete', $user);
    // User bisa bypass frontend check dengan API call langsung
}
```

### 3. Caching untuk Performance

Permissions di-cache 300 detik di `InertiaAuthorizationService`. Untuk clear cache:

```bash
php artisan cache:clear
```

Atau clear specific key:

```php
Cache::forget('inertia-auth:user:' . $user->id);
```

### 4. Company Scoping

Semua queries HARUS scoped ke company_id:

```php
// ✅ Good
User::where('company_id', auth()->user()->company_id)->find($userId);

// ❌ Bad - bisa access user dari company lain
User::find($userId);
```

---

## 🐛 Debugging

### Debug Permission untuk User

```php
// Di tinker
$user = User::find('user-id');
$perms = app(InertiaAuthorizationService::class)->for($user);
dd($perms);

// Check specific permission
$user->can('users.view'); // true/false
$user->hasRole('admin'); // true/false
```

### Debug Menu Filtering

```php
// Di controller
$menuService = app(MenuService::class);
$menus = $menuService->forUser($user, $perms, $roles);
dd($menus);
```

### Debug Frontend Props

```javascript
// Di console
console.log(usePage().props.auth) // roles & permissions
console.log(usePage().props.menus) // filtered menus
```

---

## ⚠️ Common Mistakes

### ❌ Mistake 1: Hardcode Role Checks

```php
// WRONG
if ($user->hasRole('admin')) {
    // Do something
}

// CORRECT
if ($user->can('users.delete')) {
    // Do something
}
```

### ❌ Mistake 2: Only Check Frontend

```vue
<!-- WRONG - Frontend check only, tidak aman -->
<button v-if="can('users.delete')" @click="deleteUser()">Delete</button>

<!-- CORRECT - Frontend + Backend check -->
<button v-if="can('users.delete')" @click="deleteUser()">Delete</button>
```

```php
// Backend HARUS check juga
public function destroy(User $user): RedirectResponse
{
    $this->authorize('delete', $user); // ← Backend check
    // ... delete logic
}
```

### ❌ Mistake 3: Forget Company Scoping

```php
// WRONG
$user = User::where('email', 'john@example.com')->first();

// CORRECT
$user = User::where('company_id', auth()->user()->company_id)
    ->where('email', 'john@example.com')
    ->first();
```

### ❌ Mistake 4: Permission Name Mismatch

```php
// Define di permissionCatalog
'users.create',

// Tapi di frontend check
can('user.create')  // WRONG - typo!

// CORRECT
can('users.create') // Exact match
```

---

## 📚 File Reference

| File                        | Purpose                        |
| --------------------------- | ------------------------------ |
| `MenuService.php`           | Menu configuration & filtering |
| `useAccessControl.js`       | Vue composable                 |
| `HandleInertiaRequests.php` | Share auth & menus             |
| `UserPolicy.php`            | User authorization             |
| `RolePolicy.php`            | Role authorization             |
| `RbacService.php`           | Bootstrap system               |
| `PermissionGroupSeeder.php` | Permission grouping            |

---

## 🎯 Workflow: Add New Feature dengan Access Control

### Step 1: Create Permission

```php
// RbacService::permissionCatalog()
'reports.view',
'reports.export',
'reports.delete',
```

### Step 2: Group Permission (Optional)

```php
// PermissionGroupSeeder
[
    'group' => 'Laporan',
    'subgroup' => 'Laporan',
    'sort' => 5,
    'permissions' => ['reports.view', 'reports.export', 'reports.delete'],
],
```

Run seeder:

```bash
php artisan db:seed --class=PermissionGroupSeeder
```

### Step 3: Add Menu Item

```php
// MenuService::MENUS
[
    'id' => 'reports',
    'label' => 'Laporan',
    'route' => 'reports.index',
    'icon' => 'BarChart3',
    'permission' => 'reports.view',
],
```

### Step 4: Create Controller + Policy

```php
class ReportController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Report::class);
        // ... show reports
    }
}

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reports.view');
    }
}
```

### Step 5: Create Blade/Vue Views

```vue
<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'

const { can } = useAccessControl()
</script>

<template>
  <table>
    <tr v-for="report in reports" :key="report.id">
      <td>{{ report.name }}</td>
      <td v-if="can('reports.export')">
        <button @click="exportReport(report)">Export</button>
      </td>
    </tr>
  </table>
</template>
```

### Step 6: Assign Permission ke Role

Via UI di Roles page atau via code:

```php
$role = Role::where('name', 'admin')->first();
$role->syncPermissions([
    // ... existing permissions
    'reports.view',
    'reports.export',
]);
```

---

## ✅ Checklist: Implement Feature dengan Access Control

- [ ] Define permissions di `permissionCatalog()`
- [ ] Add grouping di `PermissionGroupSeeder`
- [ ] Run: `php artisan db:seed --class=PermissionGroupSeeder`
- [ ] Add menu di `MenuService::MENUS`
- [ ] Create Policy untuk model
- [ ] Use `$this->authorize()` di controller
- [ ] Use `can()` di frontend (Vue/Blade)
- [ ] Assign permission ke role di Roles page
- [ ] Test dengan user yang punya/tidak punya permission
- [ ] Test dengan user dari different company

---

## 📞 Support & Escalation

### Jika Permission tidak Muncul di Menu

1. Check: Permission ada di `permissionCatalog()`?
2. Check: User punya permission di database?
3. Clear cache: `php artisan cache:clear`
4. Check browser console: `console.log(usePage().props.auth.permissions)`

### Jika Authorization Fail

1. Check: Policy ada di `AppServiceProvider`?
2. Check: User company_id match resource company_id?
3. Check: Permission name spelled correctly?

### Jika Performance Lambat

1. Check: Permissions di-cache? (should be ~300s)
2. Check: N+1 queries di repository?
3. Use: `with('roles')` untuk eager load
