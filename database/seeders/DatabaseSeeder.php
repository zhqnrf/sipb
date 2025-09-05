<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\FeeType;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Services\ReceiptService;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Pastikan folder receipts ada
        $dir = config('sipb.receipt_dir', 'receipts');
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        // 1) Students
        $studentsData = [
            ['nis'=>'2509001','name'=>'Alya Putri','kelas'=>'X IPA 1','rombel'=>'A'],
            ['nis'=>'2509002','name'=>'Bima Arya','kelas'=>'X IPA 1','rombel'=>'A'],
            ['nis'=>'2509003','name'=>'Citra Anggun','kelas'=>'X IPA 2','rombel'=>'B'],
            ['nis'=>'2509004','name'=>'Davi Pratama','kelas'=>'X IPA 2','rombel'=>'B'],
            ['nis'=>'2509005','name'=>'Eka Nur','kelas'=>'X IPS 1','rombel'=>'A'],
            ['nis'=>'2509006','name'=>'Farhan Rizky','kelas'=>'X IPS 1','rombel'=>'A'],
            ['nis'=>'2509007','name'=>'Gita Lestari','kelas'=>'XI IPA 1','rombel'=>'A'],
            ['nis'=>'2509008','name'=>'Hafizh Akbar','kelas'=>'XI IPA 1','rombel'=>'A'],
            ['nis'=>'2509009','name'=>'Intan Cahyani','kelas'=>'XI IPS 1','rombel'=>'B'],
            ['nis'=>'2509010','name'=>'Joko Santoso','kelas'=>'XI IPS 1','rombel'=>'B'],
            ['nis'=>'2509011','name'=>'Kirana Dewi','kelas'=>'XII IPA 1','rombel'=>'A'],
            ['nis'=>'2509012','name'=>'Lukman Hakim','kelas'=>'XII IPA 1','rombel'=>'A'],
        ];
        $students = [];
        foreach ($studentsData as $s) {
            $students[] = Student::firstOrCreate(['nis' => $s['nis']], $s);
        }

        // 2) Fee Types
        $feeSPP   = FeeType::firstOrCreate(['name' => 'SPP'],     ['description' => 'SPP bulanan']);
        $feeUjian = FeeType::firstOrCreate(['name' => 'Ujian'],   ['description' => 'Ujian Semester']);
        $feeKeg   = FeeType::firstOrCreate(['name' => 'Kegiatan'],['description' => 'Kegiatan sekolah']);

        // 3) Bills untuk 2 periode
        $periodNow  = now()->format('Y-m');
        $periodPrev = now()->subMonth()->format('Y-m');

        $amtSPP   = 200000;
        $amtUjian = 150000;
        $amtKeg   = 100000;

        DB::transaction(function () use ($students, $feeSPP, $feeUjian, $feeKeg, $periodNow, $periodPrev, $amtSPP, $amtUjian, $amtKeg) {
            foreach ($students as $i => $stu) {
                // SPP dua periode
                Bill::firstOrCreate(
                    ['student_id' => $stu->id, 'fee_type_id' => $feeSPP->id, 'period' => $periodPrev],
                    ['amount' => $amtSPP, 'paid_amount' => 0, 'status' => 'Belum']
                );
                Bill::firstOrCreate(
                    ['student_id' => $stu->id, 'fee_type_id' => $feeSPP->id, 'period' => $periodNow],
                    ['amount' => $amtSPP, 'paid_amount' => 0, 'status' => 'Belum']
                );

                // Ujian hanya periode sekarang (untuk indeks genap)
                if ($i % 2 === 0) {
                    Bill::firstOrCreate(
                        ['student_id' => $stu->id, 'fee_type_id' => $feeUjian->id, 'period' => $periodNow],
                        ['amount' => $amtUjian, 'paid_amount' => 0, 'status' => 'Belum']
                    );
                }

                // Kegiatan hanya periode sebelumnya (kelipatan 3)
                if ($i % 3 === 0) {
                    Bill::firstOrCreate(
                        ['student_id' => $stu->id, 'fee_type_id' => $feeKeg->id, 'period' => $periodPrev],
                        ['amount' => $amtKeg, 'paid_amount' => 0, 'status' => 'Belum']
                    );
                }
            }
        });

        // 4) Buat pembayaran + kwitansi
        /** @var ReceiptService $receiptService */
        $receiptService = app(ReceiptService::class);

        $billsNow = Bill::with('student','feeType')
            ->where('fee_type_id', $feeSPP->id)
            ->where('period', $periodNow)
            ->get();

        $fullPayIdx = [0, 2, 4, 6]; // Lunas
        $partialIdx = [1, 3, 5, 7]; // Sebagian

        DB::transaction(function () use ($billsNow, $fullPayIdx, $partialIdx, $amtSPP, $receiptService) {
            foreach ($billsNow as $idx => $bill) {
                if (in_array($idx, $fullPayIdx, true)) {
                    // Bayar full
                    $payment = Payment::create([
                        'bill_id'    => $bill->id,
                        'amount'     => $amtSPP,
                        'paid_at'    => now(),
                        'receipt_no' => $this->nextReceiptNo(),
                    ]);

                    $bill->increment('paid_amount', $amtSPP);
                    $status = ($bill->paid_amount >= $bill->amount) ? 'Lunas' : (($bill->paid_amount > 0) ? 'Sebagian' : 'Belum');
                    $bill->update(['status' => $status]);

                    $receiptService->generate($payment);
                } elseif (in_array($idx, $partialIdx, true)) {
                    // Bayar sebagian
                    $partial = 80000;
                    $payment = Payment::create([
                        'bill_id'    => $bill->id,
                        'amount'     => $partial,
                        'paid_at'    => now(),
                        'receipt_no' => $this->nextReceiptNo(),
                    ]);

                    $bill->increment('paid_amount', $partial);
                    $status = ($bill->paid_amount >= $bill->amount) ? 'Lunas' : (($bill->paid_amount > 0) ? 'Sebagian' : 'Belum');
                    $bill->update(['status' => $status]);

                    $receiptService->generate($payment);
                }
            }
        });

        // 5) Ringkasan
        $totalBills = Bill::count();
        $lunas      = Bill::where('status','Lunas')->count();
        $sebagian   = Bill::where('status','Sebagian')->count();
        $belum      = Bill::where('status','Belum')->count();
        $payments   = Payment::count();

        $this->command->info("Seed selesai:");
        $this->command->info("- Students : ".Student::count());
        $this->command->info("- FeeTypes : ".FeeType::count());
        $this->command->info("- Bills    : $totalBills (Lunas:$lunas | Sebagian:$sebagian | Belum:$belum)");
        $this->command->info("- Payments : $payments");
        $this->command->info("- Receipts : ".PaymentReceipt::count()." (PDF di storage/app/public/receipts)");
    }

    protected function nextReceiptNo(): string
    {
        $prefix = config('sipb.receipt_prefix', 'RCP');
        $ym = now()->format('Ym');
        $count = Payment::where('receipt_no', 'like', "{$prefix}-{$ym}-%")->count();
        $seq = str_pad((string)($count + 1), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$ym}-{$seq}";
    }
}
