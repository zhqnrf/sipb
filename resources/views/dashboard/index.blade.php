@extends('layouts.app')
@section('title','Dashboard')

@section('content')

<style>
  /* === Inline style khusus halaman dashboard === */
  .chart-card{ border-radius:16px; background:#ffffffcc; border:1px solid rgba(37,99,235,.15); }
  .chart-box{ width:100%; position:relative; }
  .chart-canvas{ display:block; width:100%; height:280px; } /* responsif: lebar 100%, tinggi tetap (bisa diubah) */

  @media (max-width: 575.98px){
    .chart-canvas{ height:240px; }
  }
</style>

{{-- ====== Filter atas ====== --}}
<form class="row g-2 mb-3">
  <div class="col-md-3">
    <input type="month" name="period" value="{{ $period }}" class="form-control">
  </div>
  <div class="col-md-3">
    <input type="number" name="fee_type_id" value="{{ request('fee_type_id') }}" class="form-control" placeholder="ID Jenis (opsional)">
  </div>
  <div class="col-md-2">
    <button class="btn btn-brand w-100">Filter</button>
  </div>
</form>

{{-- ====== Kartu ringkas ====== --}}
<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="p-3 rounded brand-gradient">
      <div class="small">Total Tagihan</div>
      <div class="fs-5 fw-bold">Rp {{ number_format($total,0,',','.') }}</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="p-3 rounded card-lite">
      <div class="small">Terkumpul</div>
      <div class="fs-5 fw-bold text-success">Rp {{ number_format($paid,0,',','.') }}</div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="p-3 rounded card-lite">
      <div class="small">Lunas</div>
      <div class="fs-5 fw-bold">{{ $lunas }}</div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="p-3 rounded card-lite">
      <div class="small">Sebagian</div>
      <div class="fs-5 fw-bold">{{ $sebagian }}</div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="p-3 rounded card-lite">
      <div class="small">Belum</div>
      <div class="fs-5 fw-bold">{{ $belum }}</div>
    </div>
  </div>
</div>

{{-- ====== Baris Grafik ====== --}}
<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="chart-card p-3 h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="m-0">Komposisi Status</h6>
        <span class="text-muted small">Periode: {{ $period }}</span>
      </div>
      <div class="chart-box">
        <canvas id="chartDonut" class="chart-canvas"></canvas>
      </div>
      <div class="small text-muted mt-2">Lunas / Sebagian / Belum</div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="chart-card p-3 h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="m-0">Terkumpul vs Kebutuhan</h6>
        <span class="text-muted small">Periode: {{ $period }}</span>
      </div>
      <div class="chart-box">
        <canvas id="chartBarTP" class="chart-canvas"></canvas>
      </div>
      <div class="small text-muted mt-2">Perbandingan total tagihan vs total masuk</div>
    </div>
  </div>
</div>

{{-- ====== Grafik Sisa per Kelas (agregasi client-side) ====== --}}
<div class="row g-3 mb-3">
  <div class="col-12">
    <div class="chart-card p-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="m-0">Sisa per Kelas</h6>
        <span class="text-muted small">Dihitung dari tabel di bawah</span>
      </div>
      <div class="chart-box">
        <canvas id="chartKelas" class="chart-canvas"></canvas>
      </div>
    </div>
  </div>
</div>

{{-- ====== Toolbar tabel ====== --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
  <h6 class="m-0">Belum Lunas / Sisa</h6>
  <div class="d-flex align-items-center gap-2">
    <input id="generalSearch" class="form-control form-control-sm" style="max-width:260px"
           placeholder="Cari cepat... (nama/kelas/jenis/periode)">
    <button class="btn btn-outline-secondary btn-sm" id="btnClearSearch" type="button" title="Bersihkan">
      <i class="bi bi-x-lg"></i>
    </button>
    <button class="btn btn-outline-primary btn-sm" onclick="exportTableToXLSX('tblBelum','rekap-belum.xlsx')">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> 
    </button>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-bordered align-middle" id="tblBelum">
    <thead>
      <tr>
        <th>Siswa</th><th>Kelas</th><th>Jenis</th><th>Period</th>
        <th>Total</th><th>Terbayar</th><th>Sisa</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
    @foreach($belumList as $b)
      <tr>
        <td class="cell-name">{{ $b->student->name }}</td>
        <td class="cell-kelas">{{ $b->student->kelas }}</td>
        <td class="cell-jenis">{{ $b->feeType->name }}</td>
        <td class="cell-period">{{ $b->period }}</td>
        <td class="cell-total" data-num="{{ $b->amount }}">Rp {{ number_format($b->amount,0,',','.') }}</td>
        <td class="cell-paid" data-num="{{ $b->paid_amount }}">Rp {{ number_format($b->paid_amount,0,',','.') }}</td>
        <td class="cell-remaining fw-bold" data-num="{{ $b->remaining() }}">Rp {{ number_format($b->remaining(),0,',','.') }}</td>
        <td>
          @if(session('role')==='admin')
            <a href="{{ route('payments.create',$b) }}" class="btn btn-sm btn-brand">Bayar</a>
          @else
            <a href="{{ route('bills.show',$b) }}" class="btn btn-sm btn-outline-primary">Detail</a>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>

@if(method_exists($belumList,'links'))
  <div class="mt-3">
    {{ $belumList->withQueryString()->onEachSide(1)->links('pagination::sipb') }}
  </div>
@endif

{{-- =================== JS Dashboard =================== --}}
<script>
(function(){
  // ====== Data dari server ======
  var DATA = {
    total:  {{ (int)$total }},
    paid:   {{ (int)$paid }},
    lunas:  {{ (int)$lunas }},
    sebagian: {{ (int)$sebagian }},
    belum:  {{ (int)$belum }},
  };

  // ====== Util ======
  function cssVar(name, fallback){ 
    try { return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback; }
    catch(e){ return fallback; }
  }
  var C1 = cssVar('--brand', '#2563eb');   // utama
  var C2 = cssVar('--brand2', '#60a5fa');  // sekunder
  var CTXT = 'rgba(2,6,23,.8)';

  // DPR-aware sizing untuk canvas responsif (lebar 100%)
  function sizeCanvas(cv) {
    if (!cv) return;
    // clientWidth dari CSS (width:100%)
    var displayW = cv.clientWidth || 300;
    var displayH = cv.clientHeight || 220; // ditentukan oleh .chart-canvas (height)
    var ratio = window.devicePixelRatio || 1;
    // set attribute width/height (px) agar tajam di retina
    cv.width  = Math.max(1, Math.floor(displayW * ratio));
    cv.height = Math.max(1, Math.floor(displayH * ratio));
    var ctx = cv.getContext('2d');
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0); // reset scale lalu apply DPR
    return {ctx:ctx, width:displayW, height:displayH};
  }

  function rupiah(n){
    try{ return 'Rp ' + (n||0).toLocaleString('id-ID'); }
    catch(e){ return 'Rp ' + (n||0); }
  }

  // ====== Donut ======
  function drawDonut(cv, series, colors, centerText){
    var sized = sizeCanvas(cv); if(!sized) return;
    var c = sized.ctx, w = sized.width, h = sized.height;
    var cx = w/2, cy = h/2, r = Math.min(w,h)*0.42, inner = r*0.6;
    var total = series.reduce((a,b)=>a+b,0) || 1, start = -Math.PI/2;

    c.clearRect(0,0,w,h);
    series.forEach(function(v,i){
      var ang = (v/total)*Math.PI*2;
      c.beginPath(); c.moveTo(cx,cy);
      c.arc(cx,cy,r,start,start+ang);
      c.closePath();
      c.fillStyle = colors[i] || '#ddd';
      c.fill();
      start += ang;
    });
    // inner cut
    c.globalCompositeOperation = 'destination-out';
    c.beginPath(); c.arc(cx,cy,inner,0,Math.PI*2); c.fill();
    c.globalCompositeOperation = 'source-over';

    if(centerText){
      c.fillStyle = CTXT; c.font = '600 14px system-ui, -apple-system, "Segoe UI", Roboto';
      c.textAlign='center'; c.textBaseline='middle';
      c.fillText(centerText, cx, cy);
    }
  }

  // ====== Bar (2 seri) ======
  function drawBars(cv, labels, valsA, valsB, colors, legend){
    var sized = sizeCanvas(cv); if(!sized) return;
    var c = sized.ctx, w = sized.width, h = sized.height;
    c.clearRect(0,0,w,h);

    var paddingTop=24, paddingBottom=28, left=42, right=12;
    var chartW = w - left - right, chartH = h - paddingTop - paddingBottom;

    var maxV = Math.max(1,
      Math.max.apply(null, valsA||[0]),
      Math.max.apply(null, valsB||[0])
    );
    var n = Math.max(1, labels.length);
    var groupW = chartW / n;
    var barW = Math.min(30, groupW/2.4);

    // Y Axis
    c.strokeStyle = 'rgba(2,6,23,.12)';
    c.lineWidth = 1;
    c.beginPath(); c.moveTo(left, paddingTop); c.lineTo(left, paddingTop+chartH); c.lineTo(w-right, paddingTop+chartH); c.stroke();

    // ticks
    c.fillStyle = 'rgba(2,6,23,.6)'; c.font = '12px system-ui, -apple-system, "Segoe UI", Roboto';
    var ticks = 4;
    for (var i=0;i<=ticks;i++){
      var val = Math.round(maxV * i / ticks);
      var y = paddingTop + chartH - (chartH * i / ticks);
      c.fillText(rupiah(val), 6, y);
      c.strokeStyle = 'rgba(2,6,23,.06)'; c.beginPath(); c.moveTo(left, y); c.lineTo(w-right, y); c.stroke();
    }

    // bars
    for (var i=0;i<n;i++){
      var x0 = left + i*groupW + (groupW/2);
      var vA = valsA[i] || 0, vB = (valsB ? valsB[i] : 0) || 0;
      var yA = paddingTop + chartH - (vA/maxV)*chartH;
      var yB = paddingTop + chartH - (vB/maxV)*chartH;

      c.fillStyle = colors[0]; c.fillRect(x0 - barW - 3, yA, barW, paddingTop+chartH - yA);
      if(valsB){ c.fillStyle = colors[1]; c.fillRect(x0 + 3, yB, barW, paddingTop+chartH - yB); }

      // label bawah
      c.fillStyle = 'rgba(2,6,23,.7)'; c.font='12px system-ui, -apple-system, "Segoe UI", Roboto'; c.textAlign='center';
      c.fillText(labels[i], x0, paddingTop+chartH+16);
    }

    // legend
    if(legend && legend.length){
      c.fillStyle='rgba(2,6,23,.8)'; c.font='12px system-ui, -apple-system, "Segoe UI", Roboto';
      var lx = Math.max(left, w - right - 140), ly = 6;
      c.fillStyle = colors[0]; c.fillRect(lx, ly, 12, 12);
      c.fillStyle = 'rgba(2,6,23,.8)'; c.fillText(legend[0], lx+18, ly+10);
      if(valsB){
        c.fillStyle = colors[1]; c.fillRect(lx, ly+18, 12, 12);
        c.fillStyle = 'rgba(2,6,23,.8)'; c.fillText(legend[1], lx+18, ly+28);
      }
    }
  }

  // ====== Inisialisasi canvas ======
  var cvDonut = document.getElementById('chartDonut');
  var cvBarTP = document.getElementById('chartBarTP');
  var cvKelas = document.getElementById('chartKelas');

  function drawAll(){
    var totalRows = DATA.lunas + DATA.sebagian + DATA.belum;
    var pct = (totalRows>0) ? Math.round((DATA.lunas/totalRows)*100) : 0;
    drawDonut(cvDonut, [DATA.lunas, DATA.sebagian, DATA.belum], [C1, '#facc15', '#94a3b8'], pct+'% Lunas');
    drawBars(cvBarTP, ['Periode'], [DATA.total], [DATA.paid], [C1, C2], ['Total','Terkumpul']);
    aggregateKelasAndDraw(); // bar kelas
  }

  // ====== Search realtime tabel + efek ke chart kelas ======
  (function(){
    var input = document.getElementById('generalSearch');
    var btnClear = document.getElementById('btnClearSearch');
    var tbl = document.getElementById('tblBelum');
    if(!input || !tbl) return;
    var timer=null;

    function filterNow(){
      var q = (input.value || '').toLowerCase().trim();
      var rows = tbl.tBodies[0]?.rows || [];
      for(var i=0;i<rows.length;i++){
        var r = rows[i];
        var text = (r.innerText || r.textContent || '').toLowerCase();
        r.style.display = text.indexOf(q) >= 0 ? '' : 'none';
      }
      aggregateKelasAndDraw();
    }

    input.addEventListener('input', function(){
      clearTimeout(timer); timer=setTimeout(filterNow, 120);
    });
    btnClear && btnClear.addEventListener('click', function(){
      input.value=''; filterNow(); input.focus();
    });

    filterNow();
  })();

  // ====== Grafik Kelas (agregasi dari tabel yang terlihat) ======
  function aggregateKelasAndDraw(){
    if(!cvKelas) return;
    var tbl = document.getElementById('tblBelum');
    var rows = tbl.tBodies[0]?.rows || [];
    var map = {}; // kelas => total sisa
    for(var i=0;i<rows.length;i++){
      var r = rows[i];
      if(r.style.display==='none') continue; // hanya yang lolos filter
      var kelas = (r.querySelector('.cell-kelas')?.innerText || '').trim() || '(Tanpa Kelas)';
      var sisa  = parseInt(r.querySelector('.cell-remaining')?.getAttribute('data-num') || '0', 10);
      map[kelas] = (map[kelas]||0) + sisa;
    }
    var labels=[], vals=[];
    Object.keys(map).sort().forEach(function(k){ labels.push(k); vals.push(map[k]); });

    // Top 7 + Lainnya
    if(labels.length>8){
      var paired = labels.map(function(k,i){ return {k:k,v:vals[i]}; })
                         .sort(function(a,b){ return b.v-a.v; });
      var top7 = paired.slice(0,7), rest = paired.slice(7);
      labels = top7.map(p=>p.k).concat(['Lainnya']);
      vals   = top7.map(p=>p.v).concat([rest.reduce((a,b)=>a+b.v,0)]);
    }

    // Single-series bar
    drawBars(cvKelas, labels, vals, null, [C2], ['Sisa']);
  }

  // ====== Redraw on resize (debounce) ======
  var resizeTimer=null;
  window.addEventListener('resize', function(){
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(drawAll, 120);
  });

  // First draw
  drawAll();
})();
</script>
@endsection
