@extends('layouts.app')
@section('title','Kelas')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <form class="d-flex align-items-center gap-2">
    <input class="form-control form-control-sm" style="max-width:260px" name="q" value="{{ $q }}" placeholder="Cari kelas/rombel">
    <button class="btn btn-brand btn-sm">Cari</button>
  </form>

  <div class="d-flex align-items-center gap-2">
    <input type="file" id="fileImport" accept=".xlsx,.xls,.csv" class="d-none">
    <button class="btn btn-outline-primary btn-sm" id="btnImport" type="button">
      <i class="bi bi-file-earmark-arrow-up me-1"></i> Import
    </button>
    <button class="btn btn-outline-primary btn-sm" type="button"
            onclick="exportTableToXLSX('tblClassrooms','kelas.xlsx')">
      <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
    </button>
    <a href="{{ route('classrooms.create') }}" class="btn btn-brand btn-sm">
      <i class="bi bi-plus-lg me-1"></i> Tambah
    </a>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle" id="tblClassrooms">
    <thead><tr><th>Nama Kelas</th><th>Rombel</th><th width="160">Aksi</th></tr></thead>
    <tbody>
      @forelse($items as $it)
      <tr>
        <td>{{ $it->name }}</td>
        <td>{{ $it->rombel }}</td>
        <td class="text-nowrap">
          <a class="btn btn-sm btn-outline-primary" href="{{ route('classrooms.edit',$it) }}">
            <i class="bi bi-pencil-square"></i> Edit
          </a>
          <form class="d-inline" method="post" action="{{ route('classrooms.destroy',$it) }}"
                onsubmit="return confirm('Hapus kelas ini?')">
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
  <div class="mt-3">{{ $items->withQueryString()->onEachSide(1)->links('pagination::sipb') }}</div>
@endif

<script>
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
            name:   String(pick(row, ['name','Nama','kelas','Kelas'])).trim(),
            rombel: String(pick(row, ['rombel','Rombel'])).trim()
          };
        }).filter(function(x){ return x.name.length > 0; });

        if(items.length === 0){
          Swal.fire({icon:'warning', title:'Tidak ada data valid', text:'Minimal kolom name/Nama harus ada.'});
          return;
        }

        Swal.fire({
          icon:'question', title:'Import '+items.length+' kelas?',
          text:'Nama kelas unik; data dengan nama sama akan diperbarui.',
          showCancelButton:true, confirmButtonText:'Import', cancelButtonText:'Batal',
          customClass:{ confirmButton:'btn btn-brand', cancelButton:'btn btn-outline-secondary' },
          buttonsStyling:false
        }).then(function(res){
          if(!res.isConfirmed) return;
          fetch("{{ route('classrooms.import') }}", {
            method:'POST',
            headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body: JSON.stringify({ items: items })
          })
          .then(function(r){ return r.json(); })
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
