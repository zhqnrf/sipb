@php
  /** @var \App\Models\Payment $payment */
  $bill    = $payment->bill;
  $student = $bill->student;
  $fee     = $bill->feeType;

  // Nama sekolah dari controller atau fallback
  $school  = $school_name ?? config('app.name', 'SIPB');

  // (Opsional) alamat & kontak sekolah — bisa kamu kirim dari controller juga
  $school_addr = $school_addr ?? 'Jl. Pendidikan No. 1, Kota — Telp. (021) 123456';
  $school_note = $school_note ?? 'Email: info@sekolah.sch.id';

  // Nama petugas (dari sesi)
  $staffName = session('staff_name') ?: (session('role') ? strtoupper(session('role')) : 'PETUGAS');

  // Logo → base64 (opsional)
  $logoBase64 = null;
  $logoPath   = public_path('assets/img/school-logo.png');
  if (file_exists($logoPath)) {
      $logoBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath));
  }

  // Format angka
  function rupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

  // Tanggal tampil
  $tanggalBayar = $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : date('d/m/Y H:i');

  // Sisa
  $sisa = max(0, (int)$bill->amount - (int)$bill->paid_amount);
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kwitansi {{ $payment->receipt_no }}</title>
<style>
  /* ====== Layout dasar ====== */
  @page { margin: 22px; }
  html, body{ padding:0; margin:0; }
  body{
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    font-size:12px; color:#0f172a; line-height:1.35;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  .wrap{ max-width: 800px; margin:0 auto; }

  /* ====== Header institusi ====== */
  .head{
    display: table; width:100%; table-layout: fixed; margin-bottom:10px;
  }
  .head .col{ display: table-cell; vertical-align: middle; }
  .logo{
    width:68px; height:68px; object-fit: contain; border:1px solid #cbd5e1; border-radius:8px; padding:4px;
  }
  .inst{
    text-align:center;
  }
  .inst .nm{ font-weight:800; font-size:18px; letter-spacing:.4px; text-transform:uppercase; }
  .inst .info{ color:#475569; font-size:11px; margin-top:2px; }
  .doc{
    text-align:right;
  }
  .doc .title{
    display:inline-block; padding:6px 10px; border:1px solid #0f172a; font-weight:800;
    letter-spacing:.3px; border-radius:6px; background:#f8fafc;
  }

  .divider{ border-top:2px solid #0f172a; margin:8px 0 12px; }

  /* ====== Blok No & Tanggal ====== */
  .meta-top{
    display: table; width:100%; table-layout: fixed; margin: 8px 0 10px;
  }
  .meta-top .col{ display: table-cell; vertical-align: top; }
  .box{
    border:1px solid #cbd5e1; border-radius:8px; padding:8px; background:#fff;
  }
  .lbl{ color:#334155; font-size:11px; }
  .val{ font-weight:700; }

  /* ====== Tabel identitas ====== */
  .meta-table{ width:100%; border-collapse: collapse; margin-top:4px; }
  .meta-table td{ padding:5px 6px; vertical-align:top; border-bottom: 1px dashed #e5e7eb; }
  .meta-table td.k{ width:30%; color:#334155; }
  .meta-table td.s{ width:2%; color:#334155; }

  /* ====== Tabel rincian pembayaran ====== */
  .table{ width:100%; border-collapse: collapse; margin-top:12px; }
  .table th,.table td{ border:1px solid #cbd5e1; padding:8px; }
  .table th{
    background:#eef2ff; font-weight:700; color:#0f172a;
  }
  .money{ font-weight:800; }
  .money.total{ color:#1d4ed8; }
  .money.paid{ color:#16a34a; }
  .money.remaining{ color:#b91c1c; }

  /* ====== Tanda tangan ====== */
  .sign-wrap{
    display: table; width:100%; table-layout: fixed; margin-top:16px;
  }
  .sign-col{ display: table-cell; vertical-align: top; }
  .sign-box{ text-align:right; }
  .sign-line{ margin-top:40px; font-weight:700; }
  .note{ font-size:11px; color:#64748b; }

  /* ====== Footer catatan ====== */
  .foot-note{
    margin-top:12px; font-size:11px; color:#475569;
    padding:8px; border:1px dashed #cbd5e1; border-radius:8px; background:#f8fafc;
  }

  /* ====== Watermark opsional ====== */
  .watermark{
    position:fixed; top:40%; left:50%; transform:translate(-50%, -50%);
    font-size:54px; color:rgba(15,23,42,.06); font-weight:900; z-index:-1; white-space:nowrap;
  }

  /* Cetak */
  @media print{
    .no-print{ display:none !important; }
    .wrap{ max-width:none; }
  }
</style>
</head>
<body>
<div class="wrapperin" style="margin: 30px;">
  <div class="wrap">

  {{-- Watermark (opsional, boleh dihapus jika tidak perlu) --}}
  <div class="watermark">SIPB</div>

  {{-- Header institusi --}}
  <div class="head">
    <div class="col" style="width:84px;">
      @if($logoBase64)
        <img class="logo" src="{{ $logoBase64 }}" alt="Logo">
      @endif
    </div>
    <div class="col inst">
      <div class="nm">KWITANSI PEMBAYARAN <br>  {{ $school }}</div>
      <div class="info">{{ $school_addr }}</div>
      @if(!empty($school_note))
        <div class="info">{{ $school_note }}</div>
      @endif
    </div>
    {{-- <div class="col doc" style="width:180px;">
      <div class="title">KWITANSI PEMBAYARAN</div>
    </div> --}}
  </div>

  <div class="divider"></div>

  {{-- Blok nomor & tanggal --}}
  <div class="meta-top">
    <div class="col">
      <div class="box">
        <div class="lbl">Nomor Kwitansi</div>
        <div class="val">{{ $payment->receipt_no }}</div>
      </div>
    </div>
    <div class="col" style="width:16px;"></div>
    <div class="col" style="width:240px;">
      <div class="box">
        <div class="lbl">Tanggal Pembayaran</div>
        <div class="val">{{ $tanggalBayar }}</div>
      </div>
    </div>
  </div>

  {{-- Identitas & pembayaran singkat --}}
  <table class="meta-table">
    <tr>
      <td class="k">Nama Siswa</td><td class="s">:</td>
      <td class="v"><b>{{ $student->name }}</b> ({{ $student->nis }})</td>
    </tr>
    <tr>
      <td class="k">Kelas / Rombel</td><td class="s">:</td>
      <td class="v">{{ $student->kelas }} {{ $student->rombel }}</td>
    </tr>
    <tr>
      <td class="k">Jenis Tagihan</td><td class="s">:</td>
      <td class="v">{{ $fee->name }}</td>
    </tr>
    <tr>
      <td class="k">Periode</td><td class="s">:</td>
      <td class="v">{{ $bill->period }}</td>
    </tr>
    <tr>
      <td class="k">Nominal Dibayar</td><td class="s">:</td>
      <td class="v money">{{ rupiah($payment->amount) }}</td>
    </tr>
  </table>

  {{-- Rincian status tagihan --}}
  <table class="table">
    <thead>
      <tr>
        <th>Tagihan Total</th>
        <th>Sudah Dibayar (s.d. transaksi ini)</th>
        <th>Sisa</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="money total">{{ rupiah($bill->amount) }}</td>
        <td class="money paid">{{ rupiah($bill->paid_amount) }}</td>
        <td class="money remaining">{{ rupiah($sisa) }}</td>
        <td><b>{{ $bill->status }}</b></td>
      </tr>
    </tbody>
  </table>

  {{-- Tanda tangan --}}
  <div class="sign-wrap">
    <div class="sign-col">
      <div class="note">
        Bukti ini sah walaupun tanpa tanda tangan basah karena dicetak dari sistem SIPB.
        Simpan kwitansi ini sebagai bukti pembayaran yang valid.
      </div>
    </div>
    <div class="sign-col sign-box" style="width:280px;">
      <div class="lbl">Petugas</div>
      <div class="sign-line">__________________________</div>
      {{-- <div><b>{{ $staffName }}</b></div> --}}
      <br> <br>
      <div class="note">Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
  </div>

  {{-- Catatan kaki --}}
  <div class="foot-note">
    Apabila terdapat perbedaan data, mohon hubungi Tata Usaha. Sistem akan menyesuaikan status pembayaran secara otomatis
    berdasarkan total transaksi yang tercatat.
  </div>

  {{-- Tombol cetak (hanya saat tampilan di browser) --}}
  {{-- <div class="no-print" style="margin-top:12px; text-align:right;">
    <button onclick="window.print()" style="padding:8px 12px; border:1px solid #cbd5e1; background:#fff; border-radius:8px; cursor:pointer;">
      Cetak
    </button>
  </div> --}}
</div>
</div>

<script>
  // Auto print saat dibuka di tab/browser (abaikan jika dipakai di generator PDF)
  if (typeof window !== 'undefined' && window.print) {
    try { window.print(); } catch(e){}
  }
</script>
</body>
</html>
