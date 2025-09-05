@extends('layouts.app')
@section('title','Tambah Jenis Tagihan')

@section('content')
<form method="post" action="{{ route('fee-types.store') }}" class="row g-3"> {{-- ← tambahkan action --}}
  @csrf
  <div class="col-md-6">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-12">
    <label class="form-label">Deskripsi (opsional)</label>
    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
              placeholder="SPP bulanan, Ujian Semester, dsb">{{ old('description') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-12 d-flex gap-2">
    <a href="{{ route('fee-types.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-brand" type="submit">Simpan</button>
  </div>
</form>
@endsection
