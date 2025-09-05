@extends('layouts.app')
@section('title','Tambah Siswa')

@section('content')
<form method="post" action="{{ route('students.store') }}" class="row g-3">
  @csrf

  <div class="col-md-4">
    <label class="form-label">NIS</label>
    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
           value="{{ old('nis') }}" required autofocus>
    @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-8">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">Kelas</label>
    <select id="classroomSelect" name="classroom_id" class="form-select @error('classroom_id') is-invalid @enderror">
      <option value="" data-rombel="">-- Pilih kelas --</option>
      @foreach($classrooms as $c)
        <option value="{{ $c->id }}" data-rombel="{{ $c->rombel }}">{{ $c->name }}{{ $c->rombel ? ' - '.$c->rombel : '' }}</option>
      @endforeach
    </select>
    @error('classroom_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">Ketik untuk mencari kelas.</small>
  </div>

  <div class="col-md-6">
    <label class="form-label">Rombel</label>
    <input type="text" name="rombel" id="rombelInput"
           class="form-control @error('rombel') is-invalid @enderror"
           value="{{ old('rombel') }}" placeholder="Otomatis dari kelas, bisa diubah">
    @error('rombel')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  {{-- (Opsional) Simpan teks kelas lama untuk kompatibilitas --}}
  <div class="col-12">
    <label class="form-label">Kelas (Teks, opsional)</label>
    <input type="text" name="kelas" class="form-control @error('kelas') is-invalid @enderror"
           value="{{ old('kelas') }}" placeholder="Contoh: X IPA 1 (akan otomatis jika pilih kelas)">
    @error('kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-12 d-flex gap-2">
    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-brand" type="submit">Simpan</button>
  </div>
</form>

{{-- Choices.js + autofill rombel --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  try {
    new Choices('#classroomSelect', {
      searchEnabled: true,
      searchPlaceholderValue: 'Cari kelas...',
      shouldSort: true,
      itemSelectText: ''
    });
  } catch(e) {}

  var sel = document.getElementById('classroomSelect');
  var rom = document.getElementById('rombelInput');
  var kelasText = document.querySelector('input[name="kelas"]');

  function fillFromClass(){
    var opt = sel.options[sel.selectedIndex];
    if(!opt) return;
    var rombel = opt.getAttribute('data-rombel') || '';
    var kelas  = opt.textContent.trim();

    if (!rom.value || rom.dataset.autofilled === '1') {
      rom.value = rombel;
      rom.dataset.autofilled = '1';
    }
    if (!kelasText.value || kelasText.dataset.autofilled === '1') {
      // ambil hanya nama kelas tanpa " - rombel"
      kelasText.value = kelas.replace(/\s-\s.*$/,'');
      kelasText.dataset.autofilled = '1';
    }
  }

  if(sel && rom){
    sel.addEventListener('change', fillFromClass);
    rom.addEventListener('input',   function(){ rom.dataset.autofilled = '0'; });
    if(kelasText){ kelasText.addEventListener('input', function(){ kelasText.dataset.autofilled = '0'; }); }
  }
});
</script>
@endsection
