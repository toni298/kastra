# Role & Permission Management Guide

> Panduan lengkap untuk manage roles dan permissions di sistem Kastra ERP.

---

## 📋 Daftar Isi

1. [Konsep Dasar](#konsep-dasar)
2. [Cara Kerja Sistem](#cara-kerja-sistem)
3. [Membuat Role Baru](#membuat-role-baru)
4. [Mengedit Role](#mengedit-role)
5. [Menghapus Role](#menghapus-role)
6. [Best Practices](#best-practices)
7. [Contoh Role Templates](#contoh-role-templates)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Konsep Dasar

### Apa itu Role?

**Role** adalah kumpulan permissions yang dikelompokkan untuk suatu fungsi/posisi tertentu.

Contoh:

- `cashier` = kasir toko
- `warehouse_manager` = manager gudang
- `accountant` = akuntan

### Apa itu Permission?

**Permission** adalah izin untuk melakukan action tertentu.

Format: `{module}.{action}`

Contoh:

- `penjualan.view` = lihat transaksi penjualan
- `penjualan.create` = buat transaksi penjualan baru
- `users.delete` = hapus pengguna

### Role vs Permission

| Aspek     | Role            | Permission                  |
| --------- | --------------- | --------------------------- |
| Level     | Tingkat tinggi  | Granular (detail)           |
| Contoh    | cashier, admin  | penjualan.create, stok.view |
| Jumlah    | Sedikit (~5-10) | Banyak (50+)                |
| Perubahan | Jarang diubah   | Juga jarang diubah          |

---

## 🔄 Cara Kerja Sistem

### 1. User mendapat Role

```
User → assigned to → Role
         (via user management)
```

Contoh: John → assigned to → Kasir

### 2. Role memiliki Permissions

```
Role → has many → Permissions
       (via role management)
```

Contoh: Kasir → penjualan.view, penjualan.create, stok.view, ...

### 3. User inherit Permissions dari Role

```
User → Role → Permissions
         ↓
       Effective Permissions
```

Contoh: John (Kasir) → bisa view/create penjualan, view stok, dll

### 4. Permission Hierarchy

```
Backend:
- Controller checks permission via Policy
  $this->authorize('create', Penjualan::class)
  atau
  if (!auth()->user()->can('penjualan.create')) abort(403)

Frontend:
- Components hide/show based on permission
  <button v-if="can('penjualan.create')">Create</button>
```

---

## ✨ Membuat Role Baru

### Step 1: Buka Roles Page

1. Login sebagai admin/owner
2. Buka menu **Pengaturan → Role**
3. Klik tombol **Tambah Role**

### Step 2: Isi Form

```
Nama Role:
  Input: "supervisor"
  Note: lowercase, no spaces, hanya angka/huruf/underscore/minus

Contoh nama role yang valid:
  ✅ supervisor
  ✅ warehouse_staff
  ✅ cash-manager
  ✅ cashier_001

Contoh nama role TIDAK valid:
  ❌ Supervisor (ada huruf besar)
  ❌ Super Visor (ada space)
  ❌ super@visor (ada karakter khusus)
```

### Step 3: Pilih Permissions

```
1. Lihat list Permission Groups (di bawah)
   Contoh: Produk, Persediaan, Penjualan, Pembelian, dll

2. Expand group yang perlu permission
   Klik arrow (chevron) di samping nama group

3. Centang subgroup untuk select semua permission di subgroup
   Contoh: "Transaksi" akan centang semua [view, create, edit, delete]

4. Atau centang individual permission
   Contoh: hanya "Lihat" tanpa "Tambah", "Ubah", "Hapus"

5. Gunakan tombol Quick Actions:
   • Pilih Semua - centang semua permission
   • Batal Semua - uncentang semua permission

6. Gunakan Search untuk cari permission tertentu
   Contoh: ketik "penjualan" untuk filter permissions penjualan saja
```

### Step 4: Simpan

Klik tombol **Buat Role** untuk simpan role baru.

**Sukses!** Role sekarang siap digunakan untuk assign ke users.

---

## ✏️ Mengedit Role

### Langkah-langkah

1. Di Roles page, cari role yang ingin diedit
2. Klik tombol **Edit** pada card role
3. Ubah nama atau permissions sesuai kebutuhan
4. Klik **Simpan Perubahan**

### Hal Penting

⚠️ **Perubahan Permission akan berlaku langsung ke semua users yang punya role ini!**

Contoh skenario:

- Role "kasir" saat ini punya permission: penjualan.view, penjualan.create
- Ada 5 users dengan role "kasir"
- Admin menambah permission "laporan.view"
- ✅ Semua 5 kasir sekarang bisa akses laporan

### Best Practice

- **Jangan banyak ubah role yang banyak digunakan**
  - Better: buat role baru untuk use case berbeda
  - Instead of: ubah role lama yang sudah banyak users

- **Dokumentasi perubahan**
  - Update catatan internal jika ada perubahan permission
  - Notify affected users tentang akses baru/berkurang

---

## 🗑️ Menghapus Role

### Kondisi Penghapusan

```
Role BISA dihapus jika:
  ✅ TIDAK ada pengguna yang menggunakan role ini

Role TIDAK BISA dihapus jika:
  ❌ Ada 1 atau lebih pengguna dengan role ini
```

### Cara Menghapus

1. Di Roles page, cari role yang ingin dihapus
2. Klik tombol ⛔ (delete icon) pada card
3. Confirm dialog akan muncul, menampilkan:
   - Nama role yang akan dihapus
   - Jumlah users yang punya role ini
4. Klik **Hapus Role** untuk confirm

### Jika Role Masih Digunakan

Jika ada users, sistem akan show error:

```
"Role masih digunakan pengguna dan tidak dapat dihapus."
```

**Solusi:**

1. Reassign semua users ke role lain terlebih dahulu
2. Baru hapus role yang kosong

---

## 📚 Best Practices

### 1. Planning Sebelum Membuat Role

```
Sebelum buat role baru:

1. Identifikasi posisi/fungsi
   Contoh: "Kasir", "Manager Gudang", "Akuntan"

2. List tasks/responsibilities
   Contoh: Kasir perlu:
   - Lihat harga produk
   - Buat transaksi penjualan
   - Lihat stok
   - Lihat laporan penjualan

3. Map ke permissions
   - Produk.view, Produk.Kategori.view
   - Penjualan.view, Penjualan.create, Penjualan.edit
   - Stok.view
   - Laporan.view

4. Review dengan stakeholder
   Pastikan sesuai business needs
```

### 2. Principle of Least Privilege

```
✅ GOOD: Give minimum permissions needed
   Role: "Kasir"
   Permissions: [penjualan.view, penjualan.create, stok.view]

❌ BAD: Give too many permissions
   Role: "Kasir"
   Permissions: [penjualan.*, stok.*, user.*, company.*]
   → Kasir punya akses yang tidak perlu (danger!)
```

### 3. Separation of Duties

```
Jangan mix responsibilities dalam 1 role:

❌ BAD:
  Role: "Financial Officer"
  Permissions: [pembelian.*, penjualan.*, coa.*, accounting.*]
  → Satu orang handle semua finance (no control/check)

✅ GOOD:
  Role 1: "Purchasing Officer"
  Permissions: [pembelian.view, pembelian.create]

  Role 2: "Accounting Manager"
  Permissions: [coa.*, accounting.*, laporan.*]

  → Different people → checks & balances
```

### 4. Naming Convention

```
✅ GOOD:
  - sales_manager (clear, lowercase, underscore)
  - warehouse_staff (descriptive)
  - cashier (simple)
  - purchasing_officer

❌ BAD:
  - SalesManager (use uppercase)
  - Sales Manager (use space)
  - sm (too short, unclear)
  - Sales_Manager_001 (too long)
```

### 5. Documentation

```
Keep notes tentang setiap role:

Role: cashier
- Digunakan oleh: Semua staff checkout
- Permissions: penjualan.view/create/edit, stok.view, laporan.view
- Created: 2024-01-15
- Last Modified: 2024-03-20 (added laporan.view)
```

---

## 🎭 Contoh Role Templates

### Template 1: Cashier (Kasir)

```
Role Name: cashier
Display Name: Kasir

Permissions:
✅ Produk.Produk (view, images)
✅ Produk.Kategori (view)
✅ Produk.Brand (view)
✅ Produk.Satuan (view)
✅ Supplier (view only)
✅ Stok.Gudang (view only)
✅ Penjualan.Transaksi (view, create, edit)
✅ Laporan (view)
✅ Cabang (view)
✅ Outlet (view)

❌ Admin functions (users, roles, company settings)
❌ Purchasing
❌ Accounting
```

### Template 2: Warehouse Manager (Manager Gudang)

```
Role Name: warehouse_manager
Display Name: Manager Gudang

Permissions:
✅ Produk (view, create, edit, delete + all sub)
✅ Supplier (view, create, edit, delete)
✅ Stok (view, adjust, opname, transfer)
✅ Pembelian (view)
✅ Penjualan (view)
✅ Laporan (view, export)
✅ Cabang (view)
✅ Outlet (view)
✅ Gudang (view, create, edit, delete)

❌ User management
❌ Accounting
❌ Company settings
```

### Template 3: Purchasing Officer (Officer Pembelian)

```
Role Name: purchasing_officer
Display Name: Officer Pembelian

Permissions:
✅ Produk (view all)
✅ Supplier (view, create, edit)
✅ Pembelian (view, create, edit)
✅ Laporan (view)
✅ Cabang (view)
✅ Outlet (view)

❌ Stock management
❌ Sales
❌ Accounting
❌ User management
```

### Template 4: Accountant (Akuntan)

```
Role Name: accountant
Display Name: Akuntan

Permissions:
✅ Chart of Account (view, create, edit, delete)
✅ Tax Configuration (view, create, edit)
✅ Pembelian (view)
✅ Penjualan (view)
✅ Laporan (view, export)
✅ Cabang (view)
✅ Outlet (view)

❌ Product management
❌ Stock adjustment
❌ User management
❌ Role management
```

---

## 🆘 Troubleshooting

### Q: Permission berubah tapi user masih lihat old akses

**Solusi:**

- Permission di-cache selama 5 menit
- Clear cache: `php artisan cache:clear`
- User bisa refresh browser atau logout/login

### Q: Buat role baru tapi permission tidak muncul

**Solusi:**

1. Cek apakah permission sudah ada di database
   - Run: `php artisan db:seed --class=PermissionGroupSeeder`
2. Refresh page
3. Cek apakah role sudah ter-save di database
   - Open database admin (phpmyadmin, etc)
   - Check `roles` table

### Q: Role tidak bisa dihapus

**Solusi:**

1. Cek jumlah users: "Role ini memiliki X pengguna"
2. Reassign users ke role lain:
   - Buka Users page
   - Edit setiap user
   - Ubah role ke role lain
3. Baru hapus role yang kosong

### Q: Ingin ubah role tapi khawatir affect users

**Solusi:**

1. Better: Buat role baru dengan permissions yang diinginkan
2. Assign new users ke role baru
3. Gradually migrate existing users
4. Delete old role setelah semua users sudah pindah

### Q: Kasir tiba-tiba tidak bisa create penjualan

**Solusi:**

1. Cek role mana yang dia punya: Users → Detail User
2. Klik role name → buka Roles page
3. Check apakah `penjualan.create` masih ada di permissions
4. Jika tidak ada, tambahkan kembali dan save
5. User bisa clear cache/refresh untuk instant update

---

## 📞 Tips & Support

### Best Practice Checklist

- [ ] Buat role berdasarkan job positions (kasir, manager, etc)
- [ ] Use least privilege principle (minimum permissions)
- [ ] Separate duties (different roles untuk checks/balances)
- [ ] Document setiap role dan alasannya
- [ ] Review permissions secara berkala (quarterly audit)
- [ ] Test role dengan test user sebelum production use
- [ ] Notify users ketika ada perubahan permission
- [ ] Keep track of role changes di audit log

### Common Mistakes ❌

- Membuat terlalu banyak roles (overcomplicate)
- Give too much permissions (security risk)
- Not documenting roles (forget why it was created)
- Direct edit role yang banyak digunakan (break users)
- Forget to remove permissions when no longer needed

### Best Approach ✅

- Plan permissions terlebih dahulu
- Create roles berdasarkan plan
- Test dengan small user group
- Roll out gradually
- Monitor & adjust based on feedback
- Annual review & cleanup
