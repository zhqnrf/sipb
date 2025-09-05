@extends('layouts.app')
@section('title','Tambah Kelas')

@section('content')
<form method="post" action="{{ route('classrooms.store') }}" class="row g-3">
  @csrf
  <div class="col-md-6">
    <label class="form-label">Nama Kelas</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name') }}" placeholder="contoh: X IPA 1" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">Rombel (opsional)</label>
    <input type="text" name="rombel" class="form-control @error('rombel') is-invalid @enderror"
           value="{{ old('rombel') }}" placeholder="contoh: A">
    @error('rombel')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-12 d-flex gap-2">
    <a href="{{ route('classrooms.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-brand" type="submit">Simpan</button>
  </div>
</form>
@endsection
