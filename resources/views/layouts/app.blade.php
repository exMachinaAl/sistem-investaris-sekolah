<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title','Dashboard') • Sistem Inventaris Sekolah</title>
<link rel="stylesheet" href="{{ asset('assets/app.css') }}">
<link rel="stylesheet" href="{{ asset('assets/photo.css') }}"></head>
<body>
<div class="app">
<aside class="sidebar" id="sideNav">
<a class="brand" href="{{ route('dashboard') }}"><span class="brand-logo">▦</span><span>SISTEM INVENTARIS<small>SEKOLAH • SARPRAS</small></span></a>
<div class="nav-label">MENU UTAMA</div>
<a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><span class="nav-symbol">◫</span> Dashboard</a>
<a class="nav-link {{ request()->routeIs('gedung.*')?'active':'' }}" href="{{ route('gedung.index') }}"><span class="nav-symbol">▤</span> Data Gedung</a>
<a class="nav-link {{ request()->routeIs('prasarana.*')?'active':'' }}" href="{{ route('prasarana.index') }}"><span class="nav-symbol">▧</span> Ruang & Prasarana</a>
<a class="nav-link {{ request()->routeIs('jurusan.*')?'active':'' }}" href="{{ route('jurusan.index') }}"><span class="nav-symbol">◇</span> Data Jurusan</a>
<a class="nav-link {{ request()->routeIs('inventaris.*')?'active':'' }}" href="{{ route('inventaris.index') }}"><span class="nav-symbol">◈</span> Inventaris Barang</a>
<a class="nav-link {{ request()->routeIs('dokumentasi.*')?'active':'' }}" href="{{ route('dokumentasi.index') }}"><span class="nav-symbol">▣</span> Dokumentasi Foto</a>
<div class="nav-label">PENGELOLAAN</div>
<a class="nav-link {{ request()->routeIs('laporan.*')?'active':'' }}" href="{{ route('laporan.index') }}"><span class="nav-symbol">▥</span> Laporan & Ekspor</a>
@if(auth()->user()->role === 'admin')
<a class="nav-link {{ request()->routeIs('import.*')?'active':'' }}" href="{{ route('import.index') }}"><span class="nav-symbol">⇧</span> Impor CSV</a>
@endif
<div class="side-bottom"><b>Portal Sarpras</b><br>Data gedung, fasilitas, dan inventaris sekolah.<br><br><span style="color:#90a8c9">Versi 1.1 • Data Guard</span></div>
</aside>
<div class="main">
<header class="topbar"><div style="display:flex;gap:12px;align-items:center"><button type="button" class="mobile-menu" onclick="document.body.classList.toggle('nav-open')" aria-label="Buka menu">☰</button><span class="crumb">@yield('title','Dashboard')</span></div>
<div class="topbar-right"><span class="user-meta">{{ auth()->user()->name }}<span>{{ ucfirst(auth()->user()->role) }}</span></span><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" title="Keluar" aria-label="Keluar">⇥</button></form></div>
</header>
<main class="content">
@if(session('success'))<div role="status" class="notice success">✓ {{ session('success') }}</div>@endif
@if(session('error'))<div role="alert" class="notice error">⚠ {{ session('error') }}</div>@endif
@if($errors->any())<div role="alert" class="notice error">Periksa kembali data: <ul style="margin:6px 0 0;padding-left:20px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main></div></div>
<script>document.addEventListener('click',e=>{if(window.innerWidth<780&&document.body.classList.contains('nav-open')&&!e.target.closest('#sideNav')&&!e.target.closest('.mobile-menu'))document.body.classList.remove('nav-open');});</script>
</body></html>
