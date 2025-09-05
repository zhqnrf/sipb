(function () {
  try { AOS.init({ duration: 600, once: true }); } catch (e) {}
  document.addEventListener('DOMContentLoaded', function () {
    var ok  = document.querySelector('meta[name="flash-ok"]');
    var err = document.querySelector('meta[name="flash-err"]');
    var okMsg  = ok  ? ok.getAttribute('content')  : '';
    var errMsg = err ? err.getAttribute('content') : '';
    if (okMsg)  Swal.fire({ icon: 'success', title: 'Sukses', text: okMsg });
    if (errMsg) Swal.fire({ icon: 'error',   title: 'Gagal',  text: errMsg });
  });
  window.exportTableToXLSX = function (tableId, filename) {
    filename = filename || 'rekap.xlsx';
    var table = document.getElementById(tableId);
    if (!table || typeof XLSX === 'undefined') return;
    var wb = XLSX.utils.table_to_book(table, { sheet: 'Data' });
    XLSX.writeFile(wb, filename);
  };
})();
