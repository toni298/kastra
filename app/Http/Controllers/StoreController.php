<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesTransaction;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(private StoreService $stores) {}

    public function index(Request $request): Response
    {
        $store = $request->attributes->get('store');
        $base = $this->catalogBaseQuery($store);

        // Maksimal 10 produk terbaru yang tersedia di toko ini.
        $latest = (clone $base)
            ->orderByDesc('products.created_at')
            ->orderBy('products.name')
            ->limit(10)
            ->get()
            ->map(fn($row) => $this->toCard($row));

        // Produk terlaris berdasarkan total kuantitas transaksi selesai di toko ini.
        // $bestSellers = (clone $base)
        //     ->leftJoin('sales_transaction_details', 'sales_transaction_details.product_id', '=', 'products.id')
        //     ->leftJoin('sales_transactions', function ($join) use ($store) {
        //         $join->on('sales_transactions.id', '=', 'sales_transaction_details.sales_transaction_id')
        //             ->where('sales_transactions.branch_id', $store->id)
        //             ->where('sales_transactions.status', 'completed');
        //     })
        //     ->groupBy([
        //         'products.id',
        //         'products.name',
        //         'products.selling_price',
        //         'products.created_at',
        //         'branch_product_stocks.quantity',
        //         'branch_product_stocks.discount',
        //     ])
        //     ->orderByDesc(DB::raw('COALESCE(SUM(sales_transaction_details.quantity), 0)'))
        //     ->orderBy('products.name')
        //     ->limit(10)
        //     ->get()
        //     ->map(fn($row) => $this->toCard($row));

        // Produk yang sedang diskon di toko ini.
        $promos = (clone $base)
            ->where('branch_product_stocks.discount', '>', 0)
            ->orderByDesc('branch_product_stocks.discount')
            ->orderBy('products.name')
            ->limit(10)
            ->get()
            ->map(fn($row) => $this->toCard($row));

        $categories = ProductCategory::query()
            ->select(['product_categories.id', 'product_categories.name'])
            ->join('products', 'products.category_id', '=', 'product_categories.id')
            ->join('branch_product_stocks', function ($join) use ($store) {
                $join->on('branch_product_stocks.product_id', '=', 'products.id')
                    ->where('branch_product_stocks.branch_id', $store->id)
                    ->where('branch_product_stocks.quantity', '>', 0);
            })
            ->where('product_categories.company_id', $store->company_id)
            ->where('product_categories.is_active', true)
            ->where('products.is_active', true)
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('product_categories.name')
            ->get();

        return Inertia::render('Store/Index', [
            'store' => $this->storeMeta($store),
            'latest' => $latest,
            // 'bestSellers' => $bestSellers,
            'promos' => $promos,
            'categories' => $categories,
        ]);
    }

    public function catalog(Request $request): Response
    {
        $store = $request->attributes->get('store');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'uuid'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0', 'gte:min_price'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'name_asc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = $this->catalogBaseQuery($store)
            ->when($filters['q'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn($q, $category) => $q->where('products.category_id', $category))
            ->when(isset($filters['min_price']), fn($q) => $q->where('products.selling_price', '>=', (int) $filters['min_price']))
            ->when(isset($filters['max_price']), fn($q) => $q->where('products.selling_price', '<=', (int) $filters['max_price']));

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('products.selling_price'),
            'price_desc' => $query->orderByDesc('products.selling_price'),
            'name_asc' => $query->orderBy('products.name'),
            default => $query->orderByDesc('products.created_at'),
        };

        $products = $query->paginate(12)->withQueryString()->through(fn($row) => $this->toCard($row));

        $categories = ProductCategory::query()
            ->select(['id', 'name'])
            ->where('company_id', $store->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Store/Catalog', [
            'store' => $this->storeMeta($store),
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'min_price' => $filters['min_price'] ?? null,
                'max_price' => $filters['max_price'] ?? null,
                'sort' => $filters['sort'] ?? 'newest',
            ],
        ]);
    }

    public function product(Request $request, string $company, string $branch, string $productSlug): Response
    {
        $store = $request->attributes->get('store');

        // Sementara: cari langsung berdasarkan ID produk dari URL.
        // ID (UUID) selalu berada di akhir slug: {nama-produk}-{uuid}.
        $productId = Str::afterLast($productSlug, '-');
        if (! Str::isUuid($productId)) {
            $candidate = Str::substr($productSlug, -36);
            $productId = Str::isUuid($candidate) ? $candidate : $productSlug;
        }

        $product = Product::query()
            ->select(['products.id', 'products.name', 'products.sku', 'products.description', 'products.selling_price', 'products.category_id', 'products.unit_id'])
            ->with([
                'category:id,name',
                'unit:id,name,code',
                'images:id,product_id,webp_path,thumbnail_path,original_path',
            ])
            ->withCount([
                'branchStocks as stock' => fn($q) => $q
                    ->select(DB::raw('COALESCE(SUM(quantity), 0)'))
                    ->where('branch_id', $store->id),
                'branchStocks as discount' => fn($q) => $q
                    ->select(DB::raw('COALESCE(MAX(discount), 0)'))
                    ->where('branch_id', $store->id),
            ])
            ->where('products.company_id', $store->company_id)
            ->where('products.is_active', true)
            ->where('products.id', $productId)
            ->firstOrFail();

        $related = Product::query()
            ->select(['products.id', 'products.name', 'products.selling_price', 'branch_product_stocks.quantity as stock', 'branch_product_stocks.discount'])
            ->join('branch_product_stocks', function ($join) use ($store) {
                $join->on('branch_product_stocks.product_id', '=', 'products.id')
                    ->where('branch_product_stocks.branch_id', $store->id)
                    ->where('branch_product_stocks.quantity', '>', 0);
            })
            ->with(['images' => fn($q) => $q->select(['id', 'product_id', 'webp_path', 'thumbnail_path', 'original_path'])->limit(1)])
            ->where('products.company_id', $store->company_id)
            ->where('products.is_active', true)
            ->where('products.id', '!=', $product->id)
            ->when($product->category_id, fn($q) => $q->where('products.category_id', $product->category_id))
            ->orderByDesc('branch_product_stocks.quantity')
            ->limit(4)
            ->get()
            ->map(fn($row) => $this->toCard($row));

        $price = (int) $product->selling_price;
        $discount = min(100, max(0, (int) $product->discount));

        return Inertia::render('Store/Product', [
            'store' => $this->storeMeta($store),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => Str::slug($product->name) . '-' . $product->id,
                'sku' => $product->sku,
                'description' => $product->description,
                'price' => $price,
                'discount' => $discount,
                'final_price' => $discount > 0 ? (int) round($price * (100 - $discount) / 100) : $price,
                'stock' => (int) $product->stock,
                'category' => $product->category?->only('id', 'name'),
                'unit' => $product->unit?->only('id', 'name'),
                'images' => $product->images->map(fn($image) => $image->preview_url)->filter()->values(),
            ],
            'related' => $related,
        ]);
    }

    public function cart(Request $request): Response
    {
        return Inertia::render('Store/Cart', [
            'store' => $this->storeMeta($request->attributes->get('store')),
        ]);
    }

    public function checkout(Request $request): Response
    {
        return Inertia::render('Store/Checkout', [
            'store' => $this->storeMeta($request->attributes->get('store')),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $store = $request->attributes->get('store');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:1000', 'required_if:delivery_method,delivery'],
            'delivery_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'payment_method' => ['nullable', Rule::in(['cod', 'transfer', 'qris'])],
            'shipping_cost' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $transaction = $this->stores->checkout($store, $data);

        return redirect()->route('store.order-success', [
            'company' => $store->company_slug,
            'branch' => $store->slug,
            'order' => $transaction->transaction_number,
        ])->with('order_placed', true);
    }

    public function orderSuccess(Request $request, string $company, string $branch, string $order): Response
    {
        $store = $request->attributes->get('store');

        abort_unless($request->session()->pull('order_placed'), 404);

        $transaction = SalesTransaction::query()
            ->select(['id', 'company_id', 'branch_id', 'customer_id', 'transaction_number', 'status', 'delivery_method', 'shipping_address', 'shipping_cost', 'total', 'customer_note', 'created_at'])
            ->with([
                'customer:id,name,telp,address',
                'details:id,sales_transaction_id,product_id,quantity,unit_price,subtotal',
                'details.product:id,name',
            ])
            ->where('company_id', $store->company_id)
            ->where('branch_id', $store->id)
            ->where('order_channel', 'store')
            ->where('transaction_number', $order)
            ->firstOrFail();

        return Inertia::render('Store/OrderSuccess', [
            'store' => $this->storeMeta($store),
            'order' => [
                'number' => $transaction->transaction_number,
                'status' => $transaction->status,
                'delivery_method' => $transaction->delivery_method,
                'shipping_address' => $transaction->shipping_address,
                'shipping_cost' => (int) $transaction->shipping_cost,
                'total' => (int) $transaction->total,
                'customer_note' => $transaction->customer_note,
                'created_at' => $transaction->created_at?->toIso8601String(),
                'customer' => [
                    'name' => $transaction->customer?->name,
                    'phone' => $transaction->customer?->telp,
                ],
                'items' => $transaction->details->map(fn($detail) => [
                    'name' => $detail->product?->name ?? 'Produk',
                    'quantity' => (int) $detail->quantity,
                    'unit_price' => (int) $detail->unit_price,
                    'subtotal' => (int) $detail->subtotal,
                ]),
            ],
            'whatsappUrl' => $this->stores->whatsappUrl($transaction, $store),
        ]);
    }

    private function catalogBaseQuery(Branch $store)
    {
        return Product::query()
            ->select(['products.id', 'products.name', 'products.selling_price', 'products.created_at', 'branch_product_stocks.quantity as stock', 'branch_product_stocks.discount'])
            ->join('branch_product_stocks', function ($join) use ($store) {
                $join->on('branch_product_stocks.product_id', '=', 'products.id')
                    ->where('branch_product_stocks.branch_id', $store->id)
                    ->where('branch_product_stocks.quantity', '>', 0);
            })
            ->with(['images' => fn($q) => $q->select(['id', 'product_id', 'webp_path', 'thumbnail_path', 'original_path'])->limit(1)])
            ->where('products.company_id', $store->company_id)
            ->where('products.is_active', true);
    }

    private function toCard($row): array
    {
        $price = (int) $row->selling_price;
        $discount = min(100, max(0, (int) ($row->discount ?? 0)));

        return [
            'id' => $row->id,
            'name' => $row->name,
            'slug' => Str::slug($row->name) . '-' . $row->id,
            'price' => $price,
            'discount' => $discount,
            'final_price' => $discount > 0 ? (int) round($price * (100 - $discount) / 100) : $price,
            'stock' => (int) $row->stock,
            'image' => $row->images->first()?->preview_url,
        ];
    }

    private function storeMeta(Branch $store): array
    {
        /** @var \App\Models\StoreSetting|null $s */
        $s = request()->attributes->get('store_settings');

        return [
            'company' => $store->company_name,
            'company_slug' => $store->company_slug,
            'company_logo' => $store->logo_path,
            'branch' => $store->name,
            'branch_slug' => $store->slug,
            'address' => $store->address,
            'city' => $store->city,
            'theme' => $s ? [
                'template' => $s->template,
                'primary_color' => $s->primary_color,
                'secondary_color' => $s->secondary_color,
                'tagline' => $s->tagline,
                'hero_title' => $s->hero_title,
                'hero_subtitle' => $s->hero_subtitle,
                'banner_url' => $s->banner_url,
                'logo_url' => $s->logo_url,
                'show_feature_badges' => $s->show_feature_badges,
                'whatsapp_number' => $s->whatsapp_number,
            ] : null,
            'sections' => $s?->active_sections ?? [],
            'payment' => $s ? [
                'cod' => $s->payment_cod_enabled,
                'transfer' => $s->payment_transfer_enabled,
                'qris' => $s->payment_qris_enabled,
                'qris_image_url' => $s->qris_image_url,
                'notes' => $s->payment_notes,
                'bank_accounts' => $s->company->storeBankAccounts()
                    ->where('is_active', true)->orderBy('sort_order')->get()
                    ->map(fn($b) => $b->only(['bank_name', 'account_number', 'account_name']))->all(),
            ] : null,
            'shipping' => $s ? [
                'pickup_enabled' => $s->pickup_enabled,
                'delivery_enabled' => $s->delivery_enabled,
                'flat_cost' => $s->flat_shipping_cost !== null ? (int) $s->flat_shipping_cost : null,
                'free_min' => $s->free_shipping_min !== null ? (int) $s->free_shipping_min : null,
            ] : null,
        ];
    }
}
