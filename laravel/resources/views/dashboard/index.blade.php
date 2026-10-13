@extends('layouts.app')

@section('title','Inicio | CEFIC')
@section('header','Inicio')

@section('content')

<style>
    a.stat-link { text-decoration:none; color:inherit; display:block; transition:transform .15s, box-shadow .15s; }
    a.stat-link:hover { transform:translateY(-3px); box-shadow:0 6px 16px rgba(16,24,39,.08); }
    .stat-sub { font-size:12px; color:#748094; margin-top:4px; }
    .two-col { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    @media(max-width:900px){ .two-col{ grid-template-columns:1fr; } }
    .bar-row { margin-bottom:14px; }
    .bar-head { display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px; }
    .bar-bg { background:#edf1f6; border-radius:20px; height:10px; overflow:hidden; }
    .bar-fill { height:100%; border-radius:20px; transition:width .6s; }
    .c-alto{background:#ef4444;} .c-medio{background:#f59e0b;} .c-bajo{background:#10b981;}
    .c-azul{background:#3b82f6;} .c-gris{background:#98a2b3;}
    .esc-box { padding:14px; border-radius:10px; background:#f8fafc; border:1px solid #e5e9f0; }
    .esc-box strong { font-size:24px; display:block; margin:4px 0; }
    .b-ok{background:#ecfdf3;color:#067647;} .b-err{background:#fef2f2;color:#b91c1c;}
</style>

@php
    $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
    $totalNivel = max($porNivel->sum(), 1);
    $totalCom   = max($comunicadosEstado->sum(), 1);
    $totalEjec  = max($ejecEstado->sum(), 1);
@endphp

<h1 class="page-title">{{ $saludo }}, {{ session('usuario_nombre') }}</h1>
<p class="muted">Resumen de la actividad institucional y los procesos automatizados.</p>
<br>

{{-- TARJETAS CLICABLES --}}
<div class="grid">
    <a href="{{ route('estudiantes.index') }}" class="card stat-link">
        <div class="muted">Estudiantes activos</div>
        <div class="stat-number">{{ number_format($estadisticas['estudiantes']) }}</div>
        <div class="stat-sub">Ver estudiantes →</div>
    </a>

    <a href="{{ route('matriculas.index') }}" class="card stat-link">
        <div class="muted">Matrículas</div>
        <div class="stat-number">{{ number_format($estadisticas['matriculas']) }}</div>
        <div class="stat-sub">Ver matrículas →</div>
    </a>

    <a href="{{ route('certificados.index') }}" class="card stat-link">
        <div class="muted">Certificados pendientes</div>
        <div class="stat-number">{{ number_format($estadisticas['certificados_pendientes']) }}</div>
        <div class="stat-sub">Revisar certificados →</div>
    </a>

    <a href="{{ route('comunicados.index') }}" class="card stat-link">
        <div class="muted">Comunicados</div>
        <div class="stat-number">{{ number_format($estadisticas['comunicados']) }}</div>
        <div class="stat-sub">Ver comunicados →</div>
    </a>

    <a href="{{ route('metricas.index') }}" class="card stat-link">
        <div class="muted">Ejecuciones registradas</div>
        <div class="stat-number">{{ number_format($estadisticas['ejecuciones']) }}</div>
        <div class="stat-sub">Ver métricas →</div>
    </a>

    <a href="{{ route('seguridad.index') }}" class="card stat-link">
        <div class="muted">Eventos registrados</div>
        <div class="stat-number">{{ number_format($estadisticas['incidentes']) }}</div>
        <div class="stat-sub">Ver seguridad SOAR →</div>
    </a>
</div>

<br>

{{-- GRÁFICOS --}}
<div class="two-col">
    <div class="card">
        <h3>Eventos por nivel de riesgo</h3><br>
        @foreach(['alto' => 'c-alto', 'medio' => 'c-medio', 'bajo' => 'c-bajo'] as $nivel => $color)
            @php $n = $porNivel[$nivel] ?? 0; @endphp
            <div class="bar-row">
                <div class="bar-head">
                    <span>{{ ucfirst($nivel) }}</span>
                    <span class="muted">{{ number_format($n) }} ({{ round($n / $totalNivel * 100) }}%)</span>
                </div>
                <div class="bar-bg"><div class="bar-fill {{ $color }}" style="width:{{ $n / $totalNivel * 100 }}%"></div></div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <h3>Con seguridad vs sin seguridad</h3><br>
        <div class="two-col" style="gap:12px;">
            @foreach(['sin_seguridad' => 'Sin seguridad', 'con_seguridad' => 'Con SOAR'] as $key => $label)
                @php $d = $porEscenario[$key] ?? null; @endphp
                <div class="esc-box">
                    <span class="muted">{{ $label }}</span>
                    <strong>{{ $d ? number_format($d->total) : 0 }}</strong>
                    <div class="stat-sub">
                        Resueltos: {{ $d && $d->total > 0 ? round($d->resueltos / $d->total * 100) : 0 }}%<br>
                        Detección: {{ $d ? number_format($d->mttd, 1) : 0 }} min
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<br>

<div class="two-col">
    <div class="card">
        <h3>Comunicados por estado</h3><br>
        @forelse($comunicadosEstado as $estado => $n)
            <div class="bar-row">
                <div class="bar-head">
                    <span>{{ ucfirst($estado) }}</span>
                    <span class="muted">{{ $n }}</span>
                </div>
                <div class="bar-bg">
                    <div class="bar-fill {{ $estado === 'enviado' ? 'c-bajo' : ($estado === 'cancelado' ? 'c-alto' : ($estado === 'pausado' ? 'c-medio' : 'c-gris')) }}"
                         style="width:{{ $n / $totalCom * 100 }}%"></div>
                </div>
            </div>
        @empty
            <p class="muted">Aún no hay comunicados.</p>
        @endforelse
    </div>

    <div class="card">
        <h3>Estado de las ejecuciones</h3><br>
        @forelse($ejecEstado as $estado => $n)
            <div class="bar-row">
                <div class="bar-head">
                    <span>{{ ucfirst($estado) }}</span>
                    <span class="muted">{{ number_format($n) }} ({{ round($n / $totalEjec * 100) }}%)</span>
                </div>
                <div class="bar-bg">
                    <div class="bar-fill {{ $estado === 'success' ? 'c-bajo' : 'c-alto' }}"
                         style="width:{{ $n / $totalEjec * 100 }}%"></div>
                </div>
            </div>
        @empty
            <p class="muted">Aún no hay ejecuciones.</p>
        @endforelse
    </div>
</div>

<br>

{{-- ACTIVIDAD RECIENTE --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <h3>Actividad reciente</h3>
        <a href="{{ route('metricas.index') }}" class="muted" style="font-size:13px;">Ver todo →</a>
    </div>
    <br>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Flujo</th><th>Proceso</th>
                    <th>Escenario</th><th>Estado</th><th>Inicio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ejecuciones as $e)
                @php $ok = in_array(strtolower($e->estado), ['success', 'completado']); @endphp
                <tr>
                    <td>#{{ $e->id }}</td>
                    <td>{{ $e->flujo }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $e->proceso)) }}</td>
                    <td>
                        <span class="badge">{{ $e->escenario === 'con_seguridad' ? 'CON SOAR' : 'SIN seguridad' }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $ok ? 'b-ok' : 'b-err' }}">{{ ucfirst($e->estado) }}</span>
                    </td>
                    <td>{{ $e->inicio }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#6b7280;">No existen ejecuciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection