<?php

namespace App\Http\Controllers;

use App\Models\FeeType;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    public function index(Request $r){
        if (!session('role')) return redirect('/login');
        $items = FeeType::orderBy('name')->paginate(20);
        return view('fee_types.index', compact('items'));
    }

    public function create(){ if (!session('role')) return redirect('/login'); return view('fee_types.create'); }

    public function store(Request $r){
        if (!session('role')) return redirect('/login');
        $data = $r->validate(['name'=>'required','description'=>'nullable']);
        FeeType::create($data);
        return redirect()->route('fee-types.index')->with('ok','Jenis tagihan ditambahkan.');
    }

    public function edit(FeeType $feeType){ if (!session('role')) return redirect('/login'); return view('fee_types.edit', compact('feeType')); }

    public function update(Request $r, FeeType $feeType){
        if (!session('role')) return redirect('/login');
        $data = $r->validate(['name'=>'required','description'=>'nullable']);
        $feeType->update($data);
        return redirect()->route('fee-types.index')->with('ok','Simpan sukses.');
    }

    public function destroy(FeeType $feeType){
        if (!session('role')) return redirect('/login');
        $feeType->delete();
        return back()->with('ok','Hapus sukses.');
    }


    public function import(\Illuminate\Http\Request $request)
{
    if (!session('role') || session('role') !== 'admin') {
        return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
    }

    $data = $request->validate([
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string|min:1',
        'items.*.description' => 'nullable|string'
    ]);

    $items = $data['items'];
    $created = 0; $updated = 0;

    foreach ($items as $row) {
        $name = trim($row['name']);
        $desc = isset($row['description']) ? trim($row['description']) : null;

        $existing = \App\Models\FeeType::where('name', $name)->first();
        if ($existing) {
            $existing->update(['description' => $desc]);
            $updated++;
        } else {
            \App\Models\FeeType::create(['name' => $name, 'description' => $desc]);
            $created++;
        }
    }

    return response()->json([
        'ok' => true,
        'message' => "Import selesai. Tambah: {$created}, Update: {$updated}."
    ]);
}

}
