@extends('layouts.app')
@section('title','Ubah Password Role')

@section('content')
<div class="row">
  <div class="col-lg-12 col-md-9">
    <div class="card card-lite p-3" data-aos="fade-up">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="badge rounded-pill bg-primary-subtle text-primary border">
          <i class="bi bi-shield-lock-fill me-1"></i> Keamanan
        </span>
        <h5 class="mb-0">Ubah Password Role</h5>
      </div>

      <form method="post" action="{{ route('passwords.update') }}" id="frmPwRole" class="row g-3">
        @csrf

        <div class="col-12">
          <label class="form-label">Pilih Role</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
            <select name="target_role" class="form-select @error('target_role') is-invalid @enderror" required>
              <option value="admin">Admin/TU</option>
              <option value="kepsek">Kepsek</option>
            </select>
            @error('target_role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Password Baru</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" name="new_password" id="pw1"
                   class="form-control @error('new_password') is-invalid @enderror"
                   placeholder="Minimal 6 karakter" required>
            <button class="btn btn-outline-secondary" type="button" data-toggle="pw1" title="Tampilkan/Sembunyikan">
              <i class="bi bi-eye"></i>
            </button>
            @error('new_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <small class="text-muted d-flex align-items-center gap-2 mt-1">
            <i class="bi bi-info-circle"></i>
            Disarankan gunakan kombinasi huruf besar, kecil, angka.
          </small>
        </div>

        <div class="col-12">
          <label class="form-label">Konfirmasi Password Baru</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
            <input type="password" name="new_password_confirmation" id="pw2"
                   class="form-control" placeholder="Ulangi password" required>
            <button class="btn btn-outline-secondary" type="button" data-toggle="pw2" title="Tampilkan/Sembunyikan">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <small id="matchHint" class="text-muted d-none">
            <i class="bi bi-check2"></i> Password cocok.
          </small>
          <small id="mismatchHint" class="text-danger d-none">
            <i class="bi bi-exclamation-triangle"></i> Password belum cocok.
          </small>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Batal
          </a>
          <button class="btn btn-brand">
            <i class="bi bi-save2"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Show/Hide password + indikator cocok --}}
<script>
  (function(){
    // Toggle show/hide
    document.querySelectorAll('button[data-toggle]').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = btn.getAttribute('data-toggle');
        var input = document.getElementById(id);
        if(!input) return;
        var visible = input.getAttribute('type') === 'text';
        input.setAttribute('type', visible ? 'password' : 'text');
        var icon = btn.querySelector('i');
        if(icon){
          icon.classList.toggle('bi-eye',  visible);
          icon.classList.toggle('bi-eye-slash', !visible);
        }
      });
    });

    // Indikator konfirmasi cocok/tidak
    var pw1 = document.getElementById('pw1');
    var pw2 = document.getElementById('pw2');
    var ok  = document.getElementById('matchHint');
    var no  = document.getElementById('mismatchHint');

    function checkMatch(){
      if(!pw1 || !pw2 || !ok || !no) return;
      var a = pw1.value || '', b = pw2.value || '';
      if(!a && !b){
        ok.classList.add('d-none'); no.classList.add('d-none');
        return;
      }
      if(a === b){
        ok.classList.remove('d-none'); no.classList.add('d-none');
      }else{
        ok.classList.add('d-none'); no.classList.remove('d-none');
      }
    }
    if(pw1) pw1.addEventListener('input', checkMatch);
    if(pw2) pw2.addEventListener('input', checkMatch);

    // Cegah submit jika tidak cocok
    var frm = document.getElementById('frmPwRole');
    if(frm){
      frm.addEventListener('submit', function(e){
        if(pw1 && pw2 && pw1.value !== pw2.value){
          e.preventDefault();
          if (typeof Swal !== 'undefined'){
            Swal.fire({
              icon:'error', title:'Password tidak cocok',
              text:'Pastikan konfirmasi password sama persis.',
              confirmButtonText:'OK',
              customClass:{ confirmButton:'btn btn-brand' },
              buttonsStyling:false
            });
          } else {
            alert('Password tidak cocok.'); // fallback
          }
          pw2.focus();
        }
      });
    }
  })();
</script>
@endsection
