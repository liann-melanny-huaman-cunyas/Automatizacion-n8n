<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>@yield('title', 'CEFIC')</title>

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f7fb;
    color: #18212f;
}
.app { display: flex; min-height: 100vh; flex-direction: column; }

.sidebar {
    width: 260px;
    background: #101827;
    color: white;
    position: fixed;
    top: 0; bottom: 0; left: 0;
    padding: 28px 18px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
.logo { font-size: 27px; font-weight: 800; letter-spacing: 2px; }
.logo-sub { color: #8e9aaf; font-size: 12px; margin: 5px 0 35px; }
.section-title {
    color: #69778f;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 1.3px;
    margin: 25px 13px 8px;
}
.nav a {
    display: flex;
    padding: 12px 14px;
    margin: 4px 0;
    color: #c6cfdd;
    text-decoration: none;
    border-radius: 9px;
}
.nav a:hover, .nav a.active {
    color: white;
    background: #1d293b;
}

.logout {
    margin-top: auto; 
    padding-top: 30px; 
}
.logout button {
    width: 100%;
    border: 1px solid #374151;
    background: transparent;
    color: #c6cfdd;
    padding: 11px;
    border-radius: 8px;
    cursor: pointer;
}

.main {
    margin-left: 260px;
    width: calc(100% - 260px);
    flex-grow: 1;
}
.topbar {
    height: 72px;
    background: white;
    border-bottom: 1px solid #e5e9f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 35px;
}
.content { padding: 32px; }
.page-title { font-size: 26px; margin: 0; }
.muted { color: #748094; }
.grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}
.card {
    background: white;
    border: 1px solid #e5e9f0;
    border-radius: 13px;
    padding: 22px;
}
.stat-number {
    font-size: 30px;
    font-weight: 700;
    margin-top: 10px;
}
.btn {
    display: inline-block;
    border: 0;
    background: #111827;
    color: white;
    padding: 11px 17px;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
}
input, select, textarea {
    padding: 11px;
    border: 1px solid #d5dae3;
    border-radius: 8px;
    font: inherit;
    width: 100%;
}
textarea { width: 100%; min-height: 140px; }

table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 13px;
    border-bottom: 1px solid #edf0f4;
    text-align: left;
    font-size: 14px;
}
th {
    color: #667085;
    font-size: 12px;
    text-transform: uppercase;
}
.badge {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    background: #edf1f6;
    font-size: 12px;
}
.alert {
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.success { background: #ecfdf3; color: #067647; }

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 10px;
}
.table-responsive table {
    white-space: nowrap; 
    min-width: 100%;
}
.table-responsive::-webkit-scrollbar {
    height: 8px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f4f7fb; 
    border-radius: 6px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #c6cfdd; 
    border-radius: 6px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #8e9aaf;
}

/* --- FIX PAGINACIÓN (NÚMEROS E ÍCONOS) --- */
/* Oculta el diseño de móvil duplicado y el texto en inglés */
nav[role="navigation"] > div:first-child,
nav[role="navigation"] p {
    display: none !important;
}

/* Fuerza al contenedor de números a mostrarse siempre y lo centra */
nav[role="navigation"] > div:last-child {
    display: flex !important;
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-top: 20px;
}
nav[role="navigation"] > div:last-child > div {
    display: flex;
    gap: 2px;
}

/* Estilo de los botones (cuadros) */
nav[role="navigation"] span.relative,
nav[role="navigation"] a.relative {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    min-width: 38px;
    height: 38px;
    border: 1px solid #e5e9f0;
    background: white;
    color: #18212f;
    text-decoration: none;
    font-size: 14px;
    border-radius: 6px;
    transition: background 0.2s;
}

nav[role="navigation"] a.relative:hover {
    background: #f5f7fb;
}

/* Color negro para el número de la página actual */
nav[role="navigation"] span[aria-current="page"] > span {
    background: #111827 !important;
    color: white !important;
    border-color: #111827 !important;
}

/* Color gris para las flechas cuando están deshabilitadas */
nav[role="navigation"] span[aria-disabled="true"] > span {
    color: #aeb9cc;
    background: #fdfdfd;
}

/* Tamaño de los íconos (SVG) */
nav[role="navigation"] svg {
    width: 18px;
    height: 18px;
}
/* --- FIN FIX PAGINACIÓN --- */

/* Escritorio Pequeño / Tablets (hasta 992px) */
@media(max-width: 992px) {
    .sidebar { width: 220px; }
    .main { margin-left: 220px; width: calc(100% - 220px); }
    .grid { grid-template-columns: repeat(2, 1fr); }
    .content { padding: 20px; }
}

/* Teléfonos Móviles (hasta 768px) */
@media(max-width: 768px) {
    .app { display: block; }
    
    .sidebar {
        position: static;
        width: 100%;
        height: auto;
        padding: 20px;
    }
    
    .logout {
        margin-top: 25px;
    }
    
    .main {
        margin-left: 0;
        width: 100%;
    }
    
    .topbar {
        flex-direction: column;
        height: auto;
        padding: 15px;
        gap: 10px;
        text-align: center;
    }
    
    .grid { grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<div class="app">

<aside class="sidebar">

    <div class="logo">CEFIC</div>
    <div class="logo-sub">Panel Administrativo</div>

    <nav class="nav">
        <div class="section-title">General</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active':'' }}">Inicio</a>

        <div class="section-title">Gestión</div>
        <a href="{{ route('estudiantes.index') }}" class="{{ request()->routeIs('estudiantes.*') ? 'active':'' }}">Estudiantes</a>
        <a href="{{ route('matriculas.index') }}" class="{{ request()->routeIs('matriculas.*') ? 'active':'' }}">Matrículas</a>
        <a href="{{ route('certificados.index') }}" class="{{ request()->routeIs('certificados.*') ? 'active':'' }}">Certificados</a>
        <a href="{{ route('comunicados.index') }}" class="{{ request()->routeIs('comunicados.*') ? 'active':'' }}">Comunicados</a>

        <div class="section-title">Seguridad</div>
        <a href="{{ route('seguridad.index') }}" class="{{ request()->routeIs('seguridad.*') ? 'active':'' }}">Seguridad SOAR</a>
        <a href="{{ route('metricas.index') }}" class="{{ request()->routeIs('metricas.*') ? 'active':'' }}">Métricas</a>
    </nav>

    <form class="logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button>Cerrar sesión</button>
    </form>

</aside>

<main class="main">

<header class="topbar">
    <strong>@yield('header','CEFIC')</strong>

    <div>
        {{ session('usuario_nombre') }}
        ·
        {{ ucfirst(session('usuario_rol')) }}
    </div>
</header>

<section class="content">

@if(session('success'))
<div class="alert success">
    {{ session('success') }}
</div>
@endif

@yield('content')

</section>

</main>

</div>

</body>
</html>