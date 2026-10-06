@extends('layouts.app')

@section('title','Inicio | CEFIC')
@section('header','Inicio')

@section('content')

<h1 class="page-title">Panel general</h1>

<p class="muted">
    Resumen de la actividad institucional y los procesos automatizados.
</p>

<br>

<div class="grid">
    <div class="card">
        <div class="muted">Estudiantes activos</div>
        <div class="stat-number">{{ $estadisticas['estudiantes'] ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="muted">Matrículas</div>
        <div class="stat-number">{{ $estadisticas['matriculas'] ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="muted">Certificados pendientes</div>
        <div class="stat-number">{{ $estadisticas['certificados_pendientes'] ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="muted">Comunicados</div>
        <div class="stat-number">{{ $estadisticas['comunicados'] ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="muted">Ejecuciones registradas</div>
        <div class="stat-number">{{ $estadisticas['ejecuciones'] ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="muted">Eventos registrados</div>
        <div class="stat-number">{{ $estadisticas['incidentes'] ?? 0 }}</div>
    </div>
</div>

<br>

<div class="card">
    <h3>Actividad reciente</h3>
    <br>

    <!-- Contenedor responsivo para el scroll dinámico -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Flujo</th>
                    <th>Proceso</th>
                    <th>Escenario</th>
                    <th>Estado</th>
                    <th>Inicio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ejecuciones as $e)
                <tr>
                    <td>#{{ $e->id }}</td>
                    <td>{{ $e->flujo }}</td>
                    <td>{{ $e->proceso }}</td>
                    <td>{{ $e->escenario }}</td>
                    <td>
                        <span class="badge" style="
                            background-color: {{ $e->estado == 'Completado' ? '#ecfdf3' : ($e->estado == 'Fallido' ? '#fef2f2' : '#edf1f6') }};
                            color: {{ $e->estado == 'Completado' ? '#067647' : ($e->estado == 'Fallido' ? '#b91c1c' : '#18212f') }};
                        ">
                            {{ $e->estado }}
                        </span>
                    </td>                    
                    <td>{{ $e->inicio }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #6b7280;">No existen ejecuciones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection