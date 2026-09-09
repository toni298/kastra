<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use App\Models\SalesPayment;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingReportService
{
    public function balanceSheet(string $companyId, Carbon $from, Carbon $to): array
    {
        $cashBalance = (int) CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->sum('current_balance');

        $accountsReceivable = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('transaction_date', '<=', $to)
            ->sum('total');

        $salesPayments = (int) SalesPayment::query()
            ->where('company_id', $companyId)
            ->where('payment_date', '<=', $to)
            ->sum('amount');

        $accountsReceivable = max(0, $accountsReceivable - $salesPayments);

        $accountsPayable = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('transaction_date', '<=', $to)
            ->sum('total');

        $purchasePayments = (int) PurchasePayment::query()
            ->where('company_id', $companyId)
            ->where('payment_date', '<=', $to)
            ->sum('amount');

        $accountsPayable = max(0, $accountsPayable - $purchasePayments);

        $totalAssets = $cashBalance + $accountsReceivable;
        $totalLiabilities = $accountsPayable;
        $totalEquity = $totalAssets - $totalLiabilities;

        return [
            'title' => 'Neraca',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'sections' => [
                [
                    'label' => 'Aset',
                    'rows' => [
                        ['Kas & Bank', $cashBalance],
                        ['Piutang Usaha', $accountsReceivable],
                    ],
                    'total' => $totalAssets,
                ],
                [
                    'label' => 'Kewajiban',
                    'rows' => [
                        ['Hutang Usaha', $accountsPayable],
                    ],
                    'total' => $totalLiabilities,
                ],
                [
                    'label' => 'Modal',
                    'rows' => [
                        ['Modal Pemilik', $totalEquity],
                    ],
                    'total' => $totalEquity,
                ],
            ],
            'summary' => [
                ['Total Aset', $totalAssets],
                ['Total Kewajiban', $totalLiabilities],
                ['Total Modal', $totalEquity],
            ],
        ];
    }

    public function incomeStatement(string $companyId, Carbon $from, Carbon $to): array
    {
        $revenue = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total');

        $salesReturns = (int) SalesReturn::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('refund_amount');

        $netRevenue = $revenue - $salesReturns;

        $cogs = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total');

        $grossProfit = $netRevenue - $cogs;

        $operatingExpense = (int) CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $netProfit = $grossProfit - $operatingExpense;

        return [
            'title' => 'Laba Rugi',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'sections' => [
                [
                    'label' => 'Pendapatan',
                    'rows' => [
                        ['Pendapatan Penjualan', $revenue],
                        ['Retur Penjualan', -$salesReturns],
                        ['Pendapatan Bersih', $netRevenue],
                    ],
                    'total' => $netRevenue,
                ],
                [
                    'label' => 'Harga Pokok Penjualan',
                    'rows' => [
                        ['Pembelian', $cogs],
                    ],
                    'total' => $cogs,
                ],
                [
                    'label' => 'Beban Operasional',
                    'rows' => [
                        ['Pengeluaran Kas & Bank', $operatingExpense],
                    ],
                    'total' => $operatingExpense,
                ],
            ],
            'summary' => [
                ['Laba Kotor', $grossProfit],
                ['Laba Bersih', $netProfit],
            ],
        ];
    }

    public function cashFlow(string $companyId, Carbon $from, Carbon $to): array
    {
        $cashIn = (int) CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $cashOut = (int) CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $salesReceipts = (int) SalesPayment::query()
            ->where('company_id', $companyId)
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount');

        $purchasePayments = (int) PurchasePayment::query()
            ->where('company_id', $companyId)
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount');

        $netCashFlow = $cashIn + $salesReceipts - $cashOut - $purchasePayments;

        return [
            'title' => 'Arus Kas',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'sections' => [
                [
                    'label' => 'Kas Masuk',
                    'rows' => [
                        ['Penerimaan Kas & Bank', $cashIn],
                        ['Penerimaan dari Penjualan', $salesReceipts],
                    ],
                    'total' => $cashIn + $salesReceipts,
                ],
                [
                    'label' => 'Kas Keluar',
                    'rows' => [
                        ['Pengeluaran Kas & Bank', $cashOut],
                        ['Pembayaran ke Supplier', $purchasePayments],
                    ],
                    'total' => $cashOut + $purchasePayments,
                ],
            ],
            'summary' => [
                ['Arus Kas Bersih', $netCashFlow],
            ],
        ];
    }

    public function generalLedger(string $companyId, Carbon $from, Carbon $to, ?string $cursor = null, int $perPage = 25): array
    {
        $unionBuilder = $this->buildGeneralLedgerUnion($companyId, $from, $to);

        $totalDebit = (int) DB::query()->fromSub($unionBuilder, 'gl')->sum('debit');
        $totalCredit = (int) DB::query()->fromSub($unionBuilder, 'gl')->sum('credit');

        $query = DB::query()->fromSub($unionBuilder, 'gl')
            ->orderBy('transaction_date')
            ->orderBy('source_type')
            ->orderBy('id')
            ->limit($perPage + 1);

        if ($cursor) {
            $decoded = json_decode(base64_decode($cursor), true);
            if (isset($decoded['d'], $decoded['s'], $decoded['i'])) {
                $query->where(function ($q) use ($decoded) {
                    $q->where('transaction_date', '>', $decoded['d'])
                        ->orWhere(function ($q2) use ($decoded) {
                            $q2->where('transaction_date', $decoded['d'])
                                ->where('source_type', '>', $decoded['s']);
                        })
                        ->orWhere(function ($q3) use ($decoded) {
                            $q3->where('transaction_date', $decoded['d'])
                                ->where('source_type', $decoded['s'])
                                ->where('id', '>', $decoded['i']);
                        });
                });
            }
        }

        $rawRows = $query->get();

        $hasMore = $rawRows->count() > $perPage;
        if ($hasMore) {
            $rawRows = $rawRows->slice(0, $perPage);
        }

        $rows = $rawRows->map(fn ($r) => [
            Carbon::parse($r->transaction_date)->format('d/m/Y'),
            $r->transaction_number,
            $r->source,
            (int) $r->debit,
            (int) $r->credit,
        ])->values()->all();

        $nextCursor = null;
        if ($hasMore && $rawRows->isNotEmpty()) {
            $last = $rawRows->last();
            $nextCursor = base64_encode(json_encode([
                'd' => $last->transaction_date,
                's' => $last->source_type,
                'i' => $last->id,
            ]));
        }

        return [
            'title' => 'Buku Besar',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'columns' => ['Tanggal', 'Referensi', 'Sumber', 'Debit', 'Kredit'],
            'rows' => $rows,
            'summary' => [
                ['Total Debit', $totalDebit],
                ['Total Kredit', $totalCredit],
                ['Selisih', $totalDebit - $totalCredit],
            ],
            'pagination' => [
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
                'per_page' => $perPage,
            ],
        ];
    }

    private function buildGeneralLedgerUnion(string $companyId, Carbon $from, Carbon $to): \Illuminate\Database\Query\Builder
    {
        $sales = DB::table('sales_transactions')
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw("id, transaction_date, transaction_number, 'Penjualan' as source, 'sales' as source_type, total as debit, 0 as credit");

        $purchases = DB::table('purchase_transactions')
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw("id, transaction_date, transaction_number, 'Pembelian' as source, 'purchases' as source_type, 0 as debit, total as credit");

        return $sales->unionAll($purchases)
            ->unionAll(
                DB::table('cash_bank_transactions')
                    ->where('company_id', $companyId)
                    ->whereBetween('transaction_date', [$from, $to])
                    ->selectRaw("id, transaction_date, transaction_number, category as source, 'cashbank' as source_type, CASE WHEN type = 'in' THEN amount ELSE 0 END as debit, CASE WHEN type = 'out' THEN amount ELSE 0 END as credit")
            );
    }

    public function trialBalance(string $companyId, Carbon $from, Carbon $to): array
    {
        $salesTotal = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total');

        $purchaseTotal = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total');

        $cashIn = (int) CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $cashOut = (int) CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $cashBalance = (int) CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->sum('current_balance');

        $rows = [
            ['Kas & Bank', $cashBalance, 0],
            ['Pendapatan', 0, $salesTotal],
            ['Pembelian', $purchaseTotal, 0],
            ['Kas Masuk', $cashIn, 0],
            ['Kas Keluar', 0, $cashOut],
        ];

        $totalDebit = array_sum(array_column($rows, 1));
        $totalCredit = array_sum(array_column($rows, 2));

        return [
            'title' => 'Neraca Saldo',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'columns' => ['Akun', 'Debit', 'Kredit'],
            'rows' => $rows,
            'summary' => [
                ['Total Debit', $totalDebit],
                ['Total Kredit', $totalCredit],
            ],
        ];
    }

    public function equityChanges(string $companyId, Carbon $from, Carbon $to): array
    {
        $beginningEquity = (int) CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->sum('opening_balance');

        $revenue = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total');

        $expense = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total');

        $netProfit = $revenue - $expense;

        return [
            'title' => 'Perubahan Modal',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'sections' => [
                [
                    'label' => 'Perubahan Modal',
                    'rows' => [
                        ['Modal Awal', $beginningEquity],
                        ['Pendapatan', $revenue],
                        ['Beban', -$expense],
                        ['Laba Bersih', $netProfit],
                    ],
                    'total' => $beginningEquity + $netProfit,
                ],
            ],
            'summary' => [
                ['Modal Akhir', $beginningEquity + $netProfit],
            ],
        ];
    }

    public function taxSummary(string $companyId, Carbon $from, Carbon $to): array
    {
        $salesTax = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('tax');

        $purchaseTax = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('tax');

        $netTax = $salesTax - $purchaseTax;

        return [
            'title' => 'Pajak',
            'period' => ['from' => $from->format('d/m/Y'), 'to' => $to->format('d/m/Y')],
            'sections' => [
                [
                    'label' => 'Ringkasan Pajak',
                    'rows' => [
                        ['PPN Keluaran (Penjualan)', $salesTax],
                        ['PPN Masukan (Pembelian)', $purchaseTax],
                    ],
                    'total' => $netTax,
                ],
            ],
            'summary' => [
                ['Pajak Netto', $netTax],
            ],
        ];
    }
}
