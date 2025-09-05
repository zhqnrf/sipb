<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $items = Classroom::when($q, function($w) use ($q){
                $w->where('name','like',"%{$q}%")->orWhere('rombel','like',"%{$q}%");
            })
            ->orderBy('name')->paginate(15);

        return view('classrooms.index', compact('items','q'));
    }

    public function create()
    {
        return view('classrooms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|min:2|unique:classrooms,name',
            'rombel' => 'nullable|string|max:50',
        ]);
        Classroom::create($data);
        return redirect()->route('classrooms.index')->with('ok','Kelas ditambahkan.');
    }

    public function edit(Classroom $classroom)
    {
        return view('classrooms.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'name'   => 'required|string|min:2|unique:classrooms,name,'.$classroom->id,
            'rombel' => 'nullable|string|max:50',
        ]);
        $classroom->update($data);
        return redirect()->route('classrooms.index')->with('ok','Kelas diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return back()->with('ok','Kelas dihapus.');
    }

    public function import(\Illuminate\Http\Request $request)
{
    if (!session('role') || session('role') !== 'admin') {
        return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
    }

    $data = $request->validate([
        'items' => 'required|array|min:1',
        'items.*.name'   => 'required|string|min:1',
        'items.*.rombel' => 'nullable|string',
    ]);

    $created = 0; $updated = 0;
    foreach ($data['items'] as $row) {
        $name = trim($row['name']);
        $attrs = ['rombel' => isset($row['rombel']) ? trim($row['rombel']) : null];

        $existing = \App\Models\Classroom::where('name', $name)->first();
        if ($existing) { $existing->update($attrs); $updated++; }
        else { \App\Models\Classroom::create(array_merge(['name'=>$name], $attrs)); $created++; }
    }

    return response()->json(['ok'=>true, 'message'=>"Import selesai. Tambah: {$created}, Update: {$updated}."]);
}

}
