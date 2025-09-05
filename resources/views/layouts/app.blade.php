<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="flash-ok"  content="{{ session('ok') }}">
  <meta name="flash-err" content="{{ session('err') }}">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%232563eb'/><stop offset='1' stop-color='%2360a5fa'/></linearGradient></defs><rect x='0' y='0' width='64' height='64' rx='14' fill='url(%23g)'/><polygon points='8,26 32,14 56,26 32,34' fill='%23fff'/><rect x='22' y='33' width='20' height='6' rx='2' fill='%23fff'/><circle cx='49' cy='28' r='2.2' fill='%23fff'/><path d='M49 30 v9' stroke='%23fff' stroke-width='3' stroke-linecap='round'/><circle cx='49' cy='41' r='3.2' fill='%23fff'/></svg>">

  <title>SIPB - Sistem Informasi Pembayaran Belajar</title>

  {{-- CSS lokal --}}
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/aos.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/choices.min.css') }}">

  <style>
    :root{ --navh: 56px; }
    .brand-gradient{ background: linear-gradient(135deg, var(--brand), var(--brand2)); color:#fff; }
    .card-lite{ background:#ffffffcc; backdrop-filter: blur(6px); border:1px solid rgba(37,99,235,.15); border-radius:16px; }

    /* Navbar sticky offset */
    .navbar.fixed-top{ box-shadow: 0 4px 16px rgba(0,0,0,.06); }
    body{ padding-top: var(--navh); }

    /* ===== Navbar modern ===== */
    .navbar-modern {
      min-height: var(--navh);
      backdrop-filter: saturate(140%) blur(6px);
    }
    .navbar-modern .navbar-brand {
      font-weight: 800;
      letter-spacing: .2px;
    }
    .nav-actions { gap: .5rem; }
    .nav-divider {
      width:1px; height:24px; background:rgba(255,255,255,.35);
      margin:0 .25rem;
    }
    .btn-outline-light.btn-ghost {
      --bs-btn-color:#fff; --bs-btn-border-color:rgba(255,255,255,.35);
      --bs-btn-hover-bg:rgba(255,255,255,.12);
      --bs-btn-hover-border-color:rgba(255,255,255,.55);
      --bs-btn-active-bg:rgba(255,255,255,.18);
      --bs-btn-active-border-color:rgba(255,255,255,.6);
      border-radius:12px; padding:.35rem .6rem;
    }
    .btn-light.btn-chip {
      border-radius:999px; padding:.35rem .7rem; font-weight:600;
    }
    .role-badge {
      display:inline-flex; align-items:center; gap:.4rem;
      background:rgba(255,255,255,.18); color:#fff; border:1px solid rgba(255,255,255,.28);
      padding:.28rem .6rem; border-radius:999px; font-size:.85rem; font-weight:600;
    }
    .role-badge .dot {
      width:8px; height:8px; border-radius:50%; background:#22c55e;
      box-shadow:0 0 0 3px rgba(34,197,94,.12);
    }

    /* ===== Shell + Sidebar ===== */
    .app-shell{ display:flex; min-height: calc(100vh - var(--navh)); }
    .sidebar{ position:sticky; top:0; height:calc(100vh - var(--navh)); width:260px; background:#ffffffee; backdrop-filter:blur(6px);
      border-right:1px solid rgba(37,99,235,.12); transition: transform .25s ease, width .25s ease, box-shadow .25s ease; z-index:1020; }
    .sidebar .sidebar-inner{ padding:12px; }
    .sidebar .sidebar-brand{ font-weight:700; font-size:18px; margin:6px 8px 10px 8px; }
    .sidebar .nav .nav-link{ display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:12px; color:#0f172a;
      transition: background .2s ease, transform .12s ease, color .2s ease; position:relative; overflow:hidden; }
    .sidebar .nav .nav-link i{ font-size:18px; width:22px; text-align:center; }
    .sidebar .nav .nav-link .lbl{ white-space:nowrap; }
    .sidebar .nav .nav-link:hover{ background: rgba(96,165,250,.18); transform: translateX(2px); }
    .sidebar .nav .nav-link.active{ background: linear-gradient(135deg, var(--brand), var(--brand2)); color:#fff;
      box-shadow: 0 6px 16px rgba(37,99,235,.25); }
    .sidebar .nav-section{ font-size:12px; color:#6b7280; padding:6px 10px; text-transform:uppercase; letter-spacing:.4px; }
    .sidebar .nav .nav-link::after{ content:""; position:absolute; inset:auto 0 0 0; height:0; background: rgba(96,165,250,.25); transition: height .2s ease; }
    .sidebar .nav .nav-link:hover::after{ height:3px; }

    .app-main{ flex:1; min-width:0; transition: margin-left .25s ease; }

    /* Mobile sidebar slide */
    @media (max-width: 991.98px){
      .sidebar{ position:fixed; left:0; top:var(--navh); height: calc(100vh - var(--navh)); transform: translateX(-100%); }
      .sidebar.show{ transform: translateX(0); box-shadow: 0 20px 40px rgba(0,0,0,.18); }
    }
    /* Desktop collapse */
    @media (min-width: 992px){
      .sidebar.collapsed{ width: 76px; }
      .sidebar.collapsed .lbl{ display:none; }
    }
  </style>
</head>
<body>

{{-- ===== NAVBAR MODERN ===== --}}
<nav class="navbar navbar-expand-lg navbar-modern brand-gradient fixed-top">
  <div class="container-fluid">
    {{-- Left: Sidebar toggle + Brand --}}
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-light btn-sm" id="btnSidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
      </button>
      <a style="color: white" class="navbar-brand" href="{{ route('dashboard') }}">SIPB</a>
    </div>

    {{-- Right actions --}}
    <div class="ms-auto d-flex align-items-center nav-actions">
      @if(session('role'))
        {{-- Chip Role --}}
        <span class="role-badge d-none d-sm-inline-flex" title="Peran aktif">
          <span class="dot"></span>{{ strtoupper(session('role')) }}
        </span>
      @endif

      {{-- (optional) menu admin --}}
      @if(session('role')==='admin')
        <a class="btn btn-outline-light btn-ghost d-none d-sm-inline-flex" href="{{ route('passwords.form') }}">
          <i class="bi bi-key me-1"></i> Ubah Password
        </a>
      @endif

      <span class="nav-divider d-none d-sm-inline-block"></span>

      {{-- Logout (POST + modal) --}}
      @if(session('role'))
        <form id="logoutForm" method="post" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="button" id="btnLogout" class="btn btn-light btn-chip">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </button>
          <noscript>
            <button type="submit" class="btn btn-light btn-chip mt-2">Logout</button>
          </noscript>
        </form>
      @endif
    </div>
  </div>
</nav>

<div class="app-shell">
  @include('partials.sidebar')
  <main id="appMain" class="app-main">
    <div class="container-fluid py-3">
      <div class="card card-lite p-3" data-aos="fade-up">
        <h5 class="mb-3">@yield('title')</h5>
        @yield('content')
      </div>
    </div>
  </main>
</div>

{{-- JS lokal --}}
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/aos.js') }}"></script>
<script src="{{ asset('assets/js/xlsx.full.min.js') }}"></script>
<script src="{{ asset('assets/js/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>

<script>
  // === Sidebar Toggle & Collapse (sinkron dengan appMain) ===
  (function(){
    var sidebar   = document.getElementById('appSidebar');
    var btnToggle = document.getElementById('btnSidebarToggle'); // tombol di navbar
    var appMain   = document.getElementById('appMain');
    var LS_KEY    = 'sipb.sidebar.collapsed';

    if(!sidebar || !appMain) return;

    function applyCollapsedFromLS(){
      var collapsed = false;
      try{ collapsed = localStorage.getItem(LS_KEY) === '1'; }catch(e){}
      if(window.matchMedia('(min-width: 992px)').matches){
        sidebar.classList.toggle('collapsed', collapsed);
        appMain.classList.toggle('sidebar-collapsed', collapsed);
      }else{
        // di mobile, pakai slide-in, jangan pakai margin
        sidebar.classList.remove('collapsed');
        appMain.classList.remove('sidebar-collapsed');
      }
    }
    applyCollapsedFromLS();

    function toggleSidebar(){
      if(window.matchMedia('(min-width: 992px)').matches){
        var willCollapse = !sidebar.classList.contains('collapsed');
        sidebar.classList.toggle('collapsed', willCollapse);
        appMain.classList.toggle('sidebar-collapsed', willCollapse);
        try{ localStorage.setItem(LS_KEY, willCollapse ? '1':'0'); }catch(e){}
      }else{
        // mobile: slide
        sidebar.classList.toggle('show');
      }
    }

    if(btnToggle){ btnToggle.addEventListener('click', toggleSidebar); }

    // Klik di luar sidebar menutup panel (mobile)
    document.addEventListener('click', function(e){
      if(window.matchMedia('(max-width: 991.98px)').matches && sidebar.classList.contains('show')){
        var clickInside = sidebar.contains(e.target) || (btnToggle && btnToggle.contains(e.target));
        if(!clickInside){ sidebar.classList.remove('show'); }
      }
    });

    // Re-apply saat resize
    var rT=null;
    window.addEventListener('resize', function(){ clearTimeout(rT); rT=setTimeout(applyCollapsedFromLS,120); });
  })();

    // === Logout confirm (SweetAlert2 tema biru) ===
  (function(){
    var btn  = document.getElementById('btnLogout');
    var form = document.getElementById('logoutForm');
    if(!btn || !form) return;
    btn.addEventListener('click', function(){
      if (typeof Swal === 'undefined') { form.submit(); return; }
      Swal.fire({
        icon: 'question',
        title: 'Keluar sekarang?',
        text: 'Sesi Anda akan diakhiri.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
          confirmButton: 'btn btn-brand ',
          cancelButton: 'btn btn-outline-secondary me-2'
        },
        buttonsStyling: false,
        // background: 'linear-gradient(135deg, rgba(59,130,246,.06), rgba(37,99,235,.06))',
      }).then(function(res){
        if (res.isConfirmed) form.submit();
      });
    });
  })();

  // === AOS init ===
  AOS.init({ once:true, duration:500, easing:'ease-out' });
</script>

</body>
</html>
