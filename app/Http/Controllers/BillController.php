<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\FeeType;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $r)
    {
        if (!session('role')) return redirect('/login');

        $period = $r->period ?: now()->format('Y-m');

        $items = Bill::with(['student','feeType'])
            ->when($period, function($q) use ($period) {
                $q->where('period', $period);
            })
            ->when($r->fee_type_id, function($q) use ($r) {
                $q->where('fee_type_id', $r->fee_type_id);
            })
            // (opsional) dukung filter classroom_id di index:
            ->when($r->classroom_id, function($q) use ($r) {
                $q->whereHas('student', function($w) use ($r) {
                    $w->where('classroom_id', $r->classroom_id);
                });
            })
            ->orderBy('created_at','desc')
            ->paginate(25);

        $feeTypes   = FeeType::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get(); // kalau mau dipakai di filter halaman index

        return view('bills.index', compact('items','feeTypes','period','classrooms'));
    }

    public function show(Bill $bill)
    {
        if (!session('role')) return redirect('/login');
        $bill->load('student','feeType','payments');
        return view('bills.show', compact('bill'));
    }

    public function generateForm()
    {
        if (!session('role')) return redirect('/login');
        $feeTypes   = FeeType::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get(); // kirim ke view agar dropdown kelas tersedia
        return view('bills.generate', compact('feeTypes','classrooms'));
    }

    public function generate(Request $r)
    {
        if (!session('role')) return redirect('/login');

        $data = $r->validate([
            'period'       => 'required|date_format:Y-m',
            'fee_type_id'  => 'required|exists:fee_types,id',
            'amount'       => 'required|integer|min:1',
            'classroom_id' => 'nullable|exists:classrooms,id', // ← tambahan
            'kelas'        => 'nullable|string',               // ← tetap boleh dipakai sebagai fallback
        ]);

        $kelasText = isset($data['kelas']) ? trim((string)$data['kelas']) : '';

        // Prioritas filter:
        // 1) classroom_id (dropdown)
        // 2) kalau kosong dan kelasText ada -> LIKE di kolom 'kelas'
        $students = Student::query()
            ->when(!empty($data['classroom_id']), function($q) use ($data) {
                $q->where('classroom_id', $data['classroom_id']);
            })
            ->when(empty($data['classroom_id']) && $kelasText !== '', function($q) use ($kelasText) {
                $q->where('kelas', 'like', "%{$kelasText}%");
            })
            ->get();

        if ($students->isEmpty()) {
            return back()
                ->withInput()
                ->with('err', 'Tidak ada siswa yang cocok dengan filter.');
        }

        DB::transaction(function() use ($students, $data) {
            foreach ($students as $s) {
                Bill::firstOrCreate(
                    [
                        'student_id' => $s->id,
                        'fee_type_id'=> $data['fee_type_id'],
                        'period'     => $data['period'],
                    ],
                    [
                        'amount'      => (int) $data['amount'],
                        'paid_amount' => 0,
                        'status'      => 'Belum',
                    ]
                );
            }
        });

        return redirect()
            ->route('bills.index', [
                'period'      => $data['period'],
                'fee_type_id' => $data['fee_type_id'],
                'classroom_id'=> $data['classroom_id'] ?? null,
            ])
            ->with('ok', 'Tagihan massal selesai dibuat untuk '
                 . $students->count() . ' siswa pada periode ' . $data['period'] . '.');
    }

    public function import(Request $request)
    {
        if (!session('role') || session('role') !== 'admin') {
            return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
        }

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.nis'           => 'required|string',
            'items.*.fee_type_name' => 'required|string',
            'items.*.period'        => 'required|string', // YYYY-MM
            'items.*.amount'        => 'required|numeric|min:1',
            'items.*.paid_amount'   => 'nullable|numeric|min:0',
        ]);

        $created = 0; $updated = 0; $errors = [];

        foreach ($data['items'] as $idx => $row) {
            try {
                $student = Student::where('nis', trim($row['nis']))->first();
                if (!$student) { $errors[] = "Baris ".($idx+1).": NIS tidak ditemukan"; continue; }

                $feeType = FeeType::where('name', trim($row['fee_type_name']))->first();
                if (!$feeType) { $errors[] = "Baris ".($idx+1).": Jenis tagihan tidak ditemukan"; continue; }

                $period = substr(trim($row['period']), 0, 7); // normalize YYYY-MM
                $amount = (int) $row['amount'];
                $paid   = isset($row['paid_amount']) ? (int) $row['paid_amount'] : null;

                $bill = Bill::firstOrNew([
                    'student_id' => $student->id,
                    'fee_type_id'=> $feeType->id,
                    'period'     => $period,
                ]);

                $bill->amount = $amount;

                if ($bill->exists) {
                    if ($paid !== null) $bill->paid_amount = $paid;
                    $updated++;
                } else {
                    $bill->paid_amount = $paid !== null ? $paid : 0;
                    $created++;
                }

                // status berdasarkan amount vs paid_amount
                if ($bill->paid_amount >= $bill->amount) {
                    $bill->status = 'Lunas';
                } elseif ($bill->paid_amount > 0) {
                    $bill->status = 'Sebagian';
                } else {
                    $bill->status = 'Belum';
                }

                $bill->save();

            } catch (\Throwable $e) {
                $errors[] = "Baris ".($idx+1).": ".$e->getMessage();
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => "Import selesai. Tambah: {$created}, Update: {$updated}."
                         . (count($errors) ? " (Error: ".count($errors).")" : ""),
            'errors'  => $errors
        ]);
    }
}
