@extends('layouts.app')
@section('title','Detail Tagihan')

@section('content')
<style>
  /* Inline style khusus halaman Detail Tagihan */
  .bill-hero{
    border-radius:16px;
    color:#fff;
    background: linear-gradient(135deg, var(--brand, #2563eb), var(--brand2, #60a5fa));
    box-shadow: 0 8px 24px rgba(37,99,235,.25);
  }
  .bill-hero .icon-badge{
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
  .sum-box{ background:#ffffffcc; border:1px solid rgba(37,99,235,.15); border-radius:14px; }
  .sum-box .ttl{font-size:12px;color:#64748b}
  .sum-box .val{font-weight:700}
  .table thead th{ white-space:nowrap; }
  .toolbar .form-control{ max-width:260px; }
</style>

{{-- Header ringkas --}}
<div class="bill-hero p-3 mb-3" data-aos="fade-up">
  <div class="d-flex align-items-start gap-3">
    <div class="icon-badge"><i class="bi bi-receipt"></i></div>
    <div class="flex-fill">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="meta-chip"><i class="bi bi-person-badge"></i> {{ $bill->student->name }}</span>
        <span class="meta-chip"><i class="bi bi-upc-scan"></i> {{ $bill->student->nis }}</span>
        <span class="meta-chip"><i class="bi bi-tags"></i> {{ $bill->feeType->name }}</span>
        <span class="meta-chip"><i class="bi bi-calendar3"></i> {{ $bill->period }}</span>
        <span class="meta-chip">
          <i class="bi bi-clipboard-check"></i>
          <span class="badge @if($bill->status==='Lunas') bg-success @elseif($bill->status==='Sebagian') bg-warning text-dark @else bg-secondary @endif">
            {{ $bill->status }}
          </span>
        </span>
      </div>

      <div class="row g-2 mt-2">
        <div class="col-6 col-md-3">
          <div class="sum-box p-2">
            <div class="ttl">Total Tagihan</div>
            <div style="color: #2563eb;"class="val">Rp {{ number_format($bill->amount,0,',','.') }}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="sum-box p-2 text-success">
            <div class="ttl">Sudah Dibayar</div>
            <div class="val">Rp {{ number_format($bill->paid_amount,0,',','.') }}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="sum-box p-2">
            <div class="ttl">Sisa</div>
            <div style="color: #C70909FF;" class="val">Rp {{ number_format($bill->remaining(),0,',','.') }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Aksi cepat --}}
    <div class="d-none d-lg-flex flex-column gap-2">
      @if(session('role')==='admin' && $bill->status!=='Lunas')
        <a href="{{ route('payments.create',$bill) }}" class="btn btn-light btn-sm">
          <i class="bi bi-cash-coin me-1"></i> Tambah Pembayaran
        </a>
      @endif
      <a href="{{ route('bills.index', ['period'=>$bill->period, 'fee_type_id'=>$bill->fee_type_id]) }}" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </div>
</div>

{{-- Toolbar Tabel --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 toolbar">
  <h6 class="m-0 d-flex align-items-center gap-2">
    <i class="bi bi-clock-history"></i> Riwayat Pembayaran
  </h6>
  <div class="d-flex align-items-center gap-2">
    <input id="searchPay" class="form-control form-control-sm" placeholder="Cari (tgl/nominal/kwitansi)">
    <button class="btn btn-outline-secondary btn-sm" id="btnClearSearch" type="button" title="Bersihkan">
      <i class="bi bi-x-lg"></i>
    </button>
    <button class="btn btn-outline-primary btn-sm" onclick="exportTableToXLSX('tblPayments','riwayat-pembayaran.xlsx')">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> 
    </button>
  </div>
</div>

{{-- Tabel Riwayat --}}
<div class="table-responsive">
  <table class="table table-striped align-middle" id="tblPayments">
    <thead>
      <tr>
        <th><i class="bi bi-calendar-date"></i> Tanggal</th>
        <th><i class="bi bi-cash"></i> Nominal</th>
        <th><i class="bi bi-upc"></i> No Kwitansi</th>
        <th width="110">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($bill->payments as $p)
        <tr>
          <td class="cell-date">{{ $p->paid_at->format('Y-m-d H:i') }}</td>
          <td class="cell-amt" data-num="{{ $p->amount }}">Rp {{ number_format($p->amount,0,',','.') }}</td>
          <td class="cell-receipt">
            <a href="{{ route('receipts.show',$p) }}" class="link-primary">{{ $p->receipt_no }}</a>
          </td>
         <td>
  <button class="btn btn-outline-secondary btn-sm btnCopy" data-copy="{{ $p->receipt_no }}" title="Salin No Kwitansi">
    <i class="bi bi-clipboard"></i>
  </button>
  <a class="btn btn-outline-primary btn-sm" href="{{ route('receipts.show',$p) }}" title="Lihat Kwitansi">
    <i class="bi bi-file-earmark-text"></i>
  </a>

  @if(session('role')==='admin')
  <form action="{{ route('payments.destroy',$p) }}" method="post" class="d-inline frmDel" data-amt="{{ number_format($p->amount,0,',','.') }}" data-receipt="{{ $p->receipt_no }}">
    @csrf
    @method('delete')
    <button type="button" class="btn btn-outline-danger btn-sm btnDel mt-2" title="Hapus Pembayaran">
      <i class="bi bi-trash"></i>
    </button>
  </form>
  @endif
</td>

        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted">Belum ada pembayaran</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- JS: Search realtime + copy kwitansi --}}
<script>
(function(){
  // ===== Realtime search
  var input = document.getElementById('searchPay');
  var btnClear = document.getElementById('btnClearSearch');
  var tbl = document.getElementById('tblPayments');
  if(input && tbl){
    var timer=null;
    function filterNow(){
      var q = (input.value || '').toLowerCase().trim();
      var rows = tbl.tBodies[0]?.rows || [];
      for(var i=0;i<rows.length;i++){
        var r = rows[i];
        var text = (r.innerText || r.textContent || '').toLowerCase();
        r.style.display = text.indexOf(q) >= 0 ? '' : 'none';
      }
    }
    input.addEventListener('input', function(){
      clearTimeout(timer); timer=setTimeout(filterNow, 100);
    });
    btnClear && btnClear.addEventListener('click', function(){
      input.value=''; filterNow(); input.focus();
    });
    filterNow();
  }

  // ===== Copy no kwitansi
  document.querySelectorAll('.btnCopy').forEach(function(btn){
    btn.addEventListener('click', async function(){
      var text = btn.getAttribute('data-copy') || '';
      try{
        await navigator.clipboard.writeText(text);
        if(typeof Swal!=='undefined'){
          Swal.fire({
            icon:'success', title:'Disalin',
            text:'No kwitansi telah disalin: '+text,
            timer:1200, showConfirmButton:false
          });
        }
      }catch(e){
        if(typeof Swal!=='undefined'){
          Swal.fire({ icon:'error', title:'Gagal menyalin', text:String(e) });
        }
      }
    });
  });
 document.querySelectorAll('.frmDel .btnDel').forEach(function(btn){
    btn.addEventListener('click', function(){
      var frm = btn.closest('form');
      if(!frm) return;
      var nominal  = frm.getAttribute('data-amt') || '0';
      var receipt  = frm.getAttribute('data-receipt') || '-';

      if (typeof Swal === 'undefined') {
        if (confirm('Hapus pembayaran '+nominal+' (Kw: '+receipt+') ?')) frm.submit();
        return;
      }

      Swal.fire({
        icon: 'warning',
        title: 'Hapus Pembayaran?',
        html: '<div class="text-start">'+
              '<div><i class="bi bi-upc"></i> Kwitansi: <b>'+ receipt +'</b></div>'+
              '<div><i class="bi bi-cash"></i> Nominal: <b>Rp '+ nominal +'</b></div>'+
              '<div class="text-danger mt-2"><i class="bi bi-exclamation-triangle"></i> Tindakan ini akan mengurangi total terbayar tagihan.</div>'+
              '</div>',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-outline-secondary me-3' },
        buttonsStyling: false,
      }).then(function(res){
        if (res.isConfirmed) frm.submit();
      });
    });
  });
})();
</script>
@endsection
