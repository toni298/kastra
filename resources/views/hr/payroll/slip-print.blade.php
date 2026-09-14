@php
    $formatRupiah = static fn(int|float|null $amount): string => 'Rp ' .
        number_format((float) $amount, 0, ',', '.') .
        ',-';
    $payrollPeriod = $item->payroll?->period;
    $periodStart = $payrollPeriod?->copy()->startOfMonth();
    $periodEnd = $payrollPeriod?->copy()->endOfMonth();
    $overtimeRows = $item->overtimes->filter(function ($overtime) use ($periodStart, $periodEnd) {
        return $periodStart && $periodEnd && $overtime->overtime_date?->between($periodStart, $periodEnd);
    });
    $commissionDetail = $item->commission_detail ?? [];
    $totalEarnings =
        (int) $item->basic_salary +
        (int) $item->allowance +
        (int) $item->commission_amount +
        (int) $item->overtime_amount;
    $totalDeductions = (int) $item->deduction;
    $netSalary = (int) $item->net_salary;
    $companyAddress = collect([$company?->address, $company?->city, $company?->province, $company?->postal_code])
        ->filter()
        ->implode(', ');
    $status = $item->payroll?->status === 'posted' ? 'PAID' : strtoupper((string) ($item->payroll?->status ?? 'DRAFT'));
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji - {{ $item->employee?->name }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 0;
        }

        :root {
            color: #172033;
            font-family: Arial, Helvetica, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            width: 210mm;
            height: 148mm;
            margin: 0;
            overflow: hidden;
            font-size: 10px;
            line-height: 1.35;
        }

        .sheet {
            width: 210mm;
            height: 148mm;
            max-height: 148mm;
            overflow: hidden;
            page-break-after: avoid;
            page-break-inside: avoid;
            padding: 10mm;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #172033;
            padding-bottom: 8px;
        }

        .logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            margin-right: 10px;
        }

        .company {
            flex: 1;
        }

        .company-name {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .company-detail {
            margin: 2px 0 0;
            color: #526174;
        }

        .document {
            text-align: right;
        }

        .document h1 {
            margin: 0;
            font-size: 15px;
            letter-spacing: .8px;
        }

        .document p {
            margin: 3px 0 0;
            color: #526174;
        }

        .section-title {
            margin: 10px 0 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #9aa6b5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-row {
            display: flex;
            min-height: 17px;
        }

        .info-label {
            width: 115px;
            color: #526174;
        }

        .info-value {
            flex: 1;
            font-weight: 600;
        }

        .finance-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .finance-box {
            border: 1px solid #9aa6b5;
        }

        .finance-heading {
            padding: 5px 7px;
            background: #edf2f5;
            border-bottom: 1px solid #9aa6b5;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .finance-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 4px 7px;
            border-bottom: 1px solid #dce2e8;
        }

        .finance-row:last-child {
            border-bottom: 0;
        }

        .finance-row strong {
            white-space: nowrap;
        }

        .finance-total {
            font-weight: 700;
            background: #f5f7f8;
        }

        .thp {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 9px;
            padding: 8px 10px;
            border: 2px solid #172033;
        }

        .thp-label {
            font-size: 11px;
            font-weight: 700;
        }

        .thp-amount {
            font-size: 18px;
            font-weight: 700;
            white-space: nowrap;
        }

        .words {
            margin: 4px 0 0;
            font-style: italic;
            color: #526174;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 12px;
            text-align: center;
        }

        .signature {
            min-height: 58px;
        }

        .signature p {
            margin: 0;
        }

        .signature-line {
            margin: 36px auto 2px;
            width: 150px;
            border-bottom: 1px solid #172033;
        }

        .secret {
            margin-top: 7px;
            padding-top: 4px;
            border-top: 1px solid #9aa6b5;
            text-align: center;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        @media print {
            html {
                width: 210mm;
                height: 148mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .sheet {
                break-after: avoid-page;
                break-inside: avoid-page;
            }
        }
    </style>
</head>

<body>
    <main class="sheet">
        <header class="header">
            @if ($company?->logo_path)
                <img class="logo"
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($company->logo_path) }}"
                    alt="Logo {{ $company->name }}">
            @endif
            <div class="company">
                <h2 class="company-name">{{ $company?->name ?? config('app.name') }}</h2>
                <p class="company-detail">{{ $companyAddress ?: '-' }}</p>
                <p class="company-detail">{{ $company?->phone ?: ($company?->email ?: '-') }}</p>
            </div>
            <div class="document">
                <h1>SLIP GAJI KARYAWAN</h1>
                <p>Periode: {{ $payrollPeriod?->translatedFormat('F Y') ?? '-' }}</p>
            </div>
        </header>

        <section>
            <h3 class="section-title">INFORMASI KARYAWAN</h3>
            <div class="employee-grid">
                <div>
                    <div class="info-row"><span class="info-label">NIK</span><span
                            class="info-value">{{ $item->employee?->nik ?: '-' }}</span></div>
                    <div class="info-row"><span class="info-label">Nama Karyawan</span><span
                            class="info-value">{{ $item->employee?->name ?: '-' }}</span></div>
                    <div class="info-row"><span class="info-label">Jabatan / Role</span><span
                            class="info-value">{{ $item->employee?->role ?: '-' }}</span></div>
                    <div class="info-row"><span class="info-label">Status Karyawan</span><span
                            class="info-value">{{ ucfirst((string) ($item->employee?->status ?: '-')) }}</span></div>
                </div>
                <div>
                    <div class="info-row"><span class="info-label">Tanggal Cetak</span><span
                            class="info-value">{{ now()->format('d/m/Y H:i') }}</span></div>
                    <div class="info-row"><span class="info-label">Tanggal Pembayaran</span><span
                            class="info-value">{{ $item->payroll?->posted_at?->format('d/m/Y') ?: '-' }}</span></div>
                    <div class="info-row"><span class="info-label">Metode / Bank</span><span class="info-value">Belum
                            tersedia</span></div>
                    <div class="info-row"><span class="info-label">Status Slip</span><span
                            class="info-value">{{ $status }}</span></div>
                </div>
            </div>
        </section>

        <section>
            <h3 class="section-title">RINCIAN KEUANGAN</h3>
            <div class="finance-grid">
                <div class="finance-box">
                    <div class="finance-heading">PENERIMAAN / EARNINGS</div>
                    <div class="finance-row"><span>Gaji
                            Pokok</span><strong>{{ $formatRupiah($item->basic_salary) }}</strong></div>
                    <div class="finance-row"><span>Tunjangan Jabatan / Makan /
                            Transport</span><strong>{{ $formatRupiah($item->allowance) }}</strong></div>
                    <div class="finance-row"><span>Komisi
                            Penjualan</span><strong>{{ $formatRupiah($item->commission_amount) }}</strong></div>
                    <div class="finance-row"><span>Upah
                            Lembur{{ $overtimeRows->isNotEmpty() ? ' (' . $overtimeRows->count() . ' catatan)' : '' }}</span><strong>{{ $formatRupiah($item->overtime_amount) }}</strong>
                    </div>
                    <div class="finance-row finance-total"><span>Total
                            Penerimaan</span><strong>{{ $formatRupiah($totalEarnings) }}</strong></div>
                </div>
                <div class="finance-box">
                    <div class="finance-heading">POTONGAN / DEDUCTIONS</div>
                    <div class="finance-row"><span>Potongan Keterlambatan /
                            Alpa</span><strong>{{ $formatRupiah($item->deduction) }}</strong></div>
                    <div class="finance-row"><span>Angsuran / Kasbon
                            Karyawan</span><strong>{{ $formatRupiah(0) }}</strong></div>
                    <div class="finance-row"><span>Potongan BPJS / PPh 21</span><strong>{{ $formatRupiah(0) }}</strong>
                    </div>
                    <div class="finance-row"><span>Rincian Lainnya</span><strong>-</strong></div>
                    <div class="finance-row finance-total"><span>Total
                            Potongan</span><strong>{{ $formatRupiah($totalDeductions) }}</strong></div>
                </div>
            </div>
            <div class="thp"><span class="thp-label">TAKE HOME PAY (THP)<br><small>Total Penerimaan - Total
                        Potongan</small></span><span class="thp-amount">{{ $formatRupiah($netSalary) }}</span></div>
            <p class="words">Terbilang: {{ ucfirst($terbilang) }} Rupiah</p>
        </section>

        <footer>
            <div class="footer">
                <div class="signature">
                    <p>Tanda Tangan Penerima</p>
                    <div class="signature-line"></div><strong>{{ $item->employee?->name ?: '-' }}</strong>
                </div>
                <div class="signature">
                    <p>{{ $company?->city ?: 'Tempat' }}, {{ now()->format('d/m/Y') }}</p>
                    <div class="signature-line"></div><strong>HR / Finance / Manager</strong>
                </div>
            </div>
            <p class="secret">DOKUMEN RAHASIA - Harap menjaga kerahasiaan informasi rincian gaji ini.</p>
        </footer>
    </main>
    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>

</html>
