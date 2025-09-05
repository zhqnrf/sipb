@extends('layouts.app')
@section('title','Edit Jenis Tagihan')

@section('content')
<form method="post" class="row g-3" action="{{ route('fee-types.update',$feeType) }}">
  @csrf @method('put')
  <div class="col-md-6">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name',$feeType->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-12">
    <label class="form-label">Deskripsi (opsional)</label>
    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
    >{{ old('description',$feeType->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-12 d-flex gap-2">
    <a href="{{ route('fee-types.index') }}" class="btn btn-outline-secondary">Kembali</a>
    <button class="btn btn-brand">Update</button>
  </div>
</form>
@endsection
