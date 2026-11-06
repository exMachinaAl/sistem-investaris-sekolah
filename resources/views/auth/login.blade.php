<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk • Sistem Inventaris Sekolah</title><link rel="stylesheet" href="{{ asset('assets/app.css') }}"></head>
<body class="auth-page"><div class="auth-card">
<div class="brand-logo">▦</div><div class="login-mark">PORTAL SARPRAS</div>
<h1>Selamat datang kembali.</h1>
<p>Masuk ke Sistem Inventaris Sekolah untuk mengelola gedung, prasarana, dan barang inventaris.</p>
@if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login.process') }}">@csrf
<div class="field"><div class="field-label-row"><label for="email">Alamat email</label><x-help-tip text="Gunakan alamat email akun yang diberikan administrator sekolah." /></div><input id="email" class="control" type="email" name="email" value="{{ old('email') }}" placeholder="admin@sekolah.test" autocomplete="username" required autofocus></div>
<div class="field"><div class="field-label-row"><label for="password">Password</label><x-help-tip text="Gunakan password akun Anda. Sistem membatasi percobaan login berulang untuk melindungi akun." /></div><input id="password" class="control" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required></div>
<div class="action-help" style="margin-top:25px;width:100%"><button type="submit" class="btn btn-primary btn-wide" style="padding:14px">Masuk ke dashboard →</button><x-help-tip text="Jika data benar Anda akan masuk ke dashboard sesuai hak akses akun." /></div>
</form><div class="auth-foot">Akses khusus staf yang diberikan akun oleh administrator sekolah.<br>SMK Negeri 1 Cerme • Sistem Informasi Sarpras</div>
</div><script src="{{ asset('assets/help.js') }}" defer></script></body></html>
