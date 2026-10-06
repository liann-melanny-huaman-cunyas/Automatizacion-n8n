@extends('layouts.app')

@section('title','Certificados | CEFIC')
@section('header','Certificados')

@section('content')

<h1 class="page-title">Certificados</h1>
<p class="muted">Solicitudes y seguimiento de certificados.</p>

<br>

<div class="card">

<table>
<thead>
<tr>
<th>ID</th>
<th>Solicitante</th>
<th>DNI</th>
<th>Actividad</th>
<th>Periodo</th>
<th>Escenario</th>
<th>Riesgo</th>
<th>Estado</th>
</tr>
</thead>

<tbody>

@forelse($certificados as $c)

<tr>
<td>#{{ $c->id }}</td>
<td>{{ $c->nombres_apellidos }}</td>
<td>{{ $c->dni }}</td>
<td>
{{ ucfirst($c->tipo_actividad ?? '') }}
<br>
<small>{{ $c->nombre_actividad }}</small>
</td>
<td>{{ $c->periodo }}</td>
<td><span class="badge">{{ $c->bloque }}</span></td>
<td>{{ $c->score_riesgo ?? '—' }}</td>
<td><span class="badge">{{ $c->estado }}</span></td>
</tr>

@empty

<tr><td colspan="8">No existen solicitudes.</td></tr>

@endforelse

</tbody>
</table>

</div>

@endsection