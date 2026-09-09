<?php

namespace Database\Seeders;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\Company;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class CashBankTransactionPerformanceSeeder extends Seeder
{
    private const TRANSACTION_COUNT = 1000;

    public function run(): void
    {
        $company = Company::query()->firstOrFail();
        $accounts = $this->accountsFor($company->id);
        $userId = User::query()->where('company_id', $company->id)->value('id');

        for ($sequence = 1; $sequence <= self::TRANSACTION_COUNT; $sequence++) {
            $number = sprintf('CBT-SEED-%05d', $sequence);

            if (CashBankTransaction::query()->where('transaction_number', $number)->exists()) {
                continue;
            }

            $account = $accounts->random();
            $type = random_int(1, 100) <= 60 ? 'in' : 'out';
            $amount = random_int(5, 250) * 10_000;

            if ($type === 'out' && $account->current_balance < $amount) {
                $type = 'in';
            }

            $date = $this->randomDate();
            $timestamp = $date->setTime(random_int(7, 20), random_int(0, 59), random_int(0, 59));

            CashBankTransaction::create([
                'company_id' => $company->id,
                'cash_bank_account_id' => $account->id,
                'created_by' => $userId,
                'transaction_number' => $number,
                'type' => $type,
                'category' => $this->categoryFor($type),
                'amount' => $amount,
                'transaction_date' => $date->toDateString(),
                'reference' => "REF-{$number}",
                'note' => 'Data performa transaksi kas dan bank.',
                'status' => 'completed',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $account->increment('current_balance', $type === 'in' ? $amount : -$amount);
            $account->refresh();
        }
    }

    private function accountsFor(string $companyId)
    {
        $accounts = CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        foreach ([
            ['Kas Operasional', 'cash', null],
            ['Bank BCA Operasional', 'bank', 'BCA'],
            ['Bank Mandiri Operasional', 'bank', 'Mandiri'],
            ['E-Wallet Bisnis', 'e_wallet', null],
        ] as [$name, $type, $bankName]) {
            $account = CashBankAccount::query()->firstOrCreate(
                ['company_id' => $companyId, 'name' => $name],
                ['type' => $type, 'bank_name' => $bankName, 'currency' => 'IDR', 'opening_balance' => 25_000_000, 'current_balance' => 25_000_000, 'is_active' => true],
            );
            $accounts->push($account);
        }

        return $accounts->unique('id')->values();
    }

    private function randomDate(): CarbonImmutable
    {
        $timezone = config('app.timezone');
        $start = CarbonImmutable::create(2024, 1, 1, 0, 0, 0, $timezone)->startOfDay();

        return CarbonImmutable::createFromTimestamp(random_int($start->timestamp, now($timezone)->endOfDay()->timestamp), $timezone);
    }

    private function categoryFor(string $type): string
    {
        $categories = $type === 'in'
            ? ['Penjualan Tunai', 'Pendapatan Lain', 'Setoran Modal']
            : ['Operasional', 'Utilitas', 'Biaya Lainnya'];

        return $categories[array_rand($categories)];
    }
}
