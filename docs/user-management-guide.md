# User Management & Permission Assignment Guide

> Panduan lengkap untuk manage users, assign roles, dan customize permissions di sistem Kastra ERP.

---

## 📋 Daftar Isi

1. [User Management Overview](#user-management-overview)
2. [Membuat User Baru](#membuat-user-baru)
3. [Mengedit User](#mengedit-user)
4. [Assign Roles ke User](#assign-roles-ke-user)
5. [Custom Permissions](#custom-permissions)
6. [Access Control Scope](#access-control-scope)
7. [User Status & Aktivasi](#user-status--aktivasi)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## 👥 User Management Overview

### User Hierarchy

```
Owner
├── Full access semua cabang, outlet, features
└── Bisa manage users, roles, permissions

Admin
├── Assigned ke specific cabang/outlet(s)
├── Full access ke assigned cabang/outlet
└── Tidak bisa manage users/roles (depends on permission)

Karyawan (Employees)
├── Assigned ke specific outlet
├── Limited operational access saja
└── Tidak bisa access admin features
```

### User Information

Setiap user memiliki:

- **Name** - Nama lengkap
- **Email** - Email unik (login identifier)
- **Company** - Perusahaan (always current company)
- **Cabang** - Cabang assignment
- **Outlet** - Outlet assignment (optional)
- **Roles** - Assigned role(s)
- **Status** - Active/Inactive
- **Created Date** - Tanggal dibuat

---

## ✨ Membuat User Baru

### Step 1: Buka Users Page

1. Login sebagai admin/owner
2. Buka menu **Pengaturan → User**
3. Klik tombol **Tambah User**

### Step 2: Isi Basic Information

```
Nama Lengkap:
  Input: "Budi Santoso"
  Requirement: Required, min 3 char, max 255 char

Email:
  Input: "budi.santoso@company.com"
  Requirement: Required, valid email format, unique
  Note: Email digunakan untuk login

Password:
  Input: "SecurePass123!@#"
  Requirement: Min 8 char, contain uppercase, lowercase, number, symbol
  Note: Share via secure channel kepada user

Telepon (Optional):
  Input: "082123456789"
  Format: Nomor telepon valid

Catatan (Optional):
  Input: "Kasir outlet Medan"
  Note: Internal notes about user role/responsibility
```

### Step 3: Assign Company & Branch/Outlet

```
Perusahaan:
  Dropdown: Select "PT. Contoh Company"
  Note: Always current company, auto-selected

Cabang:
  Dropdown: Select "Cabang Jakarta"
  Requirement: Required
  Note: User bisa diassign ke 1 cabang

Outlet:
  Dropdown: Select "Outlet Sudirman"
  Requirement: Optional
  Note: Depends on role requirements
  - If user only needs branch-level access: biarkan kosong
  - If user needs outlet-level access: assign to specific outlet

Contoh:
  ✅ Manager Gudang: Cabang = Jakarta, Outlet = (kosong)
     → Access semua outlet di Jakarta

  ✅ Kasir: Cabang = Jakarta, Outlet = Sudirman
     → Access hanya outlet Sudirman
```

### Step 4: Assign Role

```
Pilih Role:
  Dropdown: Select "cashier" atau role lainnya
  Note: User inherit semua permissions dari role ini

Jika perlu multiple roles:
  (Ini bisa di-check di advanced setting jika system support)

Contoh:
  User: John
  Role: cashier
  → John punya akses: penjualan.view, penjualan.create, stok.view, dll
```

### Step 5: Review & Simpan

1. Review semua informasi:

   ```
   ✅ Nama: Budi Santoso
   ✅ Email: budi.santoso@company.com
   ✅ Perusahaan: PT. Contoh
   ✅ Cabang: Jakarta
   ✅ Outlet: Sudirman
   ✅ Role: cashier
   ```

2. Klik **Buat User** untuk simpan

3. **Sukses!** User baru sudah dibuat dan siap digunakan

4. Share credential dengan user:
   - Email: budi.santoso@company.com
   - Password: (share via secure channel, bukan email!)
   - First login: user akan diminta ubah password

---

## ✏️ Mengedit User

### Langkah-langkah

1. Di Users page, cari user yang ingin diedit
2. Klik row user atau tombol **Edit**
3. Ubah informasi yang perlu (nama, email, cabang, outlet, role)
4. Klik **Simpan Perubahan**

### Yang Bisa Diubah

| Field      | Bisa Diubah  | Catatan                              |
| ---------- | ------------ | ------------------------------------ |
| Nama       | ✅ Ya        | Bisa diubah kapan saja               |
| Email      | ⚠️ Hati-hati | Gunakan untuk login, pastikan unique |
| Password   | ✅ Ya        | Admin bisa reset password user       |
| Perusahaan | ❌ Tidak     | Tetap di company yang sama           |
| Cabang     | ✅ Ya        | Bisa ubah assignment                 |
| Outlet     | ✅ Ya        | Bisa ubah assignment                 |
| Role       | ✅ Ya        | Permissions berubah langsung         |
| Status     | ✅ Ya        | Deactivate/activate user             |

### Contoh Use Cases

**Use Case 1: Promosi Karyawan**

```
Sebelum:
  Name: Ahmad Riza
  Role: cashier
  Outlet: Sudirman

Sesudah (setelah promosi):
  Name: Ahmad Riza
  Role: supervisor
  Outlet: Sudirman (or Cabang level)

→ Ahmad sekarang punya akses lebih (supervisor permissions)
```

**Use Case 2: Reassign Outlet**

```
Sebelum:
  Name: Siti Nurhaliza
  Outlet: Medan

Sesudah (transfer outlet):
  Name: Siti Nurhaliza
  Outlet: Jakarta

→ Siti sekarang kerja di outlet Jakarta, permissions tetap same (kasir)
```

---

## 🔑 Assign Roles ke User

### Single Role Assignment

```
User dapat memiliki 1 primary role.

Akses: Users → Edit User → Role dropdown → Select role → Save

Contoh:
  John → cashier (punya akses penjualan, stok)
  Mary → warehouse_manager (punya akses inventory, pembelian)
  David → accountant (punya akses accounting, laporan)
```

### Multiple Roles (Advanced)

```
Jika business requirement perlu multiple roles:
  1. Use custom permissions (lihat section Custom Permissions)
  2. Override dengan set specific permissions

Contoh use case:
  Admin Cabang perlu:
    - Admin permissions (user management)
    - Warehouse permissions (inventory)
  Solution: Assign permissions manually atau buat custom role
```

### Role Hierarchy

```
Owner
  ↓ (can assign)
Admin
  ↓ (can assign)
Karyawan
  ↓ (cannot assign)
(User tidak bisa assign role, permission limited)

Aturan:
- Owner bisa assign any role ke any user
- Admin bisa assign roles (depends on permission: users.assign_role)
- Karyawan tidak bisa assign role
```

---

## 🎯 Custom Permissions

### Scenario

```
Business requirement:
  - User "Eka" perlu akses: penjualan.create, stok.view, laporan.view
  - Tidak ada role yang match exactly

Solution: Override dengan custom permissions
```

### Cara 1: Create Custom Role (Recommended)

```
1. Buka Roles → Tambah Role
2. Nama: "eka_custom" atau "supervisor_outlet"
3. Select specific permissions yang diperlukan:
   - penjualan.view
   - penjualan.create
   - stok.view
   - laporan.view
4. Save role
5. Assign role ke user

Advantage:
  ✅ Reusable untuk users lain dengan kebutuhan sama
  ✅ Easy to maintain & audit
  ✅ Clear documentation
```

### Cara 2: Direct Permission Override (If Available)

```
Jika system support direct permission assignment:

1. User → Edit → Advanced tab
2. Override permissions:
   - Remove: stok.adjust (tidak perlu)
   - Add: laporan.export (perlu tambahan)
3. Save

Advantage:
  ✅ One-time customization

Disadvantage:
  ❌ Not reusable
  ❌ Hard to maintain
  ❌ Becomes inconsistent
```

### Best Practice

✅ **DO**: Create custom role jika banyak users dengan kebutuhan sama
❌ **DON'T**: Manually override permissions untuk setiap user (not scalable)

---

## 🗺️ Access Control Scope

### Company & Branch/Outlet Scope

```
Access scope ditentukan oleh company & branch/outlet assignment:

Level 1: Company
  └── semua users di company yang sama bisa see each other
  └── data tidak bisa cross-company

Level 2: Branch (Cabang)
  └── Kasir di cabang Jakarta tidak bisa see data cabang Medan
  └── Manager Jakarta tidak bisa manage outlet di Medan

Level 3: Outlet
  └── Kasir outlet Sudirman hanya bisa process transaksi Sudirman
  └── Stock view hanya lihat stock outlet Sudirman
```

### Scope Rules

```
Owner:
  Company: Can access all
  Branch: Can access all
  Outlet: Can access all

Admin (Supervisor):
  Company: Assigned company only
  Branch: Assigned branch(es) only
  Outlet: All outlets di assigned branch (or specific if assigned)

Karyawan (Employee):
  Company: Assigned company only
  Branch: Assigned branch only
  Outlet: Assigned outlet only
```

### Example Access Patterns

```
Scenario 1: Branch Manager (Jakarta)
  Company: PT. Contoh Company
  Branch: Jakarta
  Outlet: (empty/all)
  Permissions: inventory.*, laporan.view, users.view (branch level)

  Can Access:
    ✅ All outlets di Jakarta
    ✅ All products di Jakarta
    ✅ Cabang laporan Jakarta

  Cannot Access:
    ❌ Outlet di Medan
    ❌ Outlet di Bandung
    ❌ Users management (depends on permission)


Scenario 2: Cashier (Outlet Sudirman)
  Company: PT. Contoh Company
  Branch: Jakarta
  Outlet: Sudirman
  Permissions: penjualan.*, stok.view, laporan.view

  Can Access:
    ✅ Penjualan untuk outlet Sudirman only
    ✅ View stok outlet Sudirman
    ✅ View laporan outlet Sudirman

  Cannot Access:
    ❌ Outlet Jakarta Pusat
    ❌ Outlets di cabang lain
    ❌ Inventory adjustment (requires permission)
```

### Scope Enforcement

```
Backend (MUST enforce - security critical):
  - Query filtering by company_id, branch_id, outlet_id
  - Policy checks untuk scope authorization
  - Always validate user's assigned scope

Frontend (UI only - helps user experience):
  - Dropdown hanya show cabang/outlet yang user bisa access
  - Menu items filtered berdasarkan permission
  - Breadcrumb show current scope context

⚠️ Frontend filtering TIDAK ENOUGH - backend harus validate!
```

---

## 🟢 User Status & Aktivasi

### User Status

```
Active (🟢 Hijau):
  - User dapat login
  - User dapat mengakses sistem
  - Permissions berlaku normal

Inactive (🔴 Merah):
  - User TIDAK dapat login
  - Existing session tetap aktif (akan logout otomatis sesuai session timeout)
  - Berguna untuk: cuti, resign, suspended (temp)
```

### Deactivate User

```
Kapan:
  - User cuti panjang
  - User resign
  - User suspended sementara
  - User lupa password (temporary deactivate)

Cara:
  1. Users page → Edit user
  2. Toggle "Status" menjadi Inactive
  3. Save

User akan:
  ❌ Tidak bisa login baru
  ✅ Session existing tetap jalan sampai timeout/logout
```

### Reactivate User

```
Kapan:
  - User kembali dari cuti
  - User diterima kembali
  - User ended suspension

Cara:
  1. Users page → Edit user
  2. Toggle "Status" menjadi Active
  3. Save

User sekarang:
  ✅ Bisa login ulang
  ✅ Permissions restored
```

### Reset Password

```
Saat:
  - User lupa password
  - User minta password reset
  - User account compromised

Cara:
  1. Users page → Edit user
  2. Click "Reset Password" button
  3. System generate temporary password
  4. Share via secure channel (SMS, encrypted email)
  5. User login dengan temporary password
  6. User prompted to change password on first login

⚠️ JANGAN share temporary password via email atau chat biasa!
```

---

## 📚 Best Practices

### 1. Security Best Practices

```
✅ DO:
  - Use strong password requirements
  - Require password change on first login
  - Reset password via secure channel
  - Deactivate inactive users (90+ days no login)
  - Regular audit of access permissions
  - Monitor admin actions in audit log

❌ DON'T:
  - Share password via email/chat
  - Use same password untuk multiple users
  - Store password dalam documents
  - Give admin access to all karyawan
  - Forget to deactivate resigned employees
```

### 2. Access Scope Best Practices

```
✅ DO:
  - Assign users ke specific branch/outlet
  - Use permissions untuk fine-grained control
  - Document access decisions
  - Regular review user access (quarterly)

❌ DON'T:
  - Give user access ke all branches/outlets "just in case"
  - Mix up company/branch/outlet scope
  - Forget to remove access when user leaves
  - Use shared accounts (1 account = 1 person)
```

### 3. Role Assignment Best Practices

```
✅ DO:
  - Use predefined roles (cashier, manager, etc)
  - Create role untuk common positions
  - Document why user has certain role
  - Review role appropriateness during promotion/transfer

❌ DON'T:
  - Create too many custom roles (complexity)
  - Assign multiple roles to same user (confusing)
  - Give highest privilege role by default
  - Forget to update role when user responsibility changes
```

### 4. Onboarding Checklist

```
New User Onboarding:
  □ Create user account
  □ Set strong temporary password
  □ Assign to correct company/branch/outlet
  □ Assign appropriate role
  □ Test user can login
  □ Verify user can access correct modules
  □ Train user on system
  □ Document access decision

Offboarding Checklist:
  □ Deactivate user
  □ Remove any special permissions
  □ Reassign user's tasks/responsibilities
  □ Update documentation
  □ Archive user's data (if applicable)
  □ Remove from communication groups
```

---

## 🆘 Troubleshooting

### Q: User tidak bisa login

**Kemungkinan penyebab & solusi:**

1. **Status Inactive**
   - Check Users → Edit user
   - Pastikan Status = Active
   - Save dan minta user login ulang

2. **Email tidak cocok**
   - User login dengan email yang benar?
   - Case-sensitive? (biasanya tidak)
   - Email ada typo?

3. **Password salah**
   - Minta user reset password
   - Admin: reset user password (generate temporary)
   - User login dengan temporary password
   - User prompted change password

4. **Network/Browser issue**
   - Clear browser cache: Ctrl+Shift+Delete
   - Try incognito/private window
   - Try different browser
   - Check internet connection

### Q: User login sukses tapi tidak bisa akses modul tertentu

**Kemungkinan penyebab & solusi:**

1. **Permission tidak ada**
   - Check role yang diassign
   - Open Roles → check permissions
   - Add permission yang diperlukan ke role
   - User bisa refresh browser atau logout/login

2. **Cache outdated**
   - Clear cache: php artisan cache:clear
   - Or wait 5 minutes untuk automatic cache refresh
   - User logout/login untuk refresh permission cache

3. **Scope issue**
   - User assigned ke correct branch/outlet?
   - Check Users → Edit user → check Cabang/Outlet
   - Module mungkin require outlet assignment

4. **Feature disabled**
   - Admin check apakah module enabled untuk company
   - Check company settings

### Q: User bisa akses data cabang lain (Scope leak)

**SECURITY ISSUE - Investigate immediately:**

1. Verifikasi user's assigned scope

   ```
   Users → Edit user → check Cabang & Outlet
   ```

2. Check user's roles & permissions

   ```
   Roles → check permission ini scope-aware?
   ```

3. Check database queries

   ```
   Verify query include: WHERE cabang_id = ?
   Verify policy check scope
   ```

4. Check backend code
   ```
   Ensure no hardcoded company/branch/outlet
   Ensure all queries filtered by user's scope
   ```

### Q: Terlalu banyak users dengan high privilege

**Solusi:**

1. Audit: List semua users dengan admin/owner role
2. Verify apakah semua benar-benar perlu privilege tinggi
3. Downgrade ke role yang lebih limited jika tidak perlu
4. Create specific roles untuk job functions
5. Review quarterly untuk keep access up-to-date

### Q: Lupa mana user yang punya role tertentu

**Solusi:**

1. Roles page → Click role name
2. Lihat "Users with this role" section
3. Atau: Users page → Filter by role (jika ada filter)
4. Keep documentation/spreadsheet user assignments

### Q: Need bulk action (add role ke multiple users)

**Solusi (depends on system capability):**

1. **Option A: Manual (if no bulk edit)**
   - Edit each user individually
   - Add role
   - Save

2. **Option B: Bulk import (if system supports)**
   - Export current users CSV
   - Add/modify role column
   - Upload CSV import

3. **Option C: Database (if you know SQL)**
   - Direct update: UPDATE users SET role_id = ? WHERE ...
   - Run audit after: verify permissions correctly assigned

⚠️ Use Option B/C carefully - test dengan small dataset first!

---

## 📞 Support & Documentation

### Related Guides

- [Role Management Guide](./role-management-guide.md)
- [Access Control Implementation](./access-control-implementation.md)
- [Quick Start Guide](./access-control-quick-start.md)

### Audit Trail

Keep track of user management actions:

- User created: Who, when, what role
- User edited: What changed, who changed, when
- User deactivated: Why, when
- Permission assigned/removed: What, when, who

### Emergency Procedures

**If Owner account compromised:**

1. Deactivate compromised account immediately
2. Create new owner account via database (if needed)
3. Change all sensitive credentials
4. Audit all changes made while compromised
5. Restore from backup if needed

**If too many users deactivated by mistake:**

1. Don't panic - data is safe
2. Bulk reactivate: edit each user or use bulk action
3. Verify permissions are correct
4. Notify affected users

---

## Summary Checklist

- [ ] Understand user hierarchy (owner → admin → karyawan)
- [ ] Know role assignment impact (immediate permission change)
- [ ] Understand scope (company → branch → outlet)
- [ ] Use predefined roles when possible
- [ ] Create custom role untuk common need (not per-user)
- [ ] Document access decisions
- [ ] Regular audit user access (quarterly)
- [ ] Deactivate users yang sudah tidak digunakan
- [ ] Test new user access sebelum go-live
- [ ] Keep audit log of user management actions
