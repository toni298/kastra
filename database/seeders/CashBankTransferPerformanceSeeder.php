<?php

namespace Database\Seeders;

use App\Models\CashBankAccount;
use App\Models\CashBankTransfer;
use App\Models\Company;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CashBankTransferPerformanceSeeder extends Seeder
{
    private const TRANSFER_COUNT = 2000;

    public function run(): void
    {
        $company = Company::query()->firstOrFail();
        $accounts = CashBankAccount::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->get();
        $userId = User::query()->where('company_id', $company->id)->value('id');

        if ($accounts->count() < 2) {
            throw new \RuntimeException('Seeder transfer kas dan bank membutuhkan minimal dua rekening aktif. Jalankan CashBankTransactionPerformanceSeeder terlebih dahulu.');
        }

        $balances = $accounts->mapWithKeys(fn (CashBankAccount $account) => [$account->id => $account->current_balance])->all();
        $transfers = [];

        for ($sequence = 1; $sequence <= self::TRANSFER_COUNT; $sequence++) {
            $number = sprintf('CBF-SEED-%05d', $sequence);

            if (CashBankTransfer::query()->where('transfer_number', $number)->exists()) {
                continue;
            }

            $sourceId = array_key_first($balances);
            foreach ($balances as $accountId => $balance) {
                if ($balance > $balances[$sourceId]) {
                    $sourceId = $accountId;
                }
            }
            $source = $accounts->firstWhere('id', $sourceId);
            $destination = $accounts->where('id', '!=', $source->id)->random();
            $amount = min(random_int(5, 100) * 10_000, max(10_000, intdiv($balances[$sourceId], 4)));
            $date = $this->randomDate();
            $timestamp = $date->setTime(random_int(7, 20), random_int(0, 59), random_int(0, 59));

            $transfers[] = [
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'source_account_id' => $source->id,
                'destination_account_id' => $destination->id,
                'created_by' => $userId,
                'transfer_number' => $number,
                'amount' => $amount,
                'transfer_date' => $date->toDateString(),
                'reference' => "REF-{$number}",
                'note' => 'Data performa transfer kas dan bank.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $balances[$sourceId] -= $amount;
            $balances[$destination->id] += $amount;

            if (count($transfers) === 250) {
                CashBankTransfer::query()->insert($transfers);
                $transfers = [];
            }
        }

        if ($transfers !== []) {
            CashBankTransfer::query()->insert($transfers);
        }

        foreach ($balances as $accountId => $balance) {
            CashBankAccount::query()->whereKey($accountId)->update(['current_balance' => $balance]);
        }
    }

    private function randomDate(): CarbonImmutable
    {
        $timezone = config('app.timezone');
        $start = CarbonImmutable::create(2024, 1, 1, 0, 0, 0, $timezone)->startOfDay();

        return CarbonImmutable::createFromTimestamp(random_int($start->timestamp, now($timezone)->endOfDay()->timestamp), $timezone);
    }
}
