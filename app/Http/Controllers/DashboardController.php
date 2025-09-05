<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        if (!session('role')) return redirect('/login');

        $period   = $r->get('period') ?? now()->format('Y-m');
        $feeType  = $r->get('fee_type_id');

        $q = Bill::query()->where('period', $period);
        if ($feeType) $q->where('fee_type_id', $feeType);

        $total = (clone $q)->sum('amount');
        $paid  = (clone $q)->sum('paid_amount');

        $lunas     = (clone $q)->where('status','Lunas')->count();
        $sebagian  = (clone $q)->where('status','Sebagian')->count();
        $belum     = (clone $q)->where('status','Belum')->count();

        $belumList = (clone $q)->whereIn('status',['Belum','Sebagian'])
                      ->with('student','feeType')->orderBy('status')->get();

        return view('dashboard.index', compact(
            'period','feeType','total','paid','lunas','sebagian','belum','belumList'
        ));
    }
}
