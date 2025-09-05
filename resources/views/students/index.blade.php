@extends('layouts.app')
@section('title','Siswa')

@section('content')

{{-- ===== Toolbar: search realtime (pendek) + Import/Export + Tambah ===== --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <input id="generalSearch" class="form-control form-control-sm" style="max-width:260px"
           placeholder="Cari cepat... (NIS/Nama/Kelas/Rombel)">
    <button class="btn btn-outline-secondary btn-sm" id="btnClearSearch" type="button" title="Bersihkan">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="d-flex align-items-center gap-2">
    {{-- Import (selalu tampil; cek otorisasi di server) --}}
    <input type="file" id="fileImport" accept=".xlsx,.xls,.csv" class="d-none">
    <button class="btn btn-outline-primary btn-sm" id="btnImport" type="button" title="Import Excel/CSV">
      <i class="bi bi-file-earmark-arrow-up me-1"></i> Import
    </button>

    {{-- Export --}}
    <button class="btn btn-outline-primary btn-sm" type="button"
            onclick="exportTableToXLSX('tblSiswa','siswa.xlsx')">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
    </button>

    {{-- Tambah (tetap ada) --}}
    <a href="{{ route('students.create') }}" class="btn btn-brand btn-sm">
      <i class="bi bi-plus-lg me-1"></i> Tambah
    </a>
  </div>
</div>

{{-- ===== (opsional) filter server-side lama, disembunyikan karena sudah ada realtime ===== --}}
<form class="d-none">
  <div class="d-flex gap-2">
    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama/NIS">
    <input class="form-control" name="kelas" value="{{ request('kelas') }}" placeholder="Kelas">
    <button class="btn btn-brand">Cari</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle" id="tblSiswa">
    <thead>
      <tr>
        <th style="min-width:120px">NIS</th>
        <th style="min-width:220px">Nama</th>
        <th>Kelas</th>
        <th>Rombel</th>
        <th width="140">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr>
        <td class="cell-nis">{{ $it->nis }}</td>
        <td class="cell-name">{{ $it->name }}</td>
        <td class="cell-kelas">{{ $it->kelas }}</td>
        <td class="cell-rombel">{{ $it->rombel }}</td>
        <td class="text-nowrap">
          @if(session('role')==='admin')
            <a class="btn btn-sm btn-outline-primary" href="{{ route('students.edit',$it) }}">
              <i class="bi bi-pencil-square"></i> Edit
            </a>
            <form action="{{ route('students.destroy',$it) }}" method="post" class="d-inline"
                  onsubmit="return confirm('Hapus siswa ini?')">
              @csrf @method('delete')
              <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i> Hapus
              </button>
            </form>
          @else
            <span class="text-muted">Read-only</span>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($items,'links'))
  <div class="mt-3">
    {{-- gunakan pagination custom sipb --}}
    {{ $items->withQueryString()->onEachSide(1)->links('pagination::sipb') }}
  </div>
@endif

{{-- ===== JS: realtime search + import siswa ===== --}}
<script>
  // Realtime search (debounce)
  (function(){
    var input = document.getElementById('generalSearch');
    var btnClear = document.getElementById('btnClearSearch');
    var table = document.getElementById('tblSiswa');
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
      clearTimeout(timer); timer = setTimeout(filterNow, 120);
    });
    btnClear && btnClear.addEventListener('click', function(){
      input.value = ''; filterNow(); input.focus();
    });
  })();

  // Import Siswa via SheetJS -> POST JSON ke server
  // Header yang didukung: nis/NIS, name/Nama, kelas/Kelas, rombel/Rombel
  (function(){
    var btnImport = document.getElementById('btnImport');
    var fileInput = document.getElementById('fileImport');
    if(!btnImport || !fileInput) return;

    btnImport.addEventListener('click', function(){ fileInput.click(); });

    function pick(row, keys){
      for (var i=0;i<keys.length;i++) {
        var k = keys[i];
        if (row.hasOwnProperty(k)) return row[k];
        var alt = Object.keys(row).find(function(x){ return x.toLowerCase() === k.toLowerCase(); });
        if (alt) return row[alt];
      }
      return '';
    }

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

          var items = json.map(function(row){
            return {
              nis:    String(pick(row, ['nis','NIS'])).trim(),
              name:   String(pick(row, ['name','Nama','nama'])).trim(),
              kelas:  String(pick(row, ['kelas','Kelas'])).trim(),
              rombel: String(pick(row, ['rombel','Rombel'])).trim()
            };
          }).filter(function(x){ return x.nis && x.name; });

          if(items.length === 0){
            Swal.fire({icon:'warning', title:'Tidak ada data valid', text:'Minimal kolom NIS & Name harus ada.'});
            return;
          }

          Swal.fire({
            icon:'question',
            title:'Import '+items.length+' siswa?',
            text:'Siswa dengan NIS sama akan diperbarui.',
            showCancelButton:true,
            confirmButtonText:'Import',
            cancelButtonText:'Batal',
            customClass:{ confirmButton:'btn btn-brand', cancelButton:'btn btn-outline-secondary' },
            buttonsStyling:false
          }).then(function(res){
            if(!res.isConfirmed) return;

            fetch("{{ route('students.import') }}", {
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
