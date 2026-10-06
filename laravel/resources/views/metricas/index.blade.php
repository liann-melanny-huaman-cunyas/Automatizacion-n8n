@extends('layouts.app')

@section('title','Métricas | CEFIC')
@section('header','Métricas de Seguridad')

@section('content')

<h1 class="page-title">Métricas de seguridad</h1>

<p class="muted">
Indicadores para la evaluación de los procesos automatizados.
</p>

<br>

<div class="card">

<form method="GET">

<select name="escenario">

<option value="todos"
{{ $escenario === 'todos' ? 'selected':'' }}>
Todos los escenarios
</option>

<option value="sin_seguridad"
{{ $escenario === 'sin_seguridad' ? 'selected':'' }}>
Sin seguridad
</option>

<option value="con_seguridad"
{{ $escenario === 'con_seguridad' ? 'selected':'' }}>
Con seguridad SOAR
</option>

</select>

<select name="flujo">

<option value="todos">Todos los flujos</option>

@foreach($flujos as $f)

<option
value="{{ $f }}"
{{ $flujo === $f ? 'selected':'' }}
>
{{ $f }}
</option>

@endforeach

</select>

<button class="btn">Consultar</button>

</form>

</div>

<br>

<div class="grid">

<div class="card">
<div class="muted">Tiempo Medio de Detección</div>
<div class="stat-number">
{{ number_format($metricas['mttd'],2) }}
<small>min</small>
</div>
<div class="muted">MTTD</div>
</div>

<div class="card">
<div class="muted">Tiempo Medio de Respuesta</div>
<div class="stat-number">
{{ number_format($metricas['mttr'],2) }}
<small>min</small>
</div>
<div class="muted">MTTR</div>
</div>

<div class="card">
<div class="muted">Clasificación correcta</div>
<div class="stat-number">
{{ number_format($metricas['clasificacion'],2) }}%
</div>
</div>

<div class="card">
<div class="muted">Integración de controles</div>
<div class="stat-number">
{{ number_format($metricas['controles'],2) }}%
</div>
</div>

<div class="card">
<div class="muted">Ejecuciones seguras</div>
<div class="stat-number">
{{ number_format($metricas['ejecuciones'],2) }}%
</div>
</div>

<div class="card">
<div class="muted">Disponibilidad</div>
<div class="stat-number">
{{ number_format($metricas['disponibilidad'],2) }}%
</div>
</div>

</div>

<br>

<div class="card">

<h3>Instrumentos de evaluación</h3>

<br>

<table>

<tr>
<th>Instrumento</th>
<th>Indicador</th>
<th>Resultado</th>
</tr>

<tr>
<td>Ficha SLA</td>
<td>MTTD</td>
<td>{{ number_format($metricas['mttd'],2) }} min</td>
</tr>

<tr>
<td>Ficha SLA</td>
<td>MTTR</td>
<td>{{ number_format($metricas['mttr'],2) }} min</td>
</tr>

<tr>
<td>Ficha de clasificación</td>
<td>Clasificación correcta</td>
<td>{{ number_format($metricas['clasificacion'],2) }}%</td>
</tr>

<tr>
<td>Lista de cotejo</td>
<td>Integración de controles</td>
<td>{{ number_format($metricas['controles'],2) }}%</td>
</tr>

<tr>
<td>Lista de cotejo</td>
<td>Ejecuciones seguras</td>
<td>{{ number_format($metricas['ejecuciones'],2) }}%</td>
</tr>

<tr>
<td>Registro de disponibilidad</td>
<td>Disponibilidad</td>
<td>{{ number_format($metricas['disponibilidad'],2) }}%</td>
</tr>

</table>

</div>

@endsection