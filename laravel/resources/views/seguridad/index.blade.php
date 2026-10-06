@extends('layouts.app')

@section('title','Seguridad SOAR | CEFIC')
@section('header','Seguridad SOAR')

@section('content')

<h1 class="page-title">Seguridad SOAR</h1>

<p class="muted">
Monitoreo de eventos registrados por los procesos automatizados.
</p>

<br>

<form method="GET">

<select name="escenario">

<option value="todos">Todos los escenarios</option>

<option value="sin_seguridad"
{{ $escenario === 'sin_seguridad' ? 'selected':'' }}>
Sin seguridad
</option>

<option value="con_seguridad"
{{ $escenario === 'con_seguridad' ? 'selected':'' }}>
Con seguridad SOAR
</option>

</select>

<button class="btn">Filtrar</button>

</form>

<br>

<div class="card">

<table>
<thead>
<tr>
<th>ID</th>
<th>Flujo</th>
<th>Proceso</th>
<th>Escenario</th>
<th>Categoría</th>
<th>Acción</th>
<th>Detección</th>
<th>Resolución</th>
</tr>
</thead>

<tbody>

@forelse($eventos as $e)

<tr>
<td>#{{ $e->id }}</td>
<td>{{ $e->flujo }}</td>
<td>{{ $e->proceso }}</td>
<td><span class="badge">{{ $e->escenario }}</span></td>
<td>{{ $e->categoria }}</td>
<td>{{ $e->accion_ejecutada }}</td>
<td>{{ $e->hora_deteccion ?? '—' }}</td>
<td>{{ $e->hora_resolucion ?? 'Pendiente' }}</td>
</tr>

@empty

<tr><td colspan="8">No existen eventos registrados.</td></tr>

@endforelse

</tbody>
</table>

</div>

@endsection