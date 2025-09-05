@extends('layouts.app')
@section('title','Input Pembayaran/Angsuran')

@section('content')
<style>
  /* Inline style khusus halaman ini */
  .bill-hero{
    border-radius:16px;
    color:#fff;
    background: linear-gradient(135deg, var(--brand, #2563eb), var(--brand2, #60a5fa));
    box-shadow: 0 8px 24px rgba(37,99,235,.25);
  }
  .bill-hero .icon-badge{
    width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.18);
    display:flex;align-items:center;justify-content:center;font-size:18px;
    border:1px solid rgba(255,255,255,.35);
  }
  .meta-chip{
    display:inline-flex;align-items:center;gap:.4rem;
    background:#ffffffcc;border:1px solid rgba(37,99,235,.25);
    color:#0f172a;border-radius:999px;padding:.25rem .6rem;font-weight:600;
  }
  .meta-chip i{font-size:15px}
  .sum-box{ background:#ffffffcc; border:1px solid rgba(37,99,235,.15); border-radius:14px; }
  .sum-box .ttl{font-size:12px;color:#64748b}
  .sum-box .val{font-weight:700;}
  .form-hint{font-size:.85rem;color:#64748b}
</style>

{{-- Header ringkas --}}
<div class="bill-hero p-3 mb-3" data-aos="fade-up">
  <div class="d-flex align-items-start gap-3">
    <div class="icon-badge"><i class="bi bi-cash-coin"></i></div>
    <div class="flex-fill">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="meta-chip"><i class="bi bi-person-badge"></i> {{ $bill->student->name }}</span>
        <span class="meta-chip"><i class="bi bi-upc-scan"></i> {{ $bill->student->nis }}</span>
        <span class="meta-chip"><i class="bi bi-tags"></i> {{ $bill->feeType->name }}</span>
        <span class="meta-chip"><i class="bi bi-calendar3"></i> {{ $bill->period }}</span>
      </div>

      <div class="row g-2 mt-2">
        <div class="col-6 col-md-3">
          <div class="sum-box p-2 text-dark">
            <div class="ttl">Total Tagihan</div>
            <div class="val">Rp {{ number_format($bill->amount,0,',','.') }}</div>
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
            <div class="val" style="color: #C70909FF" id="sisaText">Rp {{ number_format($bill->remaining(),0,',','.') }}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="sum-box p-2">
            <div class="ttl">Status</div>
            <div class="val">
              <span class="badge @if($bill->status==='Lunas') bg-success @elseif($bill->status==='Sebagian') bg-warning text-dark @else bg-secondary @endif">
                {{ $bill->status }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    {{-- Optional: Aksi cepat --}}
    <div class="d-none d-lg-flex flex-column gap-2">
      <a href="{{ route('bills.show',$bill) }}" class="btn btn-light btn-sm">
        <i class="bi bi-clock-history me-1"></i> Riwayat
      </a>
    </div>
  </div>
</div>

{{-- Form pembayaran --}}
<form method="post" class="row g-3" id="frmPay">@csrf
  <div class="col-md-6" data-aos="fade-up" data-aos-delay="50">
    <label class="form-label">Nominal Bayar</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="text"
             inputmode="numeric"
             autocomplete="off"
             id="amountDisplay"
             class="form-control"
             placeholder="Masukkan nominal (contoh: 150000)"
             required>
      <span class="input-group-text"><i class="bi bi-calculator"></i></span>
    </div>
    <div class="form-hint mt-1">
      Tekan angka saja, otomatis jadi format 1.234.567. Maksimal: <b id="maxText">Rp {{ number_format($bill->remaining(),0,',','.') }}</b>
    </div>
    {{-- Field asli yang akan dikirim (angka murni) --}}
    <input type="hidden" name="amount" id="amountRaw" value="">
  </div>

  <div class="col-md-6 d-flex align-items-end" data-aos="fade-up" data-aos-delay="100">
    <div class="w-100">
      <button class="btn btn-brand w-100">
        <i class="bi bi-printer-fill me-1"></i> Simpan & Cetak Kwitansi
      </button>
      <div class="form-hint mt-2">
        Transaksi akan langsung membuat berkas kwitansi PDF dan membuka tampilan cetak.
      </div>
    </div>
  </div>
</form>

<script>
(function(){
  // Nilai batas
  var MAX = {{ (int) $bill->remaining() }}; // batas maksimum sesuai sisa
  var amountDisplay = document.getElementById('amountDisplay');
  var amountRaw     = document.getElementById('amountRaw');
  var sisaText      = document.getElementById('sisaText');

  // Helper: format ribuan (1.234.567)
  function formatRupiah(num){
    try{
      var n = (num||0);
      return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }catch(e){ return num; }
  }

  // Ambil angka murni dari input display
  function onlyDigits(str){
    return (str || '').replace(/[^\d]/g, '');
  }

  // Update tampilan saat ketik
  function onType(){
    var raw = onlyDigits(amountDisplay.value);
    // hilangkan nol leading
    raw = raw.replace(/^0+/, '');
    if(raw === '') { amountDisplay.value = ''; amountRaw.value=''; return; }

    // Batasi <= MAX
    var val = parseInt(raw,10) || 0;
    if (val > MAX) val = MAX;

    amountDisplay.value = formatRupiah(val);
    amountRaw.value = val;

    // Update teks sisa (preview)
    var newSisa = Math.max(0, ({{ (int)$bill->remaining() }} - val));
    if(sisaText) sisaText.textContent = 'Rp ' + formatRupiah(newSisa);
  }

  // Event listeners
  amountDisplay.addEventListener('input', onType);
  amountDisplay.addEventListener('blur', function(){
    // Normalisasi saat blur
    onType();
  });

  // Prevent non-digit ke input (kecuali control keys)
  amountDisplay.addEventListener('keypress', function(e){
    var ch = e.key || '';
    if(!/[0-9]/.test(ch)) e.preventDefault();
  });

  // Saat submit: pastikan kirim angka murni & validasi max
  var frm = document.getElementById('frmPay');
  frm.addEventListener('submit', function(e){
    onType();
    var val = parseInt(amountRaw.value || '0', 10);
    if(!val || val < 1){
      e.preventDefault();
      if (typeof Swal !== 'undefined'){
        Swal.fire({
          icon: 'warning',
          title: 'Nominal belum diisi',
          text: 'Masukkan jumlah pembayaran yang valid.',
          confirmButtonText: 'OK',
          customClass:{ confirmButton:'btn btn-brand' },
          buttonsStyling:false
        });
      } else { alert('Nominal belum diisi.'); }
      amountDisplay.focus();
      return;
    }
    if(val > MAX){
      e.preventDefault();
      if (typeof Swal !== 'undefined'){
        Swal.fire({
          icon: 'error',
          title: 'Lebih dari sisa',
          text: 'Nominal tidak boleh melebihi sisa tagihan.',
          confirmButtonText: 'OK',
          customClass:{ confirmButton:'btn btn-brand' },
          buttonsStyling:false
        });
      } else { alert('Nominal tidak boleh melebihi sisa tagihan.'); }
      onType(); amountDisplay.focus();
    }
  });
})();
</script>
@endsection
