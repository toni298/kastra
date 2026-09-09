<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSetting extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'company_id',
        'template',
        'primary_color',
        'secondary_color',
        'tagline',
        'hero_title',
        'hero_subtitle',
        'banner_path',
        'logo_path',
        'show_feature_badges',
        'payment_cod_enabled',
        'payment_transfer_enabled',
        'payment_qris_enabled',
        'qris_image_path',
        'payment_notes',
        'pickup_enabled',
        'delivery_enabled',
        'flat_shipping_cost',
        'free_shipping_min',
        'subdomain',
        'custom_domain',
        'domain_status',
        'is_store_active',
        'whatsapp_number',
        'meta',
    ];

    protected $casts = [
        'show_feature_badges' => 'boolean',
        'payment_cod_enabled' => 'boolean',
        'payment_transfer_enabled' => 'boolean',
        'payment_qris_enabled' => 'boolean',
        'pickup_enabled' => 'boolean',
        'delivery_enabled' => 'boolean',
        'is_store_active' => 'boolean',
        'flat_shipping_cost' => 'integer',
        'free_shipping_min' => 'integer',
        'meta' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Definisi type section yang tersedia (lego blocks).
     * Setiap type mendefinisikan konten, konfigurasi yang bisa diubah user,
     * serta default-nya. Ini sumber kebenaran untuk editor & storefront.
     */
    public static function sectionTypes(): array
    {
        return [
            'hero' => [
                'label' => 'Hero / Banner',
                'configurable' => false, // konten hero dari pengaturan tampilan utama
                'defaults' => [],
            ],
            'badges' => [
                'label' => 'Badge Keunggulan',
                'configurable' => true,
                'defaults' => [
                    'items' => [
                        ['icon' => 'truck', 'title' => 'Pengiriman Cepat', 'desc' => 'Dikirim di hari yang sama'],
                        ['icon' => 'shield', 'title' => 'Produk Original', 'desc' => 'Garansi 100% asli'],
                        ['icon' => 'headset', 'title' => 'Dukungan 24/7', 'desc' => 'Siap membantu Anda'],
                        ['icon' => 'refresh', 'title' => 'Mudah Ditukar', 'desc' => 'Retur tanpa ribet'],
                    ],
                ],
            ],
            'categories' => [
                'label' => 'Kategori',
                'configurable' => true,
                'defaults' => ['title' => 'Jelajahi Kategori'],
            ],
            'latest' => [
                'label' => 'Produk Terbaru',
                'configurable' => true,
                'defaults' => ['title' => 'Produk Terbaru', 'link_label' => 'Produk Lainnya', 'limit' => 10],
            ],
            'promos' => [
                'label' => 'Produk Promo',
                'configurable' => true,
                'defaults' => ['title' => 'Promo & Penawaran', 'link_label' => 'Lihat Semua', 'limit' => 10],
            ],
            'promo_banner' => [
                'label' => 'Banner Promo',
                'configurable' => true,
                'defaults' => [
                    'eyebrow' => 'Penawaran Terbatas',
                    'title' => 'Hemat Besar',
                    'highlight' => 'Minggu Ini',
                    'subtitle' => 'Dapatkan produk pilihan dengan potongan harga spesial. Hanya sampai akhir minggu.',
                    'button_label' => 'Shop Now',
                    'dark_title' => 'Diskon hingga 50% untuk produk pilihan',
                    'dark_subtitle' => 'Cek halaman katalog untuk melihat seluruh produk promo yang tersedia di toko kami.',
                    'marquee' => "Gratis ongkir pengambilan di toko\nPembayaran aman\nGaransi produk 30 hari\nPromo spesial minggu ini",
                ],
            ],
            'advantages' => [
                'label' => 'Keunggulan Toko',
                'configurable' => true,
                'defaults' => [
                    'eyebrow' => 'Kenapa Memilih Kami',
                    'title' => 'Keunggulan Toko',
                    'subtitle' => 'Kami berkomitmen memberikan produk dan layanan terbaik untuk setiap pelanggan.',
                    'items' => [
                        ['icon' => 'shield', 'title' => 'Kualitas Terjamin', 'desc' => 'Produk pilihan terbaik'],
                        ['icon' => 'truck', 'title' => 'Pengiriman Aman', 'desc' => 'Dikemas dengan rapi'],
                        ['icon' => 'refresh', 'title' => 'Retur Fleksibel', 'desc' => 'Garansi pengembalian'],
                        ['icon' => 'headset', 'title' => 'Layanan Ramah', 'desc' => 'Respon cepat & sigap'],
                        ['icon' => 'zap', 'title' => 'Harga Bersaing', 'desc' => 'Penawaran terbaik'],
                    ],
                ],
            ],
            'testimonials' => [
                'label' => 'Testimoni',
                'configurable' => true,
                'defaults' => [
                    'eyebrow' => 'Testimoni',
                    'title' => 'Apa Kata Pelanggan',
                    'subtitle' => 'Cerita nyata dari pelanggan yang telah mempercayakan kebutuhannya kepada kami.',
                    'items' => [
                        ['name' => 'Budi Santoso', 'role' => 'Pengusaha Kafe', 'rating' => 5, 'quote' => 'Pelayanan sangat cepat dan hasilnya memuaskan. Pasti pesan lagi di sini.'],
                        ['name' => 'Siti Rahma', 'role' => 'Ibu Rumah Tangga', 'rating' => 5, 'quote' => 'Produk berkualitas, harga bersahabat, dan pengiriman rapi. Sangat direkomendasikan!'],
                        ['name' => 'Andi Wijaya', 'role' => 'Owner Distro', 'rating' => 4, 'quote' => 'Sudah langganan sejak lama. Respon admin cepat dan selalu membantu.'],
                    ],
                ],
            ],
            'richtext' => [
                'label' => 'Teks Kustom',
                'configurable' => true,
                'defaults' => ['content' => '<p>Tulis konten Anda di sini...</p>'],
            ],
        ];
    }

    /**
     * Sections default untuk storefront (satu per type, urutan bawaan).
     */
    public static function defaultSections(): array
    {
        $order = ['hero', 'badges', 'categories', 'latest', 'promo_banner', 'promos', 'testimonials', 'advantages'];
        $sections = [];
        foreach ($order as $i => $type) {
            $def = self::sectionTypes()[$type];
            $sections[] = [
                'key' => 'sec_' . $type,
                'type' => $type,
                'label' => $def['label'],
                'enabled' => true,
                'sort' => $i,
                'config' => $def['defaults'],
            ];
        }
        return $sections;
    }

    /**
     * Sections tersimpan di meta['sections'], dinormalisasi ke skema lego
     * (key/type/config). Migrasi otomatis dari format lama (id = nama section).
     */
    public function getSectionsAttribute(): array
    {
        $raw = $this->meta['sections'] ?? null;
        if (! is_array($raw) || empty($raw)) {
            return self::defaultSections();
        }

        return collect($raw)->map(fn($s) => self::normalizeSection($s))
            ->sortBy('sort')->values()->all();
    }

    /**
     * Normalisasi satu section ke skema lego; kompatibel dengan format lama.
     */
    public static function normalizeSection(array $s): array
    {
        $types = self::sectionTypes();
        // Format lama: id = nama type (hero/badges/...). Format baru: type terpisah.
        $type = $s['type'] ?? ($s['id'] ?? null);
        if (! isset($types[$type])) {
            $type = 'richtext';
        }
        $def = $types[$type];

        return [
            'key' => (string) ($s['key'] ?? $s['id'] ?? \Illuminate\Support\Str::uuid()),
            'type' => $type,
            'label' => (string) ($s['label'] ?? $def['label']),
            'enabled' => (bool) ($s['enabled'] ?? true),
            'sort' => (int) ($s['sort'] ?? 0),
            'config' => array_merge($def['defaults'], is_array($s['config'] ?? null) ? $s['config'] : []),
        ];
    }

    /**
     * Hanya section yang aktif, terurut.
     */
    public function getActiveSectionsAttribute(): array
    {
        return collect($this->sections)->where('enabled', true)->values()->all();
    }

    /**
     * Ongkir untuk subtotal tertentu (flat / gratis jika memenuhi minimum).
     */
    public function shippingCostFor(int $subtotal, string $deliveryMethod): int
    {
        if ($deliveryMethod !== 'delivery') {
            return 0;
        }

        $flat = (int) ($this->flat_shipping_cost ?? 0);
        $freeMin = $this->free_shipping_min;

        if ($freeMin !== null && $freeMin !== '' && $subtotal >= (int) $freeMin) {
            return 0;
        }

        return max(0, $flat);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path ? asset('storage/' . $this->banner_path) : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getQrisImageUrlAttribute(): ?string
    {
        return $this->qris_image_path ? asset('storage/' . $this->qris_image_path) : null;
    }
}
