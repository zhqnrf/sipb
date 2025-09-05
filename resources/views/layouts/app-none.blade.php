<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="flash-ok"  content="{{ session('ok') }}">
  <meta name="flash-err" content="{{ session('err') }}">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%232563eb'/><stop offset='1' stop-color='%2360a5fa'/></linearGradient></defs><rect x='0' y='0' width='64' height='64' rx='14' fill='url(%23g)'/><polygon points='8,26 32,14 56,26 32,34' fill='%23fff'/><rect x='22' y='33' width='20' height='6' rx='2' fill='%23fff'/><circle cx='49' cy='28' r='2.2' fill='%23fff'/><path d='M49 30 v9' stroke='%23fff' stroke-width='3' stroke-linecap='round'/><circle cx='49' cy='41' r='3.2' fill='%23fff'/></svg>">

    <title>Login - Sistem Informasi Pembayaran Belajar</title>

  {{-- CSS lokal --}}
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/aos.css') }}">


</head>
<body>

        @yield('content')

<script src="{{ asset('assets/js/choices.min.js') }}"></script>

{{-- JS lokal --}}
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('assets/js/app.js') }}"></script>



</body>
</html>
