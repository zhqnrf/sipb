@extends('layouts.app-none')
@section('title','Login')
@section('content')

<style>
  /* ===== Stage ===== */
  .login-stage{
    position:relative; min-height:100dvh; display:flex; align-items:center; justify-content:center;
    background:
      radial-gradient(1100px 700px at -10% 10%, color-mix(in oklab, var(--brand, #2563eb) 10%, transparent) , transparent 60%),
      radial-gradient(1100px 700px at 110% 90%, color-mix(in oklab, var(--brand2, #60a5fa) 10%, transparent) , transparent 60%),
      linear-gradient(180deg, #f6fbff, #ffffff);
    color:var(--ink, #0f172a); overflow:hidden;
  }

   /* pastikan label "Login" benar2 center */
  .btn-brand{ display:flex; align-items:center; justify-content:center; }

  /* wrapper khusus input agar toggle selalu tepat tengah */
  .input-wrap{ position:relative; }
  .input-wrap .has-toggle{ padding-right:3rem !important; }   /* ruang tombol */
  .input-wrap .toggle-pw{
    position:absolute; right:.5rem; top:0; bottom:0;           /* center vertikal */
    display:grid; place-items:center; width:42px;
    border:0; background:transparent; color:#334155; cursor:pointer;
  }
  .input-wrap .toggle-pw i{ font-size:1.05rem; }

  /* ===== Shapes: glassmorphism gradient, draggable ===== */
  .bg-canvas{ position:absolute; inset:0; overflow:hidden; z-index:0; }
  .shape{
    position:absolute; will-change: transform;
    border-radius:26px;
    /* glass look */
    background:
      radial-gradient(120% 120% at 30% 30%, rgba(255,255,255,.75) 0 22%, rgba(255,255,255,.25) 38%, transparent 60%),
      linear-gradient(135deg, color-mix(in oklab, var(--brand,#2563eb) 55%, transparent), color-mix(in oklab, var(--brand2,#60a5fa) 55%, transparent));
    border:1px solid rgba(255,255,255,.55);
    backdrop-filter: blur(12px) saturate(120%);
    -webkit-backdrop-filter: blur(12px) saturate(120%);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,.7),
      0 18px 42px rgba(37,99,235,.22);
    opacity:.92;
    cursor:grab; user-select:none;
  }
  .shape:active{ cursor:grabbing; }
  .shape.circle{ border-radius:50%; }
  .shape.square{ border-radius:26px; }
  .shape.triangle{
    border-radius:20px; /* untuk tepi halus sebelum clip-path */
    -webkit-clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    background:
      radial-gradient(110% 110% at 32% 32%, rgba(255,255,255,.75) 0 20%, rgba(255,255,255,.18) 34%, transparent 60%),
      linear-gradient(150deg, color-mix(in oklab, var(--brand,#2563eb) 55%, transparent), color-mix(in oklab, var(--brand2,#60a5fa) 55%, transparent));
  }
  .shape::after{ /* highlight halus */
    content:""; position:absolute; inset:0; border-radius:inherit; pointer-events:none;
    background: linear-gradient(180deg, rgba(255,255,255,.35), rgba(255,255,255,0) 60%);
  }

  /* ===== Card (glass + gradient stroke) ===== */
  .card-lite{
    position:relative; z-index:1;
    background: rgba(255,255,255,.82);
    border-radius:22px; padding:28px;
    backdrop-filter: blur(10px);
    border:1px solid transparent;
    box-shadow:
      0 14px 32px rgba(2,132,199,.10),
      0 3px 8px rgba(2,132,199,.06);
    background-image:
      linear-gradient(#ffffffd9,#ffffffd9),
      linear-gradient(120deg, color-mix(in oklab, var(--brand,#2563eb) 40%, #fff), color-mix(in oklab, var(--brand2,#60a5fa) 40%, #fff));
    background-origin: border-box;
    background-clip: padding-box, border-box;
  }
  .brand-title{ font-weight:800; letter-spacing:.2px; color:var(--brand, #2563eb); }
  .subtle{ color:#64748b; }

  .btn-brand{
    background: linear-gradient(90deg, var(--brand, #2563eb), var(--brand2, #60a5fa));
    border:0; color:#fff; font-weight:700;
    box-shadow: 0 10px 22px rgba(37,99,235,.28);
  }
  .btn-brand:hover{ filter:brightness(1.03); transform: translateY(-1px); }

  /* ===== Perfect center toggle (selalu tepat tengah field) ===== */
  .pos-rel{ position:relative; }
  .has-toggle{ padding-right:3rem !important; }       /* ruang untuk tombol */
  .toggle-pw{
    position:absolute; top:0; right:.5rem; bottom:0;   /* kunci tengah vertikal */
    display:grid; place-items:center; width:42px;
    border:0; background:transparent; color:#334155; cursor:pointer;
  }
  .toggle-pw i{ font-size:1.05rem; }
  .form-control:focus{
    border-color: transparent;
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--brand,#2563eb) 22%, transparent);
  }

  @media (max-width: 576px){
    .card-lite{ padding:20px; border-radius:18px; }
  }
</style>

<section class="login-stage">
  <!-- Shapes -->
  <div class="bg-canvas" id="bgCanvas">
    <div class="shape circle"   style="width:130px;height:130px; left:6%;  top:12%;"   data-vx="0.45"  data-vy="0.35"></div>
    <div class="shape square"   style="width:100px;height:100px; left:78%; top:10%;"   data-vx="-0.40" data-vy="0.28"></div>
    <div class="shape triangle" style="width:180px;height:150px; left:12%; top:72%;"   data-vx="0.32"  data-vy="-0.26"></div>
    <div class="shape circle"   style="width:82px; height:82px;  left:54%; top:68%;"   data-vx="-0.52" data-vy="-0.22"></div>
    <div class="shape square"   style="width:150px;height:150px; left:84%; top:64%;"   data-vx="0.28"  data-vy="-0.32"></div>
    <div class="shape triangle" style="width:160px;height:135px; left:32%; top:20%;"   data-vx="-0.25" data-vy="0.22"></div>
    <div class="shape circle"   style="width:110px;height:110px; left:28%; top:44%;"   data-vx="0.36"  data-vy="0.18"></div>
    <div class="shape square"   style="width:125px;height:125px; left:62%; top:26%;"   data-vx="-0.33" data-vy="0.34"></div>
  </div>

  <div class="container position-relative" style="z-index:1;">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <form method="post" action="{{ route('doLogin') }}" class="card-lite">
          @csrf
          <div class="text-center mb-3">
            <i class="bi bi-mortarboard" style="font-size:44px;color:var(--brand)"></i>
            <div class="brand-title mt-1">SIPB — Masuk</div>
            <div class="small subtle">Sistem Informasi Pembayaran Belajar</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Masuk sebagai</label>
            <select name="role" class="form-select" required>
              <option value="">— Pilih —</option>
              <option value="admin">Admin/TU</option>
              <option value="kepsek">Kepsek (read-only)</option>
            </select>
          </div>

        <div class="mb-3">
  <label class="form-label fw-semibold">Password</label>
  <div class="input-wrap">
    <input type="password" id="password" name="password"
           class="form-control has-toggle" autocomplete="current-password" required>
    <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan/Sembunyikan password">
      <i class="bi bi-eye" id="eyeIcon"></i>
    </button>
  </div>
</div>


          <button class="btn btn-brand w-100 py-2">Login</button>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
/* ===== Toggle password (ikon selalu center) ===== */
(function(){
  const pw = document.getElementById('password');
  const btn = document.getElementById('togglePw');
  const icon = document.getElementById('eyeIcon');
  if(!pw || !btn || !icon) return;
  const toggle = ()=>{
    const isText = pw.type === 'text';
    pw.type = isText ? 'password' : 'text';
    icon.classList.toggle('bi-eye', isText);
    icon.classList.toggle('bi-eye-slash', !isText);
  };
  btn.addEventListener('click', toggle);
  btn.addEventListener('keydown', e=>{
    if(e.key==='Enter' || e.key===' ') { e.preventDefault(); toggle(); }
  });
})();

/* ===== Shapes: drag + inertia + bounce dinding + sedikit tabrakan ===== */
(function(){
  const area = document.getElementById('bgCanvas');
  const nodes = Array.from(document.querySelectorAll('.shape'));
  if(!area || !nodes.length) return;

  let bounds = area.getBoundingClientRect();
  const updateBounds = ()=> bounds = area.getBoundingClientRect();
  window.addEventListener('resize', updateBounds);

  const bodies = nodes.map(el=>{
    const r = el.getBoundingClientRect(), a = bounds;
    const w = r.width || parseFloat(el.style.width) || 120;
    const h = r.height|| parseFloat(el.style.height)|| 120;
    const x = (r.left - a.left) || parseFloat(el.style.left) || 0;
    const y = (r.top  - a.top)  || parseFloat(el.style.top)  || 0;
    el.style.left = '0px'; el.style.top = '0px';
    return {
      el, w, h, x, y,
      vx: parseFloat(el.dataset.vx||0.35),
      vy: parseFloat(el.dataset.vy||0.25),
      dragging:false, dx:0, dy:0, lastX: x, lastY: y,
      rcol: Math.min(w,h)*0.5
    };
  });

  function wall(b){
    const maxX = bounds.width  - b.w;
    const maxY = bounds.height - b.h;
    if(b.x <= 0){ b.x=0; b.vx = Math.abs(b.vx); }
    if(b.y <= 0){ b.y=0; b.vy = Math.abs(b.vy); }
    if(b.x >= maxX){ b.x=maxX; b.vx = -Math.abs(b.vx); }
    if(b.y >= maxY){ b.y=maxY; b.vy = -Math.abs(b.vy); }
  }

  function collide(a,b){
    const ax=a.x+a.w/2, ay=a.y+a.h/2, bx=b.x+b.w/2, by=b.y+b.h/2;
    const dx=ax-bx, dy=ay-by, d=Math.hypot(dx,dy), md=a.rcol+b.rcol;
    if(d>=md || d===0) return;
    const nx=dx/d, ny=dy/d, overlap=(md-d)/2;
    a.x+=nx*overlap; a.y+=ny*overlap; b.x-=nx*overlap; b.y-=ny*overlap;
    const va=a.vx*nx + a.vy*ny, vb=b.vx*nx + b.vy*ny, diff=vb-va; // elastis
    a.vx+=nx*diff; a.vy+=ny*diff; b.vx-=nx*diff; b.vy-=ny*diff;
  }

  // Drag handlers (mouse & touch)
  function onDown(e,b){
    b.dragging=true;
    const p = getPoint(e);
    b.dx = p.x - b.x; b.dy = p.y - b.y;
    b.lastX = p.x - b.dx; b.lastY = p.y - b.dy;
    b.el.style.transition='none';
  }
  function onMove(e,b){
    if(!b.dragging) return;
    const p = getPoint(e);
    const nx = p.x - b.dx, ny = p.y - b.dy;
    // kecepatan dari delta agar ada inertia saat lepas
    b.vx = (nx - b.lastX) * 0.4;
    b.vy = (ny - b.lastY) * 0.4;
    b.x = nx; b.y = ny;
    b.lastX = nx; b.lastY = ny;
  }
  function onUp(b){ b.dragging=false; }

  function getPoint(e){
    if(e.touches && e.touches[0]){
      const t=e.touches[0]; return {x:t.clientX - bounds.left, y:t.clientY - bounds.top};
    }
    return {x:e.clientX - bounds.left, y:e.clientY - bounds.top};
  }

  // Bind events per shape
  bodies.forEach(b=>{
    const down = (e)=>{ e.preventDefault(); onDown(e,b); };
    const move = (e)=>{ onMove(e,b); };
    const up   = ()=> onUp(b);

    b.el.addEventListener('mousedown', down);
    window.addEventListener('mousemove', move);
    window.addEventListener('mouseup', up);

    b.el.addEventListener('touchstart', down, {passive:false});
    window.addEventListener('touchmove', move, {passive:false});
    window.addEventListener('touchend', up);
  });

  function tick(){
    // motion
    for(const b of bodies){
      if(!b.dragging){
        b.vx *= 0.995; b.vy *= 0.995;
        b.x  += b.vx;  b.y  += b.vy;
        wall(b);
      }
    }
    // simple pair collisions
    for(let i=0;i<bodies.length;i++){
      for(let j=i+1;j<bodies.length;j++){
        collide(bodies[i], bodies[j]);
      }
    }
    // render
    for(const b of bodies){
      b.el.style.transform = `translate(${b.x}px, ${b.y}px)`;
    }
    requestAnimationFrame(tick);
  }
  tick();
})();
</script>

@endsection
