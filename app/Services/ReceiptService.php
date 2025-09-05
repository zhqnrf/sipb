<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentReceipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    public function generate(Payment $payment): PaymentReceipt
    {
        $dir = config('sipb.receipt_dir'); // receipts
        $filename = $payment->receipt_no . '.pdf';
        $path = $dir . '/' . $filename;

        $pdf = Pdf::loadView('receipts.show', [
            'payment'     => $payment->load('bill.student','bill.feeType'),
            'school_name' => config('sipb.school_name'),
        ])->setPaper('A5', 'portrait');

        Storage::disk('public')->put($path, $pdf->output());

        return PaymentReceipt::create([
            'payment_id' => $payment->id,
            'file_path'  => $path,
        ]);
    }
}
