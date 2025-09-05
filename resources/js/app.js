
// === Sidebar Toggle ===
(function(){
  var sidebar   = document.getElementById('appSidebar');
  var main      = document.getElementById('appMain');
  var btnToggle = document.getElementById('btnSidebarToggle');

  if(!sidebar || !btnToggle) return;

  // Remember collapsed state (desktop)
  var LS_KEY = 'sipb.sidebar.collapsed';
  function applyCollapsedFromLS(){
    try{
      var collapsed = localStorage.getItem(LS_KEY) === '1';
      if(window.matchMedia('(min-width: 992px)').matches){
        sidebar.classList.toggle('collapsed', collapsed);
      }else{
        sidebar.classList.remove('collapsed');
      }
    }catch(e){}
  }
  applyCollapsedFromLS();

  // Toggle behavior:
  // - Desktop (≥992px): collapse/expand (icons-only)
  // - Mobile: slide show/hide
  function toggleSidebar(){
    if(window.matchMedia('(min-width: 992px)').matches){
      var willCollapse = !sidebar.classList.contains('collapsed');
      sidebar.classList.toggle('collapsed', willCollapse);
      try{ localStorage.setItem(LS_KEY, willCollapse ? '1':'0'); }catch(e){}
    }else{
      sidebar.classList.toggle('show');
    }
  }
  btnToggle.addEventListener('click', toggleSidebar);

  // Close when clicking outside on mobile
  document.addEventListener('click', function(e){
    if(window.matchMedia('(max-width: 991.98px)').matches){
      if(sidebar.classList.contains('show')){
        var clickInside = sidebar.contains(e.target) || btnToggle.contains(e.target);
        if(!clickInside){ sidebar.classList.remove('show'); }
      }
    }
  });

  // Re-apply on resize
  window.addEventListener('resize', applyCollapsedFromLS);
})();

(function () {
  // AOS init (aman kalau file tidak ada)
  try { AOS.init({ duration: 600, once: true }); } catch (e) {}

  // Flash -> SweetAlert2
  document.addEventListener('DOMContentLoaded', function () {
    var ok  = document.querySelector('meta[name="flash-ok"]');
    var err = document.querySelector('meta[name="flash-err"]');
    var okMsg  = ok  ? ok.getAttribute('content')  : '';
    var errMsg = err ? err.getAttribute('content') : '';
    if (okMsg)  Swal.fire({ icon: 'success', title: 'Sukses', text: okMsg });
    if (errMsg) Swal.fire({ icon: 'error',   title: 'Gagal',  text: errMsg });
  });

  // Export table ke Excel (SheetJS)
  window.exportTableToXLSX = function (tableId, filename) {
    filename = filename || 'rekap.xlsx';
    var table = document.getElementById(tableId);
    if (!table || typeof XLSX === 'undefined') return;
    var wb = XLSX.utils.table_to_book(table, { sheet: 'Data' });
    XLSX.writeFile(wb, filename);
  };
})();


