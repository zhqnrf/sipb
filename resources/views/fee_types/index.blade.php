@extends('layouts.app')
@section('title','Jenis Tagihan')

@section('content')

{{-- Toolbar: search pendek + Import/Export + Tambah --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <input id="generalSearch"
           class="form-control form-control-sm"
           style="max-width:260px"
           placeholder="Cari cepat...">
    <button class="btn btn-outline-secondary btn-sm" id="btnClearSearch" title="Bersihkan">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="d-flex align-items-center gap-2">
    {{-- Import (selalu tampil; validasi izin di server) --}}
    <input type="file" id="fileImport" accept=".xlsx,.xls,.csv" class="d-none">
    <button class="btn btn-outline-primary btn-sm" id="btnImport" type="button" title="Import Excel/CSV">
      <i class="bi bi-file-earmark-arrow-up me-1"></i> Import
    </button>

    {{-- Export --}}
    <button class="btn btn-outline-primary btn-sm" type="button"
            onclick="exportTableToXLSX('tblFeeTypes','jenis-tagihan.xlsx')">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
    </button>

    {{-- Tambah (tetap ada) --}}
    <a href="{{ route('fee-types.create') }}" class="btn btn-brand btn-sm">
      <i class="bi bi-plus-lg me-1"></i> Tambah
    </a>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle" id="tblFeeTypes">
    <thead>
      <tr>
        <th style="min-width:200px">Nama</th>
        <th>Deskripsi</th>
        <th width="160">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr>
        <td class="cell-name">{{ $it->name }}</td>
        <td class="cell-desc">{{ $it->description }}</td>
        <td class="text-nowrap">
          <a class="btn btn-sm btn-outline-primary" href="{{ route('fee-types.edit',$it) }}">
            <i class="bi bi-pencil-square"></i> Edit
          </a>
          <form action="{{ route('fee-types.destroy',$it) }}" method="post" class="d-inline"
                onsubmit="return confirm('Hapus jenis ini?')">
            @csrf @method('delete')
            <button class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash"></i> Hapus
            </button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($items,'links'))
  <div class="mt-3">
    {{ $items->withQueryString()->links() }}
  </div>
@endif

{{-- Tema pagination + JS realtime search & import --}}
<style>
  /* Pagination sesuai tema biru */
  .pagination { --bs-pagination-color: var(--brand); --bs-pagination-hover-color: #0b5ed7; }
  .page-link { border-radius: 10px; }
  .page-item.active .page-link{
    background: linear-gradient(135deg, var(--brand), var(--brand2));
    border-color: rgba(37,99,235,.3);
    color:#fff;
    box-shadow: 0 6px 16px rgba(37,99,235,.25);
  }
  .page-link:focus{ box-shadow: 0 0 0 .16rem rgba(37,99,235,.25); }
</style>

<script>
  // ===== Realtime search (dengan debounce ringan) =====
  (function(){
    var input = document.getElementById('generalSearch');
    var btnClear = document.getElementById('btnClearSearch');
    var table = document.getElementById('tblFeeTypes');
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

  // ===== Import via SheetJS (client) -> POST JSON ke server =====
  (function(){
    var btnImport = document.getElementById('btnImport');
    var fileInput = document.getElementById('fileImport');
    if(!btnImport || !fileInput) return;

    btnImport.addEventListener('click', function(){ fileInput.click(); });

    fileInput.addEventListener('change', function(){
      var f = this.files && this.files[0];
      if(!f){ return; }
      var reader = new FileReader();
      reader.onload = function(e){
        try{
          if (typeof XLSX === 'undefined') {
            throw new Error('Library SheetJS (XLSX) belum termuat.');
          }
          var data = new Uint8Array(e.target.result);
          var wb = XLSX.read(data, {type:'array'});
          var ws = wb.Sheets[wb.SheetNames[0]];
          var json = XLSX.utils.sheet_to_json(ws, {defval:''});

          // Normalisasi kolom
          var items = json.map(function(row){
            var name = row.name || row.Nama || row.nama || '';
            var description = row.description || row.Deskripsi || row.deskripsi || '';
            return { name: String(name).trim(), description: String(description).trim() };
          }).filter(function(x){ return x.name.length > 0; });

          if(items.length === 0){
            Swal.fire({icon:'warning', title:'Tidak ada data', text:'Pastikan ada kolom "name/Nama".'});
            return;
          }

          Swal.fire({
            icon:'question',
            title:'Import '+items.length+' baris?',
            text:'Data dengan nama sama akan diperbarui.',
            showCancelButton:true,
            confirmButtonText:'Import',
            cancelButtonText:'Batal',
            customClass:{ confirmButton:'btn btn-brand', cancelButton:'btn btn-outline-secondary' },
            buttonsStyling:false
          }).then(function(res){
            if(!res.isConfirmed) return;

            fetch("{{ route('fee-types.import') }}", {
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
