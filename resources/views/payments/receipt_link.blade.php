@extends('layouts.app')
@section('title','Kwitansi')

@section('content')
<style>
  /* Inline style khusus halaman kwitansi */
  .receipt-hero{
    border-radius:16px;
    color:#fff;
    background: linear-gradient(135deg, var(--brand, #2563eb), var(--brand2, #60a5fa));
    box-shadow: 0 8px 24px rgba(37,99,235,.25);
  }
  .receipt-hero .icon-badge{
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
  .btn-ghost{
    --bs-btn-color:#0f172a; --bs-btn-border-color:rgba(2,6,23,.15);
    --bs-btn-hover-bg:rgba(2,6,23,.04); --bs-btn-hover-border-color:rgba(2,6,23,.25);
    border-radius:12px;
  }
</style>

{{-- Header sukses --}}
<div class="receipt-hero p-3 mb-3" data-aos="fade-up">
  <div class="d-flex align-items-start gap-3">
    <div class="icon-badge"><i class="bi bi-receipt-cutoff"></i></div>
    <div class="flex-fill">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="meta-chip"><i class="bi bi-check2-circle"></i> Kwitansi berhasil dibuat</span>
        <span class="meta-chip d-none d-sm-inline-flex"><i class="bi bi-info-circle"></i> Siap untuk dicetak</span>
      </div>
      <div class="text-white-75 small mt-1">
        Jika tab baru tidak terbuka otomatis, gunakan tombol di bawah untuk membuka/unduh kwitansi.
      </div>
    </div>
    <div class="d-none d-lg-flex">
      <a href="{{ route('bills.index', ['period'=>now()->format('Y-m')]) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Tagihan
      </a>
    </div>
  </div>
</div>

{{-- Aksi utama --}}
<div class="card-lite-2 p-3" data-aos="fade-up" data-aos-delay="50">
  <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-check-circle-fill"></i>
    <div>Kwitansi siap. Gunakan tombol di bawah untuk membuka atau mengunduh.</div>
  </div>

  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-brand" target="_blank" rel="noopener" href="{{ $url }}" id="btnOpen">
      <i class="bi bi-box-arrow-up-right me-1"></i> Buka Kwitansi (PDF)
    </a>

    {{-- Unduh langsung (akan bekerja jika file di domain yang sama) --}}
    <a class="btn btn-ghost" href="{{ $url }}" download>
      <i class="bi bi-download me-1"></i> Unduh PDF
    </a>

    {{-- Salin tautan --}}
    <button type="button" class="btn btn-ghost" id="btnCopy">
      <i class="bi bi-clipboard me-1"></i> Salin Link
    </button>

  </div>

  <div class="small text-muted mt-2">
    Catatan: Beberapa browser tidak mengizinkan cetak otomatis dari file PDF. Setelah terbuka, tekan <b>Ctrl/Cmd + P</b>.
  </div>
</div>

<script>
(function(){

  // Salin link
  var btnCopy = document.getElementById('btnCopy');
  if(btnCopy){
    btnCopy.addEventListener('click', async function(){
      try{
        await navigator.clipboard.writeText(url);
        if(typeof Swal!=='undefined'){
          Swal.fire({icon:'success', title:'Link disalin', timer:1200, showConfirmButton:false});
        }
      }catch(err){
        if(typeof Swal!=='undefined'){
          Swal.fire({icon:'error', title:'Gagal menyalin', text:String(err)});
        }else{
          alert('Gagal menyalin: ' + err);
        }
      }
    });
  }

  // Buka untuk cetak (hanya membuka tab; print dialog tergantung viewer PDF)
  var btnPrint = document.getElementById('btnPrint');
  if(btnPrint){
    btnPrint.addEventListener('click', function(){
      try {
        var w = window.open(url, '_blank');
        if(!w && typeof Swal!=='undefined'){
          Swal.fire({icon:'info', title:'Popup diblokir', text:'Silakan izinkan pop-up untuk domain ini.'});
        }
      } catch(e){
        if(typeof Swal!=='undefined'){
          Swal.fire({icon:'error', title:'Tidak dapat membuka', text:String(e)});
        }
      }
    });
  }
})();
</script>
@endsection
