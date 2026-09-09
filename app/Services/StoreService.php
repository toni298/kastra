<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Customer;
use App\Models\NumberGenerator;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreService
{
    public function __construct(private StockMovementService $stockMovementService) {}

    /**
     * Validate cart items against live prices & stock, create customer,
     * sales transaction (status pending), details and decrement branch stock.
     *
     * @throws ValidationException
     */
    public function checkout(Branch $store, array $data): SalesTransaction
    {
        return DB::transaction(function () use ($store, $data) {
            $items = collect($data['items']);

            $this->assertItemsValid($items);

            $productIds = $items->pluck('product_id')->unique()->values();

            $products = Product::query()
                ->select(['id', 'company_id', 'name', 'selling_price', 'is_active'])
                ->where('company_id', $store->company_id)
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $stocks = BranchProductStock::query()
                ->select(['id', 'product_id', 'quantity', 'discount'])
                ->where('company_id', $store->company_id)
                ->where('branch_id', $store->id)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $errors = [];
            $subtotal = 0;

            $lines = $items->map(function (array $item) use ($products, $stocks, &$errors, &$subtotal) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                if (! $product || ! $product->is_active) {
                    $errors["items"][] = 'Salah satu produk tidak tersedia lagi.';
                    return null;
                }

                $stock = $stocks->get($product->id);
                $available = (int) ($stock?->quantity ?? 0);

                if ($available < $quantity) {
                    $errors["items"][] = "Stok {$product->name} tidak mencukupi (tersisa {$available}).";
                    return null;
                }

                // Harga jual mengikuti diskon yang diatur pada stok cabang.
                $unitPrice = (int) $product->selling_price;
                $discount = min(100, max(0, (int) ($stock?->discount ?? 0)));
                if ($discount > 0) {
                    $unitPrice = (int) round($unitPrice * (100 - $discount) / 100);
                }
                $lineSubtotal = $unitPrice * $quantity;
                $subtotal += $lineSubtotal;

                return [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            })->filter();

            if ($errors !== []) {
                throw ValidationException::withMessages(['items' => array_values($errors['items'])]);
            }

            // Validasi metode pengiriman terhadap pengaturan toko.
            /** @var \App\Models\StoreSetting|null $settings */
            $settings = request()->attributes->get('store_settings');
            $method = $data['delivery_method'] ?? 'pickup';

            if ($method === 'delivery' && $settings && ! $settings->delivery_enabled) {
                throw ValidationException::withMessages(['delivery_method' => 'Pengiriman tidak tersedia untuk toko ini.']);
            }
            if ($method === 'pickup' && $settings && ! $settings->pickup_enabled) {
                throw ValidationException::withMessages(['delivery_method' => 'Ambil di toko tidak tersedia.']);
            }

            // Ongkir dihitung server-side dari settings (flat / gratis jika memenuhi minimum).
            $shippingCost = $settings
                ? $settings->shippingCostFor($subtotal, $method)
                : ($method === 'delivery' ? (int) ($data['shipping_cost'] ?? 0) : 0);

            $customer = Customer::create([
                'company_id' => $store->company_id,
                'branch_id' => $store->id,
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'telp' => $data['phone'],
                'status' => 'active',
            ]);

            $transaction = SalesTransaction::create([
                'company_id' => $store->company_id,
                'branch_id' => $store->id,
                'customer_id' => $customer->id,
                'created_by' => $this->storeOperatorId($store),
                'transaction_number' => $this->orderNumber($store->company_id),
                'document_type' => 'invoice',
                'order_channel' => 'store',
                'delivery_method' => $method,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'shipping_recipient_name' => $method === 'delivery' ? $data['name'] : null,
                'shipping_phone' => $method === 'delivery' ? $data['phone'] : null,
                'shipping_address' => $method === 'delivery' ? $data['address'] : null,
                'shipping_cost' => $shippingCost,
                'customer_note' => $data['note'] ?? null,
                'transaction_date' => Carbon::today(),
                'due_date' => null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal + $shippingCost,
                'note' => null,
            ]);

            $transaction->details()->createMany($lines->all());

            foreach ($lines as $line) {
                $this->stockMovementService->move([
                    'company_id' => $store->company_id,
                    'branch_id' => $store->id,
                    'product_id' => $line['product_id'],
                    'user_id' => $transaction->created_by,
                    'reference_number' => $transaction->transaction_number,
                    'type' => 'OUT',
                    'movement_type' => 'SALE',
                    'qty' => $line['quantity'],
                    'notes' => 'Checkout toko ' . $transaction->transaction_number,
                ]);
            }

            return $transaction->loadMissing('details:id,sales_transaction_id,product_id,quantity,unit_price,subtotal');
        });
    }

    private function assertItemsValid(Collection $items): void
    {
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => ['Keranjang belanja kosong.']]);
        }
    }

    private function orderNumber(string $companyId): string
    {
        $now = Carbon::now();
        $period = $now->copy()->startOfMonth();

        $generator = NumberGenerator::query()
            ->where('company_id', $companyId)
            ->where('document_type', 'store_order')
            ->where('aktif', true)
            ->lockForUpdate()
            ->first();

        if (! $generator) {
            $generator = NumberGenerator::create([
                'company_id' => $companyId,
                'document_type' => 'store_order',
                'prefix' => 'SO',
                'format' => '{PREFIX}-{YYYY}{MM}-{SEQUENCE}',
                'reset_period' => 'monthly',
                'current_sequence' => 0,
                'aktif' => true,
            ]);
        }

        if (! $generator->sequence_period || ! $generator->sequence_period->equalTo($period)) {
            $generator->current_sequence = 0;
            $generator->sequence_period = $period;
        }

        $generator->current_sequence++;
        $generator->save();

        $sequence = str_pad((string) $generator->current_sequence, 4, '0', STR_PAD_LEFT);

        return str_replace(
            ['{PREFIX}', '{YYYY}', '{MM}', '{SEQUENCE}'],
            [$generator->prefix, $now->format('Y'), $now->format('m'), $sequence],
            $generator->format
        );
    }

    private function storeOperatorId(Branch $store): string
    {
        $userId = User::query()
            ->where('company_id', $store->company_id)
            ->orderBy('created_at')
            ->value('id');

        if (! $userId) {
            throw ValidationException::withMessages(['store' => ['Toko belum memiliki operator.']]);
        }

        return $userId;
    }

    public function whatsappUrl(SalesTransaction $transaction, Branch $store): ?string
    {
        $number = $store->whatsapp_number ?: $store->phone;

        if (! $number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        if (Str::startsWith($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (Str::startsWith($digits, '620')) {
            $digits = '62' . substr($digits, 3);
        }

        $items = $transaction->details
            ->map(fn($detail) => sprintf(
                '- %s x%d (%s)',
                $detail->product?->name ?? 'Produk',
                $detail->quantity,
                'Rp ' . number_format((int) $detail->subtotal, 0, ',', '.')
            ))
            ->implode("\n");

        $message = implode("\n", array_filter([
            'Halo, saya sudah membuat pesanan di toko online Anda.',
            '',
            "No. Pesanan: {$transaction->transaction_number}",
            "Nama: {$transaction->customer?->name}",
            'Total: Rp ' . number_format((int) $transaction->total, 0, ',', '.'),
            'Pengiriman: ' . ($transaction->delivery_method === 'pickup' ? 'Ambil di tempat' : 'Dikirim'),
            '',
            'Ringkasan produk:',
            $items,
            '',
            'Mohon konfirmasi pesanan saya. Terima kasih.',
        ]));

        return "https://wa.me/{$digits}?text=" . rawurlencode($message);
    }
}
