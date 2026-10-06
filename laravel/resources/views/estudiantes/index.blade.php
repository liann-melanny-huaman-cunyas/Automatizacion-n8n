@extends('layouts.app')

@section('title','Estudiantes | CEFIC')
@section('header','Estudiantes')

@section('content')

<h1 class="page-title">Estudiantes</h1>
<p class="muted">Registro de estudiantes de CEFIC.</p>

<br>

<form method="GET">
    <input
        style="width:350px; max-width: 100%;"
        name="buscar"
        value="{{ $buscar }}"
        placeholder="Buscar nombre, código, DNI o correo..."
    >
    <button class="btn">Buscar</button>
</form>

<br>

<div class="card">

    <!-- Aquí se agrega el contenedor responsivo -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Estudiante</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Programa</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>
                @foreach($estudiantes as $e)
                <tr>
                    <td>{{ $e->codigo_estudiante }}</td>
                    <td><strong>{{ $e->nombres_apellidos }}</strong></td>
                    <td>{{ $e->dni }}</td>
                    <td>{{ $e->correo }}</td>
                    <td>{{ $e->programa_academico }}</td>
                    <td><span class="badge">{{ ucfirst($e->estado) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection