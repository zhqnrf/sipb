<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function create(Bill $bill){
        if (!session('role')) return redirect('/login');
        $bill->load('student','feeType');
        return view('payments.create', compact('bill'));
    }

    protected function nextReceiptNo(): string
    {
        $prefix = config('sipb.receipt_prefix');
        $ym = now()->format('Ym');
        $seq = str_pad((string)( (int) (Payment::where('receipt_no','like', "{$prefix}-{$ym}-%")->count()) + 1 ), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$ym}-{$seq}";
    }

    public function store(Request $r, Bill $bill, ReceiptService $receiptService){
        if (!session('role')) return redirect('/login');

        $data = $r->validate(['amount'=>'required|integer|min:1']);
        if ($data['amount'] > $bill->remaining()) {
            return back()->with('err','Nominal melebihi sisa tagihan.')->withInput();
        }

        $receiptNo = $this->nextReceiptNo();

        $payment = DB::transaction(function() use ($bill,$data,$receiptNo){
            $p = Payment::create([
                'bill_id'    => $bill->id,
                'amount'     => $data['amount'],
                'paid_at'    => now(),
                'receipt_no' => $receiptNo,
            ]);

            $bill->paid_amount += $data['amount'];
            $bill->refreshStatus();

            return $p;
        });

        $receipt = $receiptService->generate($payment);

        return redirect()->route('receipts.show', $payment->id)
               ->with('ok','Pembayaran berhasil. Kwitansi siap dicetak.');
    }

    public function destroy(Payment $payment, Request $request)
    {
        // sederhana: hanya admin/TU
        if (!session('role') || session('role') !== 'admin') {
            return back()->with('err', 'Tidak diizinkan.');
        }

        $payment->load(['bill']); // pastikan relasi

        DB::beginTransaction();
        try {
            $bill = $payment->bill;

            if ($bill) {
                // rollback uang masuk
                $bill->paid_amount = max(0, ((int)$bill->paid_amount) - (int)$payment->amount);

                // hitung status baru
                if ($bill->paid_amount >= $bill->amount) {
                    $bill->status = 'Lunas';
                } elseif ($bill->paid_amount > 0) {
                    $bill->status = 'Sebagian';
                } else {
                    $bill->status = 'Belum';
                }
                $bill->save();
            }

            // hapus file kwitansi jika ada
            // asumsi relasi hasOne ke PaymentReceipt
            $receipt = null;
            if (method_exists($payment, 'receipt')) {
                $receipt = $payment->receipt()->first();
            } else {
                // fallback: cari berdasarkan payment_id jika field ada
                if (PaymentReceipt::where('payment_id', $payment->id)->exists()) {
                    $receipt = PaymentReceipt::where('payment_id', $payment->id)->first();
                }
            }

            if ($receipt) {
                // hapus file PDF dari storage jika path ada
                if (!empty($receipt->pdf_path)) {
                    // biasanya disimpan di disk 'public' => storage/app/public/receipts/...
                    if (Storage::disk('public')->exists($receipt->pdf_path)) {
                        Storage::disk('public')->delete($receipt->pdf_path);
                    } else {
                        // fallback bila pdf_path sudah mengandung 'receipts/...' tanpa disk prefix
                        @unlink(storage_path('app/public/' . ltrim($receipt->pdf_path, '/')));
                    }
                }
                $receipt->delete();
            }

            // hapus payment
            $payment->delete();

            DB::commit();

            return back()->with('ok', 'Pembayaran berhasil dihapus dan total terbayar telah diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('err', 'Gagal menghapus pembayaran: '.$e->getMessage());
        }
    }
}
