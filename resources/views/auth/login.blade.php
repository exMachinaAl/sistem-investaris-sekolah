<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk • Sistem Inventaris Sekolah</title><link rel="stylesheet" href="{{ asset('assets/app.css') }}"></head>
<body class="auth-page"><div class="auth-card">
<div class="brand-logo">▦</div><div class="login-mark">PORTAL SARPRAS</div>
<h1>Selamat datang kembali.</h1>
<p>Masuk ke Sistem Inventaris Sekolah untuk mengelola gedung, prasarana, dan barang inventaris.</p>
@if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login.process') }}">@csrf
<div class="field"><label for="email">Alamat email</label><input id="email" class="control" type="email" name="email" value="{{ old('email') }}" placeholder="admin@sekolah.test" autocomplete="username" required autofocus></div>
<div class="field"><label for="password">Password</label><input id="password" class="control" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required></div>
<button type="submit" class="btn btn-primary btn-wide" style="margin-top:25px;padding:14px">Masuk ke dashboard →</button>
</form><div class="auth-foot">Akses khusus staf yang diberikan akun oleh administrator sekolah.<br>SMK Negeri 1 Cerme • Sistem Informasi Sarpras</div>
</div></body></html>
