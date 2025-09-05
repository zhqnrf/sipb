@php
  // Helper aktif
  function is_active($patterns){
    $patterns = (array)$patterns;
    foreach($patterns as $p){
      if(request()->routeIs($p)) return 'active';
    }
    return '';
  }
@endphp

<style>
  /* ===== Sidebar fix: tidak nabrak navbar ===== */
  :root{ --navh: 56px; } /* fallback jika layout belum set */

  /* Desktop (>=992px): sidebar fixed di bawah navbar */
  @media (min-width: 992px){
    #appSidebar{
      position: fixed;
      left: 0;
      top: var(--navh);
      height: calc(100vh - var(--navh));
      width: 260px;
      background:#ffffffee;
      backdrop-filter: blur(6px);
      border-right: 1px solid rgba(37,99,235,.12);
      transform: none;
      z-index: 1020;
    }
    #appSidebar.collapsed{ width: 76px; }
    /* Bila layout belum atur margin-left konten, kita bantu set di sini */
    #appMain{ margin-left: 260px; transition: margin-left .25s ease; }
    #appMain.sidebar-collapsed{ margin-left: 76px; }
  }

  /* Mobile (<992px): slide-in dari kiri, tidak ganggu navbar */
  @media (max-width: 991.98px){
    #appSidebar{
      position: fixed;
      left: 0;
      top: var(--navh);
      height: calc(100vh - var(--navh));
      width: 260px;
      background:#ffffff;
      border-right: 1px solid rgba(37,99,235,.12);
      transform: translateX(-100%);
      transition: transform .25s ease, box-shadow .25s ease;
      z-index: 1020;
    }
    #appSidebar.show{ transform: translateX(0); box-shadow: 0 20px 40px rgba(0,0,0,.18); }
    /* di mobile, konten full width */
    #appMain{ margin-left: 0 !important; }
  }

  /* Styling isi sidebar (ikut tema kamu) */
  .sidebar-inner{ padding:12px; }
  .sidebar-brand{ font-weight:700; font-size:18px; margin:6px 8px 10px 8px; }
  .nav.flex-column .nav-link{
    display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:12px; color:#0f172a;
    transition: background .2s ease, transform .12s ease, color .2s ease; position:relative; overflow:hidden;
  }
  .nav.flex-column .nav-link i{ font-size:18px; width:22px; text-align:center; }
  .nav.flex-column .nav-link .lbl{ white-space:nowrap; }
  .nav.flex-column .nav-link:hover{ background: rgba(96,165,250,.18); transform: translateX(2px); }
  .nav.flex-column .nav-link.active{
    background: linear-gradient(135deg, var(--brand, #2563eb), var(--brand2, #60a5fa)); color:#fff;
    box-shadow: 0 6px 16px rgba(37,99,235,.25);
  }
  .nav-section{ font-size:12px; color:#6b7280; padding:6px 10px; text-transform:uppercase; letter-spacing:.4px; }
  .nav.flex-column .nav-link::after{
    content:""; position:absolute; inset:auto 0 0 0; height:0; background: rgba(96,165,250,.25); transition: height .2s ease;
  }
  .nav.flex-column .nav-link:hover::after{ height:3px; }
</style>

<aside id="appSidebar" class="sidebar shadow-sm">
  <div class="sidebar-inner">
    <div class="sidebar-brand d-flex align-items-center gap-2">
      <i class="bi bi-mortarboard"></i><span class="lbl">SIPB</span>
    </div>

    <nav class="nav flex-column">
      <a class="nav-link {{ is_active('dashboard') }}" href="{{ route('dashboard') }}">
        <i class="bi bi-speedometer2"></i> <span class="lbl">Dashboard</span>
      </a>

      <div class="nav-section">Master</div>
      <a class="nav-link {{ is_active('students.*') }}" href="{{ route('students.index') }}">
        <i class="bi bi-people"></i> <span class="lbl">Siswa</span>
      </a>
      <a class="nav-link {{ is_active('classrooms.*') }}" href="{{ route('classrooms.index') }}">
        <i class="bi bi-building"></i> <span class="lbl">Kelas</span>
      </a>
      <a class="nav-link {{ is_active('fee-types.*') }}" href="{{ route('fee-types.index') }}">
        <i class="bi bi-tags"></i> <span class="lbl">Jenis Tagihan</span>
      </a>

      <div class="nav-section mt-2">Tagihan</div>
      <a class="nav-link {{ is_active('bills.generate.*') }}" href="{{ route('bills.generate.form') }}">
        <i class="bi bi-ui-checks-grid"></i> <span class="lbl">Generate Tagihan</span>
      </a>
      <a class="nav-link {{ is_active('bills.index') }}" href="{{ route('bills.index') }}">
        <i class="bi bi-receipt"></i> <span class="lbl">Daftar Tagihan</span>
      </a>

      {{-- <div class="nav-section mt-2">Kwitansi</div>
      <a class="nav-link {{ is_active('receipts.*') }}" href="{{ route('bills.index',['period'=>now()->format('Y-m')]) }}">
        <i class="bi bi-file-earmark-text"></i> <span class="lbl">Riwayat Pembayaran</span>
      </a> --}}
    </nav>


  </div>

  <script>
    (function(){
      // Pastikan tinggi navbar dibaca dan dipakai sebagai --navh agar sidebar tidak nabrak
      function syncNavHeight(){
        var nav = document.querySelector('.navbar.fixed-top') || document.querySelector('.navbar');
        var h = nav ? nav.getBoundingClientRect().height : 56;
        document.documentElement.style.setProperty('--navh', h + 'px');
      }
      syncNavHeight();
      window.addEventListener('resize', function(){ clearTimeout(window.__nvT); window.__nvT = setTimeout(syncNavHeight, 120); });

      // Collapse/expand sinkron dengan konten (desktop)
      var btn = document.getElementById('btnSidebarCollapse');
      var sidebar = document.getElementById('appSidebar');
      var appMain = document.getElementById('appMain');
      var LSKEY = 'sipb.sidebar.collapsed';

      function applyFromLS(){
        var collapsed = false;
        try{ collapsed = localStorage.getItem(LSKEY) === '1'; }catch(e){}
        if (window.matchMedia('(min-width: 992px)').matches){
          sidebar.classList.toggle('collapsed', collapsed);
          if(appMain) appMain.classList.toggle('sidebar-collapsed', collapsed);
        }else{
          sidebar.classList.remove('collapsed');
          if(appMain) appMain.classList.remove('sidebar-collapsed');
        }
      }
      applyFromLS();
      window.addEventListener('resize', function(){ clearTimeout(window.__sbT); window.__sbT=setTimeout(applyFromLS, 120); });

      if(btn && sidebar){
        btn.addEventListener('click', function(){
          var willCollapse = !sidebar.classList.contains('collapsed');
          sidebar.classList.toggle('collapsed', willCollapse);
          if(appMain) appMain.classList.toggle('sidebar-collapsed', willCollapse);
          try{ localStorage.setItem(LSKEY, willCollapse ? '1':'0'); }catch(e){}
          var icon = btn.querySelector('i');
          if(icon){
            icon.classList.toggle('bi-chevron-double-left', !willCollapse);
            icon.classList.toggle('bi-chevron-double-right', willCollapse);
          }
        });
      }
    })();
  </script>



</aside>
