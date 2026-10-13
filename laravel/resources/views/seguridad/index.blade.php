@extends('layouts.app')

@section('title','Seguridad SOAR | CEFIC')
@section('header','Seguridad SOAR')

@section('content')

<style>
    .b-alto   { background:#fef3f2; color:#b42318; }
    .b-medio  { background:#fffaeb; color:#b54708; }
    .b-bajo   { background:#ecfdf3; color:#067647; }
    .b-con    { background:#eff8ff; color:#175cd3; }
    .b-sin    { background:#f2f4f7; color:#475467; }
    .filtros  { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin:0; }
    .filtros select { width:260px; max-width:100%; }
    .sub      { display:block; font-size:12px; color:#748094; margin-top:2px; }
</style>

<h1 class="page-title">Seguridad SOAR</h1>
<p class="muted">Monitoreo de eventos registrados por los procesos automatizados.</p>
<br>

<form method="GET" class="filtros">
    <select name="escenario">
        <option value="todos">Todos los escenarios</option>
        <option value="sin_seguridad" @selected(($escenario ?? '') === 'sin_seguridad')>Sin seguridad</option>
        <option value="con_seguridad" @selected(($escenario ?? '') === 'con_seguridad')>Con seguridad SOAR</option>
    </select>
    <button class="btn">Filtrar</button>

    @if(($escenario ?? 'todos') !== 'todos')
        <a href="{{ route('seguridad.index') }}" class="btn" style="background:#95a5a6;">Limpiar</a>
    @endif
</form>
<br>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Flujo / Proceso</th>
                    <th>Escenario</th>
                    <th>Categoría</th>
                    <th>Nivel</th>
                    <th>Riesgo</th>
                    <th>Acción ejecutada</th>
                    <th>Detección</th>
                    <th>Resolución</th>
                </tr>
            </thead>

            <tbody>
                @forelse($eventos as $e)
                <tr>
                    <td>#{{ $e->id }}</td>

                    <td>
                        <strong>{{ $e->proceso }}</strong>
                        <span class="sub">Flujo {{ $e->flujo }}</span>
                    </td>

                    <td>
                        <span class="badge {{ $e->escenario === 'con_seguridad' ? 'b-con' : 'b-sin' }}">
                            {{ $e->escenario === 'con_seguridad' ? 'CON SOAR' : 'SIN seguridad' }}
                        </span>
                    </td>

                    <td>{{ ucfirst(str_replace('_', ' ', $e->categoria)) }}</td>

                    <td>
                        @php
                            $nivel = strtolower($e->nivel ?? '');
                            $clase = match($nivel) {
                                'alto'  => 'b-alto',
                                'medio' => 'b-medio',
                                'bajo'  => 'b-bajo',
                                default => '',
                            };
                        @endphp
                        <span class="badge {{ $clase }}">{{ $nivel ? ucfirst($nivel) : '—' }}</span>
                    </td>

                    <td>{{ $e->score_riesgo ?? '—' }}</td>

                    <td style="white-space:normal;min-width:180px;">{{ $e->accion_ejecutada }}</td>

                    <td>{{ $e->hora_deteccion ?? '—' }}</td>

                    <td>
                        @if($e->hora_resolucion)
                            {{ $e->hora_resolucion }}
                        @else
                            <span class="badge b-medio">Pendiente</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:#748094;">
                        No existen eventos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($eventos instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div style="margin-top:20px;text-align:center;">
            {{ $eventos->links() }}
        </div>
    @endif
</div>

@endsection