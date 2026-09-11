<?php

namespace App\Services;

use App\Models\User;

/**
 * MenuService - Centralized menu configuration & filtering.
 *
 * Sistem menu harus dapat:
 * 1. Defined centrally dalam 1 tempat (structured array)
 * 2. Filtered berdasarkan role/permission user
 * 3. Generate breadcrumb dari current route
 * 4. Support nested items & icons
 * 5. Track active item berdasarkan route pattern
 */
class MenuService
{
   private const MENUS = [
      [
         'id' => 'dashboard',
         'label' => 'Dashboard',
         'route' => 'dashboard',
         'icon' => 'LayoutDashboard',
         'permission' => null,
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
                  ['label' => 'Kategori', 'route' => 'products.master.index', 'params' => ['product_categories'], 'permission' => 'product_categories.view'],
                  ['label' => 'Brand', 'route' => 'products.master.index', 'params' => ['product_brands'], 'permission' => 'product_brands.view'],
                  ['label' => 'Satuan', 'route' => 'products.master.index', 'params' => ['units'], 'permission' => 'units.view'],
               ],
            ],
            [
               'id' => 'suppliers',
               'label' => 'Supplier',
               'route' => 'suppliers.index',
               'icon' => 'Truck',
               'permission' => 'suppliers.view',
            ],
            [
               'id' => 'users',
               'label' => 'Pengguna',
               'route' => 'users.index',
               'icon' => 'Users',
               'permission' => 'users.view',
            ],
            [
               'id' => 'hr',
               'label' => 'SDM / HR',
               'route' => 'hr.employees.index',
               'icon' => 'UsersRound',
               'permission' => 'hr.employees.view',
               'activePattern' => ['hr.*'],
            ],
         ],
      ],
      [
         'id' => 'transactions',
         'label' => 'Transaksi',
         'section' => true,
         'items' => [
            [
               'id' => 'inventory',
               'label' => 'Persediaan',
               'route' => 'inventory.stock',
               'icon' => 'Boxes',
               'permission' => [
                  'inventory.summary.view',
                  'inventory.stock.view',
                  'inventory.movements.view',
                  'inventory.transfers.view',
                  'inventory.opname.view',
               ],
               'activePattern' => ['inventory.*'],
            ],
            [
               'id' => 'sales',
               'label' => 'Penjualan',
               'route' => 'sales.index',
               'icon' => 'ShoppingCart',
               'permission' => [
                  'penjualan.summary.view',
                  'penjualan.transactions.view',
                  'penjualan.returns.view',
                  'penjualan.customers.view',
               ],
               'activePattern' => ['sales.*'],
            ],
            [
               'id' => 'purchases',
               'label' => 'Pembelian',
               'route' => 'purchases.index',
               'icon' => 'ShoppingBag',
               'permission' => 'pembelian.view',
               'activePattern' => ['purchases.*'],
            ],
            [
               'id' => 'cash-bank',
               'label' => 'Kas & Bank',
               'route' => 'cash-bank.transactions.index',
               'icon' => 'CircleDollarSign',
               'permission' => 'cash_bank.view',
               'activePattern' => ['cash-bank.*'],
            ],
            [
               'id' => 'reports',
               'label' => 'Laporan & Analitik',
               'route' => 'reports.sales',
               'icon' => 'ChartNoAxesCombined',
               'permission' => 'laporan.view',
               'activePattern' => ['reports.*'],
            ],
            [
               'id' => 'accounting',
               'label' => 'Akuntansi',
               'icon' => 'Landmark',
               'permission' => null,
               'activePattern' => ['accounting.*'],
               'children' => [
                  ['id' => 'accounting-overview', 'label' => 'Overview', 'route' => 'accounting.index', 'permission' => 'accounting.view'],
                  ['id' => 'accounting-reports', 'label' => 'Laporan', 'route' => 'accounting.reports.index', 'permission' => 'laporan.view'],
                  ['id' => 'accounting-settings', 'label' => 'Pengaturan', 'route' => 'accounting.settings.index', 'permission' => 'coa.view'],
               ],
            ],
         ],
      ],
      [
         'id' => 'ecommerce',
         'label' => 'E-Commerce',
         'section' => true,
         'items' => [
            [
               'id' => 'store-appearance',
               'label' => 'Tampilan Toko',
               'route' => 'ecommerce.settings.appearance',
               'icon' => 'Palette',
               'permission' => 'store.settings.view',
               'activePattern' => ['ecommerce.settings.appearance'],
            ],
            [
               'id' => 'store-payment',
               'label' => 'Pembayaran',
               'route' => 'ecommerce.settings.payment',
               'icon' => 'CreditCard',
               'permission' => 'store.settings.view',
               'activePattern' => ['ecommerce.settings.payment'],
            ],
            [
               'id' => 'store-shipping',
               'label' => 'Pengiriman',
               'route' => 'ecommerce.settings.shipping',
               'icon' => 'Truck',
               'permission' => 'store.settings.view',
               'activePattern' => ['ecommerce.settings.shipping'],
            ],
            [
               'id' => 'store-domain',
               'label' => 'Domain',
               'route' => 'ecommerce.settings.domain',
               'icon' => 'Globe',
               'permission' => 'store.settings.view',
               'activePattern' => ['ecommerce.settings.domain'],
            ],
         ],
      ],
      [
         'id' => 'settings',
         'label' => 'Pengaturan',
         'section' => true,
         'items' => [
            [
               'id' => 'company',
               'label' => 'Profil Bisnis',
               'route' => 'company.edit',
               'icon' => 'Store',
               'permission' => 'company.view',
               'activePattern' => ['company.*'],
            ],
            [
               'id' => 'roles',
               'label' => 'Role',
               'route' => 'roles.index',
               'icon' => 'ShieldCheck',
               'permission' => 'roles.view',
               'activePattern' => ['roles.*'],
            ],
         ],
      ],
   ];

   public function all(): array
   {
      return self::MENUS;
   }

   public function salesTabs(): array
   {
      return [
         ['id' => 'overview', 'label' => 'Ringkasan', 'route' => 'sales.index', 'permission' => 'penjualan.summary.view'],
         ['id' => 'transactions', 'label' => 'Transaksi', 'route' => 'sales.transactions.index', 'permission' => 'penjualan.transactions.view'],
         ['id' => 'returns', 'label' => 'Retur', 'route' => 'sales.returns.index', 'permission' => 'penjualan.returns.view'],
         ['id' => 'customers', 'label' => 'Pelanggan', 'route' => 'sales.customers.index', 'permission' => 'penjualan.customers.view'],
      ];
   }

   /**
    * Dapatkan menu yang accessible oleh user berdasarkan role & permission.
    */
   public function forUser(User $user, array $permissions): array
   {
      return $this->filterMenus(self::MENUS, $permissions);
   }

   /**
    * Filter menus berdasarkan permission user.
    */
   private function filterMenus(array $menus, array $permissions): array
   {
      return array_values(
         array_filter(
            array_map(
               fn($menu) => $this->filterMenuItem($menu, $permissions),
               $menus
            ),
            fn($menu) => $menu !== null
         )
      );
   }

   /**
    * Filter single menu item.
    */
   private function filterMenuItem(array $menu, array $permissions): ?array
   {
      if (!$this->canAccess($menu, $permissions)) {
         return null;
      }

      if ($menu['section'] ?? false) {
         $filteredItems = array_values(
            array_filter(
               array_map(
                  fn($item) => $this->filterMenuItem($item, $permissions),
                  $menu['items'] ?? []
               ),
               fn($item) => $item !== null
            )
         );

         // Jika section tidak punya item, skip section
         if (empty($filteredItems)) {
            return null;
         }

         return [
            ...$menu,
            'items' => $filteredItems,
         ];
      }

      // Filter children jika ada
      if (isset($menu['children']) && is_array($menu['children'])) {
         $filteredChildren = array_values(
            array_filter(
               $menu['children'],
               fn($child) => $this->canAccess($child, $permissions)
            )
         );

         return [
            ...$menu,
            'children' => $filteredChildren,
         ];
      }

      return $menu;
   }

   /**
    * Cek apakah user bisa access item based on permission.
    */
   private function canAccess(array $item, array $permissions): bool
   {
      $permission = $item['permission'] ?? null;

      // Jika tidak ada permission requirement, accessible untuk semua
      if ($permission === null) {
         return true;
      }

      if (is_array($permission)) {
         return count(array_intersect($permission, $permissions)) > 0;
      }

      return in_array($permission, $permissions, true);
   }

   /**
    * Cari menu item berdasarkan route name (untuk breadcrumb & active state).
    */
   public function findByRoute(?string $routeName): ?array
   {
      if (!$routeName) {
         return null;
      }

      foreach (self::MENUS as $menu) {
         $found = $this->findInMenu($menu, $routeName);
         if ($found) {
            return $found;
         }
      }

      return null;
   }

   /**
    * Recursive search untuk menu item.
    */
   private function findInMenu(array $menu, string $routeName): ?array
   {
      // Check current item
      if (($menu['route'] ?? null) === $routeName) {
         return $menu;
      }

      // Check pattern match
      if (isset($menu['activePattern']) && is_array($menu['activePattern'])) {
         foreach ($menu['activePattern'] as $pattern) {
            if (strpos($routeName, rtrim($pattern, '.*')) === 0) {
               return $menu;
            }
         }
      }

      // Recursive search children
      if (isset($menu['children']) && is_array($menu['children'])) {
         foreach ($menu['children'] as $child) {
            if (($child['route'] ?? null) === $routeName) {
               return $child;
            }
         }
      }

      // Recursive search items (sections)
      if (isset($menu['items']) && is_array($menu['items'])) {
         foreach ($menu['items'] as $item) {
            $found = $this->findInMenu($item, $routeName);
            if ($found) {
               return $found;
            }
         }
      }

      return null;
   }

   /**
    * Generate breadcrumb dari current route.
    */
   public function breadcrumb(array $allMenus, ?string $currentRoute): array
   {
      if (!$currentRoute) {
         return [['label' => 'Dashboard', 'route' => 'dashboard']];
      }

      $crumbs = [];
      $this->buildBreadcrumb($allMenus, $currentRoute, $crumbs);

      return !empty($crumbs) ? $crumbs : [['label' => 'Dashboard', 'route' => 'dashboard']];
   }

   /**
    * Build breadcrumb recursively.
    */
   private function buildBreadcrumb(array $menus, string $currentRoute, array &$crumbs, bool $inSection = false): bool
   {
      foreach ($menus as $menu) {
         // Section breadcrumb (optional)
         if (($menu['section'] ?? false) && !$inSection) {
            if ($this->buildBreadcrumb($menu['items'] ?? [], $currentRoute, $crumbs, true)) {
               return true;
            }
            continue;
         }

         // Match route atau pattern
         $matches = ($menu['route'] ?? null) === $currentRoute;
         if (!$matches && isset($menu['activePattern'])) {
            foreach ($menu['activePattern'] as $pattern) {
               if (strpos($currentRoute, rtrim($pattern, '.*')) === 0) {
                  $matches = true;
                  break;
               }
            }
         }

         if ($matches) {
            if ($menu['route'] ?? null) {
               $crumbs[] = [
                  'label' => $menu['label'],
                  'route' => $menu['route'],
               ];
            }
            return true;
         }

         // Cek children
         if (isset($menu['children']) && is_array($menu['children'])) {
            foreach ($menu['children'] as $child) {
               if (($child['route'] ?? null) === $currentRoute) {
                  if ($menu['route'] ?? null) {
                     $crumbs[] = ['label' => $menu['label'], 'route' => $menu['route']];
                  }
                  $crumbs[] = ['label' => $child['label'], 'route' => $child['route']];
                  return true;
               }
            }
         }

         // Recursive untuk items (sections)
         if (isset($menu['items']) && is_array($menu['items'])) {
            if ($this->buildBreadcrumb($menu['items'], $currentRoute, $crumbs, true)) {
               return true;
            }
         }
      }

      return false;
   }
}
