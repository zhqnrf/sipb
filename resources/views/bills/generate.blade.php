@extends('layouts.app')
@section('title','Generate Tagihan Massal')

@section('content')
<style>
  /* Inline style khusus halaman ini */
  .hero{
    border-radius:16px;
    color:#fff;
    background: linear-gradient(135deg, var(--brand, #2563eb), var(--brand2, #60a5fa));
    box-shadow: 0 8px 24px rgba(37,99,235,.25);
  }
  .hero .icon-badge{
    width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.18);
    display:flex;align-items:center;justify-content:center;font-size:20px;
    border:1px solid rgba(255,255,255,.35);
  }
  .meta-chip{
    display:inline-flex;align-items:center;gap:.45rem;
    background:#ffffffcc;border:1px solid rgba(37,99,235,.25);
    color:#0f172a;border-radius:999px;padding:.28rem .65rem;font-weight:600;
  }
  .meta-chip i{font-size:15px}
  .card-lite-2{ background:#ffffffcc; border:1px solid rgba(37,99,235,.15); border-radius:16px; }
  .form-hint{font-size:.85rem;color:#64748b}
</style>

{{-- Header --}}
<div class="hero p-3 mb-3" data-aos="fade-up">
  <div class="d-flex align-items-start gap-3">
    <div class="icon-badge"><i class="bi bi-ui-checks-grid"></i></div>
    <div class="flex-fill">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="meta-chip"><i class="bi bi-calendar3"></i> Periode massal</span>
        <span class="meta-chip"><i class="bi bi-tags"></i> Jenis Tagihan</span>
        <span class="meta-chip"><i class="bi bi-cash-stack"></i> Nominal</span>
        <span class="meta-chip"><i class="bi bi-building"></i> Kelas (opsional)</span>
      </div>
      <div class="text-white-50 small mt-1">
        Buat tagihan massal untuk periode & jenis tertentu. Jika kelas dikosongkan, sistem akan membuat untuk semua siswa.
      </div>
    </div>
    <div class="d-none d-lg-flex">
      <a href="{{ route('bills.index',['period'=>now()->format('Y-m')]) }}" class="btn btn-light btn-sm">
        <i class="bi bi-receipt me-1"></i> Lihat Tagihan Periode Ini
      </a>
    </div>
  </div>
</div>

{{-- Form --}}
<form method="post" id="frmGen" class="row g-3 card-lite-2 p-3" data-aos="fade-up">@csrf
  <div class="col-md-3">
    <label class="form-label"><i class="bi bi-calendar2-week me-1"></i> Periode</label>
    <input type="month" name="period" class="form-control" required value="{{ now()->format('Y-m') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label"><i class="bi bi-tag me-1"></i> Jenis Tagihan</label>
    <select id="feeTypeSelect" name="fee_type_id" class="form-select" required>
      <option value="">-- Pilih --</option>
      @foreach($feeTypes as $f)
        <option value="{{ $f->id }}">{{ $f->name }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label"><i class="bi bi-currency-exchange me-1"></i> Nominal</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="text" id="amountDisplay" class="form-control" inputmode="numeric" autocomplete="off"
             placeholder="cth: 150000" required>
      <span class="input-group-text"><i class="bi bi-calculator"></i></span>
    </div>
    {{-- <div class="form-hint mt-1">Ketik angka saja, otomatis jadi 1.234.567.</div> --}}
    <input type="hidden" name="amount" id="amountRaw" value="">
  </div>

  {{-- Dropdown kelas (opsional, searchable) --}}
  <div class="col-md-3">
    <label class="form-label"><i class="bi bi-building me-1"></i> Kelas (opsional)</label>
    <select id="classroomSelect" name="classroom_id" class="form-select">
      <option value="">-- Semua Kelas --</option>
      @foreach(\App\Models\Classroom::orderBy('name')->get() as $c)
        <option value="{{ $c->id }}">{{ $c->name }}{{ $c->rombel ? ' - '.$c->rombel : '' }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-12 d-flex gap-2">
    <button class="btn btn-brand">
      <i class="bi bi-play-fill me-1"></i> Generate
    </button>
  
  </div>
</form>

{{-- Aktifkan Choices.js & format rupiah + konfirmasi --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  // Enable Choices (local assets sudah dimuat di layout)
  try {
    new Choices('#feeTypeSelect', { searchEnabled:true, itemSelectText:'', shouldSort:true });
    new Choices('#classroomSelect', { searchEnabled:true, searchPlaceholderValue:'Cari kelas...', itemSelectText:'', shouldSort:true });
  } catch(e) {}

  // ===== Format rupiah untuk nominal =====
  var disp = document.getElementById('amountDisplay');
  var raw  = document.getElementById('amountRaw');

  function onlyDigits(s){ return (s||'').replace(/[^\d]/g,''); }
  function formatRp(n){
    n = (n||'').toString();
    return n.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function syncAmount(){
    var v = onlyDigits(disp.value).replace(/^0+/,'');
    if(!v){ disp.value = ''; raw.value=''; return; }
    disp.value = formatRp(v);
    raw.value  = parseInt(v,10) || 0;
  }

  disp.addEventListener('input', syncAmount);
  disp.addEventListener('keypress', function(e){ if(!/[0-9]/.test(e.key||'')) e.preventDefault(); });
  disp.addEventListener('blur', syncAmount);

  // ===== Konfirmasi sebelum submit =====
  var frm = document.getElementById('frmGen');
  frm.addEventListener('submit', function(e){
    syncAmount();
    if(!(raw.value && parseInt(raw.value,10) >= 1)){
      e.preventDefault();
      if(typeof Swal!=='undefined'){
        Swal.fire({icon:'warning', title:'Nominal belum valid', text:'Isi nominal minimal Rp 1', confirmButtonText:'OK', customClass:{confirmButton:'btn btn-brand'}, buttonsStyling:false});
      } else { alert('Nominal belum valid'); }
      disp.focus();
      return;
    }
    if(typeof Swal==='undefined') return; // tanpa swal langsung submit

    e.preventDefault();
    var period = (frm.querySelector('input[name="period"]')||{}).value || '';
    var feeSel = document.getElementById('feeTypeSelect');
    var feeTxt = feeSel ? (feeSel.selectedOptions[0]?.text || '-') : '-';
    var clsSel = document.getElementById('classroomSelect');
    var clsTxt = clsSel ? (clsSel.selectedOptions[0]?.text || 'Semua Kelas') : 'Semua Kelas';
    var nominal= 'Rp ' + formatRp(raw.value);

    Swal.fire({
      icon:'question',
      title:'Generate Tagihan?',
      html: '<div class="text-start">'+
            '<div><i class="bi bi-calendar3 me-1"></i><b> Periode:</b> '+ period +'</div>'+
            '<div><i class="bi bi-tag me-1"></i><b> Jenis:</b> '+ feeTxt +'</div>'+
            '<div><i class="bi bi-cash-coin me-1"></i><b> Nominal:</b> '+ nominal +'</div>'+
            '<div><i class="bi bi-building me-1"></i><b> Kelas:</b> '+ clsTxt +'</div>'+
            '</div>',
      showCancelButton:true,
      confirmButtonText:'Ya, Lanjutkan',
      cancelButtonText:'Batal',
      reverseButtons:true,
      customClass:{ confirmButton:'btn btn-brand', cancelButton:'btn btn-outline-secondary me-2' },
      buttonsStyling:false,

    }).then(function(res){
      if(res.isConfirmed) frm.submit();
    });
  });
});
</script>
@endsection
