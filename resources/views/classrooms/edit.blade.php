@extends('layouts.app')
@section('title','Edit Kelas')

@section('content')
<form method="post" action="{{ route('classrooms.update',$classroom) }}" class="row g-3">
  @csrf @method('put')
  <div class="col-md-6">
    <label class="form-label">Nama Kelas</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name',$classroom->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">Rombel (opsional)</label>
    <input type="text" name="rombel" class="form-control @error('rombel') is-invalid @enderror"
           value="{{ old('rombel',$classroom->rombel) }}">
    @error('rombel')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-12 d-flex gap-2">
    <a href="{{ route('classrooms.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-brand" type="submit">Update</button>
  </div>
</form>
@endsection
