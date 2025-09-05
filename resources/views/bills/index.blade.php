@extends('layouts.app')
@section('title','Daftar Tagihan')

@section('content')
<style>
  /* ====== Inline style khusus halaman Daftar Tagihan ====== */
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

  .toolbar .form-control{max-width:260px}
  .toolbar .btn{border-radius:10px}

  .table thead th{ white-space:nowrap; background:#f8fbff; border-bottom:1px solid rgba(37,99,235,.15);}
  .table tbody tr:hover{ background: rgba(96,165,250,.08); }
  .badge-status{
    border-radius:999px; padding:.35rem .6rem; font-weight:700; letter-spacing:.2px;
    box-shadow: 0 4px 10px rgba(0,0,0,.06);
  }

  .cell-amount .money,
  .cell-paid .money{ font-weight:700; }
  .cell-amount .money{ color:#2563eb; }
  .cell-paid .money{ color:#16a34a; }

  .card-lite-2{ background:#ffffffcc; border:1px solid rgba(37,99,235,.12); border-radius:16px; }
</style>

{{-- ===== Header / Context ===== --}}
<div class="hero p-3 mb-3" data-aos="fade-up">
  <div class="d-flex align-items-start gap-3">
    <div class="icon-badge"><i class="bi bi-receipt"></i></div>
    <div class="flex-fill">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="meta-chip"><i class="bi bi-calendar3"></i> Periode aktif: <b>{{ $period }}</b></span>
        <span class="meta-chip"><i class="bi bi-tags"></i> Filter jenis tersedia</span>
      </div>
      <div class="text-white-75 small mt-1">
        Gunakan filter server-side untuk periode/jenis; lalu manfaatkan pencarian cepat & impor/ekspor di toolbar.
      </div>
    </div>
    <div class="d-none d-lg-flex">
      <a href="{{ route('bills.generate.form') }}" class="btn btn-light btn-sm">
        <i class="bi bi-ui-checks-grid me-1"></i> Generate Tagihan
      </a>
    </div>
  </div>
</div>

{{-- ===== Filter server-side (tetap ada) ===== --}}
<form class="row g-2 mb-3 card-lite-2 p-3" data-aos="fade-up" data-aos-delay="40">
  <div class="col-md-3">
    <label class="form-label"><i class="bi bi-calendar2-week me-1"></i> Periode</label>
    <input type="month" name="period" value="{{ $period }}" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label"><i class="bi bi-tag me-1"></i> Jenis</label>
    <select name="fee_type_id" class="form-select">
      <option value="">-- Semua Jenis --</option>
     @foreach($feeTypes as $f)
  <option value="{{ $f->id }}">{{ $f->name }}</option>
@endforeach

    </select>
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <button class="btn btn-brand w-100">
      <i class="bi bi-filter-circle me-1"></i> Filter
    </button>
  </div>
</form>

{{-- ===== Toolbar atas: search + Import/Export ===== --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 toolbar" data-aos="fade-up" data-aos-delay="60">
  <div class="d-flex align-items-center gap-2">
    <input id="generalSearch" class="form-control form-control-sm" placeholder="Cari cepat...">
    <button class="btn btn-outline-secondary btn-sm" id="btnClearSearch" type="button" title="Bersihkan">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="d-flex align-items-center gap-2">
    {{-- Import (Excel/CSV) --}}
    <input type="file" id="fileImport" accept=".xlsx,.xls,.csv" class="d-none">
    <button class="btn btn-outline-primary btn-sm" id="btnImport" type="button" title="Import Excel/CSV">
      <i class="bi bi-file-earmark-arrow-up me-1"></i> Import
    </button>

    {{-- Export (Excel) --}}
    <button class="btn btn-outline-primary btn-sm" type="button"
            onclick="exportTableToXLSX('tblBills','bills.xlsx')">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
    </button>
  </div>
</div>

<div class="table-responsive" data-aos="fade-up" data-aos-delay="80">
  <table class="table table-bordered align-middle" id="tblBills">
    <thead>
      <tr>
        <th><i class="bi bi-person-badge"></i> Siswa</th>
        <th><i class="bi bi-tags"></i> Jenis</th>
        <th><i class="bi bi-calendar3"></i> Periode</th>
        <th><i class="bi bi-cash-coin"></i> Total</th>
        <th><i class="bi bi-wallet2"></i> Terbayar</th>
        <th><i class="bi bi-clipboard-check"></i> Status</th>
        <th width="190">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($items as $b)
      <tr>
        <td class="cell-student">{{ $b->student->name }}</td>
        <td class="cell-fee">{{ $b->feeType->name }}</td>
        <td class="cell-period">{{ $b->period }}</td>
        <td class="cell-amount" data-raw="{{ $b->amount }}">
          <span class="money">Rp {{ number_format($b->amount,0,',','.') }}</span>
        </td>
        <td class="cell-paid" data-raw="{{ $b->paid_amount }}">
          <span class="money">Rp {{ number_format($b->paid_amount,0,',','.') }}</span>
        </td>
        <td>
          @php
            $cls = $b->status==='Lunas' ? 'bg-success' : ($b->status==='Sebagian' ? 'bg-warning text-dark' : 'bg-secondary');
          @endphp
          <span class="badge {{ $cls }} badge-status">{{ $b->status }}</span>
        </td>
        <td class="text-nowrap">
          <a class="btn btn-sm btn-outline-primary" href="{{ route('bills.show',$b) }}">
            <i class="bi bi-clock-history"></i> Riwayat
          </a>
          @if(session('role')==='admin' && $b->status!=='Lunas')
            <a class="btn btn-sm btn-brand" href="{{ route('payments.create',$b) }}">
              <i class="bi bi-cash-coin"></i> Bayar
            </a>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

@if(method_exists($items,'links'))
  <div class="mt-3" data-aos="fade-up" data-aos-delay="100">
    {{-- gunakan template kustom kamu: pagination::sipb --}}
    {{ $items->withQueryString()->onEachSide(1)->links('pagination::sipb') }}
  </div>
@endif

{{-- ===== JS: search realtime + import SheetJS + export (sudah ada di app.js) ===== --}}
<script>
  // ===== Realtime search singkat (debounce) =====
  (function(){
    var input = document.getElementById('generalSearch');
    var btnClear = document.getElementById('btnClearSearch');
    var table = document.getElementById('tblBills');
    if(!input || !table) return;

    var timer = null;
    function filterNow(){
      var q = (input.value || '').toLowerCase().trim();
      var rows = table.tBodies[0]?.rows || [];
      for(var i=0;i<rows.length;i++){
        var r = rows[i];
        var text = (r.innerText || r.textContent || '').toLowerCase();
        r.style.display = text.indexOf(q) >= 0 ? '' : 'none';
      }
    }
    input.addEventListener('input', function(){
      clearTimeout(timer);
      timer = setTimeout(filterNow, 120);
    });
    btnClear && btnClear.addEventListener('click', function(){
      input.value = ''; filterNow(); input.focus();
    });
  })();

  // ===== Import Bills via SheetJS -> POST JSON ke server =====
  // Kolom minimal: NIS, fee_type (nama), period (YYYY-MM), amount (angka)
  (function(){
    var btnImport = document.getElementById('btnImport');
    var fileInput = document.getElementById('fileImport');
    if(!btnImport || !fileInput) return;

    btnImport.addEventListener('click', function(){ fileInput.click(); });

    fileInput.addEventListener('change', function(){
      var f = this.files && this.files[0];
      if(!f) return;

      var reader = new FileReader();
      reader.onload = function(e){
        try{
          if (typeof XLSX === 'undefined') throw new Error('Library SheetJS (XLSX) belum termuat.');
          var data = new Uint8Array(e.target.result);
          var wb = XLSX.read(data, {type:'array'});
          var ws = wb.Sheets[wb.SheetNames[0]];
          var json = XLSX.utils.sheet_to_json(ws, {defval:''});

          function pick(row, keys) {
            for (var i=0;i<keys.length;i++) {
              var k = keys[i];
              if (row.hasOwnProperty(k)) return row[k];
              var alt = Object.keys(row).find(function(x){ return x.toLowerCase() === k.toLowerCase(); });
              if (alt) return row[alt];
            }
            return '';
          }

          var items = json.map(function(row){
            var nis   = String(pick(row, ['nis','NIS'])).trim();
            var ft    = String(pick(row, ['fee_type','fee','jenis','fee_type_name'])).trim();
            var period= String(pick(row, ['period','periode'])).trim();
            var amount= Number(String(pick(row, ['amount','total'])).replace(/[^\d.-]/g,'')) || 0;
            var paid  = String(pick(row, ['paid_amount','terbayar'])).trim();
            var paid_amount = paid ? (Number(paid.replace(/[^\d.-]/g,'')) || 0) : null;

            return { nis: nis, fee_type_name: ft, period: period, amount: amount, paid_amount: paid_amount };
          }).filter(function(x){ return x.nis && x.fee_type_name && x.period && x.amount > 0; });

          if(items.length === 0){
            Swal.fire({icon:'warning', title:'Tidak ada data valid',
                       text:'Pastikan kolom: NIS, fee_type (nama), period (YYYY-MM), amount.'});
            return;
          }

          Swal.fire({
            icon:'question',
            title:'Import '+items.length+' baris?',
            text:'Bill yang sudah ada (NIS + Jenis + Periode) akan diperbarui.',
            showCancelButton:true,
            confirmButtonText:'Import',
            cancelButtonText:'Batal',
            customClass:{ confirmButton:'btn btn-brand', cancelButton:'btn btn-outline-secondary' },
            buttonsStyling:false
          }).then(function(res){
            if(!res.isConfirmed) return;

            fetch("{{ route('bills.import') }}", {
              method:'POST',
              headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
              },
              body: JSON.stringify({ items: items })
            })
            .then(function(r){
              if(r.headers.get('content-type')?.includes('application/json')) return r.json();
              return r.text().then(function(t){ throw new Error(t || 'Respon tidak valid'); });
            })
            .then(function(resp){
              if(resp && resp.ok){
                Swal.fire({icon:'success', title:'Import sukses', text: resp.message || 'Data tersimpan.'})
                  .then(function(){ location.reload(); });
              }else{
                throw new Error(resp && resp.message ? resp.message : 'Gagal menyimpan.');
              }
            })
            .catch(function(err){
              Swal.fire({icon:'error', title:'Gagal import', text: err.message || 'Terjadi kesalahan.'});
            });
          });

        }catch(err){
          Swal.fire({icon:'error', title:'File tidak valid', text: err.message || 'Periksa format file.'});
        }finally{
          fileInput.value = '';
        }
      };
      reader.readAsArrayBuffer(f);
    });
  })();
</script>
@endsection
