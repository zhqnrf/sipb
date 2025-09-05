<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function show(Payment $payment){
        if (!session('role')) return redirect('/login');
        $payment->load('receipt');
        if (!$payment->receipt || !$payment->receipt->file_path) {
            return back()->with('err','Kwitansi belum tersedia.');
        }
        $url = asset('storage/' . $payment->receipt->file_path);
        return view('payments.receipt_link', compact('payment','url'));
    }
}
