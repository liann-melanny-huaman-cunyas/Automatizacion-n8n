@extends('layouts.app')

@section('title', 'Métricas | CEFIC')
@section('header', 'Métricas de Seguridad')

@section('content')

<style>
    .metricas-container { width: 100%; }
    .page-title { margin-bottom: 8px; }
    .muted { color: #777; }
    .filtros-card { margin-top: 20px; }
    
    .filtros-form {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }
    .filtro-group { display: flex; flex-direction: column; gap: 6px; }
    .filtro-group label { font-size: 14px; font-weight: 600; }
    .filtro-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; background: #fff; font-size: 14px; }
    .btn-consultar { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; white-space: nowrap; }
    
    .metricas-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
    .metrica-card { padding: 20px; border-radius: 12px; }
    .metrica-titulo { font-size: 14px; margin-bottom: 10px; }
    .stat-number { font-size: 32px; font-weight: 700; line-height: 1.2; margin-bottom: 6px; }
    .stat-number small { font-size: 15px; font-weight: 500; }
    .filtro-actual { margin-top: 20px; font-size: 14px; }

    /* Estilos para la tabla paginada */
    .detalle-section { margin-top: 40px; }
    .detalle-section h3 { margin-bottom: 15px; }
    .table-responsive { width: 100%; overflow-x: auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    th { background-color: #f8f9fa; font-weight: 600; }
    .badge-alto { background-color: #fee2e2; color: #ef4444; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;}
    .badge-medio { background-color: #fef3c7; color: #f59e0b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;}
    .badge-bajo { background-color: #d1fae5; color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;}

    @media (max-width: 900px) {
        .filtros-form { grid-template-columns: 1fr 1fr; }
        .btn-consultar { width: 100%; }
        .metricas-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .filtros-form { grid-template-columns: 1fr; }
        .metricas-grid { grid-template-columns: 1fr; }
        .stat-number { font-size: 28px; }
        .metrica-card { padding: 16px; }
    }
</style>

<div class="metricas-container">

    <h1 class="page-title">Métricas de seguridad</h1>
    <p class="muted">Indicadores para la evaluación de los procesos automatizados.</p>

    {{-- FILTROS --}}
    <div class="card filtros-card">
        <form method="GET" class="filtros-form">
            <div class="filtro-group">
                <label for="escenario">Escenario</label>
                <select name="escenario" id="escenario">
                    <option value="todos" {{ $escenario === 'todos' ? 'selected' : '' }}>Todos los escenarios</option>
                    <option value="sin_seguridad" {{ $escenario === 'sin_seguridad' ? 'selected' : '' }}>Sin seguridad</option>
                    <option value="con_seguridad" {{ $escenario === 'con_seguridad' ? 'selected' : '' }}>Con seguridad SOAR</option>
                </select>
            </div>

            <div class="filtro-group">
                <label for="flujo">Proceso</label>
                <select name="flujo" id="flujo">
                    <option value="todos">Todos los procesos</option>
                    @foreach($flujos as $f)
                        @php $codigoFlujo = str_pad((string) $f, 2, '0', STR_PAD_LEFT); @endphp
                        <option value="{{ $f }}" {{ (string) $flujo === (string) $f ? 'selected' : '' }}>
                            {{ $nombresFlujos[$codigoFlujo] ?? 'Flujo ' . $codigoFlujo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-consultar">Consultar</button>
        </form>
    </div>

    {{-- FILTRO ACTUAL --}}
    <div class="filtro-actual muted">
        <strong>Escenario:</strong> 
        @if($escenario === 'todos') Todos @elseif($escenario === 'sin_seguridad') Sin seguridad @else Con seguridad SOAR @endif
        &nbsp; | &nbsp;
        <strong>Proceso:</strong>
        @if($flujo === 'todos') Todos @else @php $codigoFlujoActual = str_pad((string) $flujo, 2, '0', STR_PAD_LEFT); @endphp {{ $nombresFlujos[$codigoFlujoActual] ?? 'Flujo ' . $codigoFlujoActual }} @endif
    </div>

    {{-- MÉTRICAS --}}
    @php
        $fmt = fn ($v, $sufijo = '') => $v !== null ? number_format($v, 2) . $sufijo : '—';
    @endphp

    <div class="metricas-grid">
        <div class="card metrica-card">
            <div class="muted metrica-titulo">Tiempo Medio de Detección</div>
            <div class="stat-number">
                {{ $fmt($metricas['mttd']) }}
                @if($metricas['mttd'] !== null)<small>min</small>@endif
            </div>
            <div class="muted">MTTD</div>
        </div>

        <div class="card metrica-card">
            <div class="muted metrica-titulo">Tiempo Medio de Respuesta</div>
            <div class="stat-number">
                {{ $fmt($metricas['mttr']) }}
                @if($metricas['mttr'] !== null)<small>min</small>@endif
            </div>
            <div class="muted">MTTR</div>
        </div>

        <div class="card metrica-card">
            <div class="muted metrica-titulo">Clasificación correcta</div>
            <div class="stat-number">{{ $fmt($metricas['clasificacion'], '%') }}</div>
            <div class="muted">Precisión de clasificación</div>
        </div>

        <div class="card metrica-card">
            <div class="muted metrica-titulo">Integración de controles</div>
            <div class="stat-number">{{ $fmt($metricas['controles'], '%') }}</div>
            <div class="muted">Controles implementados</div>
        </div>

        <div class="card metrica-card">
            <div class="muted metrica-titulo">Ejecuciones seguras</div>
            <div class="stat-number">{{ $fmt($metricas['ejecuciones'], '%') }}</div>
            <div class="muted">Ejecuciones correctas</div>
        </div>

        <div class="card metrica-card">
            <div class="muted metrica-titulo">Disponibilidad</div>
            <div class="stat-number">{{ $fmt($metricas['disponibilidad'], '%') }}</div>
            <div class="muted">Disponibilidad de los flujos</div>
        </div>
    </div>

    {{-- NUEVA SECCIÓN: DETALLE PAGINADO --}}
    <div class="detalle-section">
        <h3>Registro de Eventos (Detalle)</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Incidente</th>
                        <th>Flujo</th>
                        <th>Categoría</th>
                        <th>Nivel</th>
                        <th>Ingreso</th>
                        <th>Detección</th>
                        <th>Resolución</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventosPaginados as $evento)
                    <tr>
                        <td><strong>{{ $evento->id_incidente }}</strong><br><small class="muted">{{ $evento->escenario }}</small></td>
                        <td>{{ $evento->flujo }}</td>
                        <td>{{ ucfirst($evento->categoria) }}</td>
                        <td>
                            <span class="badge-{{ strtolower($evento->nivel) }}">
                                {{ strtoupper($evento->nivel) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($evento->hora_ingreso)->format('d/m/Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($evento->hora_deteccion)->format('H:i:s') }}</td>
                        <td>{{ \Carbon\Carbon::parse($evento->hora_resolucion)->format('H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">No hay eventos registrados para estos filtros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- CONTROLES DE PAGINACIÓN --}}
        <div style="margin-top: 20px; text-align: center;">
            {{ $eventosPaginados->appends(['escenario' => $escenario, 'flujo' => $flujo])->links() }}
        </div>
    </div>

</div>
@endsection