<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $q     = trim($request->get('q', ''));
        $kelas = trim($request->get('kelas', ''));

        $items = Student::with('classroom')
            ->when($q, function($w) use ($q){
                $w->where(function($s) use ($q){
                    $s->where('name','like',"%{$q}%")
                      ->orWhere('nis','like',"%{$q}%")
                      ->orWhere('kelas','like',"%{$q}%")
                      ->orWhere('rombel','like',"%{$q}%");
                });
            })
            ->when($kelas, function($w) use ($kelas){
                $w->where('kelas','like',"%{$kelas}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('students.index', compact('items'));
    }

    public function create()
    {
        $classrooms = Classroom::orderBy('name')->get();
        return view('students.create', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nis'          => ['required','string','min:1','unique:students,nis'],
            'name'         => ['required','string','min:2'],
            'classroom_id' => ['nullable','exists:classrooms,id'],
            'kelas'        => ['nullable','string'],
            'rombel'       => ['nullable','string'],
        ]);

        // Sinkron dari master kelas jika dipilih
        if (!empty($data['classroom_id'])) {
            $cls = Classroom::find($data['classroom_id']);
            if ($cls) {
                // Jika 'kelas' kosong, isi dengan nama kelas master
                if (empty($data['kelas'])) {
                    $data['kelas'] = $cls->name;
                }
                // Jika 'rombel' kosong, isi otomatis dari master
                if (empty($data['rombel'])) {
                    $data['rombel'] = $cls->rombel;
                }
            }
        }

        Student::create($data);

        return redirect()->route('students.index')->with('ok','Siswa ditambahkan.');
    }

    public function edit(Student $student)
    {
        $classrooms = Classroom::orderBy('name')->get();
        return view('students.edit', compact('student','classrooms'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'nis'          => ['required','string','min:1', Rule::unique('students','nis')->ignore($student->id)],
            'name'         => ['required','string','min:2'],
            'classroom_id' => ['nullable','exists:classrooms,id'],
            'kelas'        => ['nullable','string'],
            'rombel'       => ['nullable','string'],
        ]);

        if (!empty($data['classroom_id'])) {
            $cls = Classroom::find($data['classroom_id']);
            if ($cls) {
                if (empty($data['kelas']))  $data['kelas']  = $cls->name;
                if (empty($data['rombel'])) $data['rombel'] = $cls->rombel;
            }
        }

        $student->update($data);

        return redirect()->route('students.index')->with('ok','Siswa diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return back()->with('ok','Siswa dihapus.');
    }

    /**
     * Import siswa dari JSON (dipost oleh SheetJS client)
     * items[]: { nis, name, kelas?, rombel?, classroom_id? | classroom_name? }
     * - Jika classroom_name diberikan dan classroom_id kosong, kita coba cari id-nya.
     */
    public function import(Request $request)
    {
        if (!session('role') || session('role') !== 'admin') {
            return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
        }

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.nis'  => 'required|string|min:1',
            'items.*.name' => 'required|string|min:1',
            'items.*.kelas'        => 'nullable|string',
            'items.*.rombel'       => 'nullable|string',
            'items.*.classroom_id' => 'nullable|integer|exists:classrooms,id',
            'items.*.classroom_name' => 'nullable|string',
        ]);

        $created = 0; $updated = 0; $errors = [];

        foreach ($data['items'] as $idx => $row) {
            try {
                $nis   = trim($row['nis']);
                $attrs = [
                    'name'   => trim($row['name']),
                    'kelas'  => isset($row['kelas'])  ? trim($row['kelas'])  : null,
                    'rombel' => isset($row['rombel']) ? trim($row['rombel']) : null,
                ];

                // Resolve classroom by id or by name
                $classroomId = isset($row['classroom_id']) ? (int)$row['classroom_id'] : null;
                if (!$classroomId && !empty($row['classroom_name'])) {
                    $cls = Classroom::where('name', trim($row['classroom_name']))->first();
                    if ($cls) $classroomId = $cls->id;
                }
                if ($classroomId) {
                    $attrs['classroom_id'] = $classroomId;
                    $cls = Classroom::find($classroomId);
                    if ($cls) {
                        if (empty($attrs['kelas']))  $attrs['kelas']  = $cls->name;
                        if (empty($attrs['rombel'])) $attrs['rombel'] = $cls->rombel;
                    }
                }

                $student = Student::where('nis', $nis)->first();
                if ($student) {
                    $student->update($attrs); $updated++;
                } else {
                    Student::create(array_merge(['nis'=>$nis], $attrs)); $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = 'Baris '.($idx+1).': '.$e->getMessage();
            }
        }

        return response()->json([
            'ok' => true,
            'message' => "Import selesai. Tambah: {$created}, Update: {$updated}."
                .(count($errors)? " (Error: ".count($errors).")" : ""),
            'errors' => $errors,
        ]);
    }
}
