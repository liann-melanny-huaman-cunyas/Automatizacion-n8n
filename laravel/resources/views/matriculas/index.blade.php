@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>Matrículas</h1>
        <p>Gestión de matrículas registradas en el sistema.</p>
    </div>
</div>

<div class="card">

    <form method="GET" action="{{ route('matriculas.index') }}" class="search-form">
        <input
            type="text"
            name="buscar"
            value="{{ $buscar }}"
            placeholder="Buscar por estudiante, código, programa o curso..."
        >

        <button type="submit">
            Buscar
        </button>
    </form>

</div>

<div class="card table-card">

    <div class="table-wrapper">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estudiante</th>
                    <th>Código</th>
                    <th>Programa</th>
                    <th>Periodo</th>
                    <th>Curso</th>
                    <th>Estado</th>
                    <th>Bloque</th>
                    <th>Riesgo</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody>

            @forelse($matriculas as $matricula)

                <tr>
                    <td>{{ $matricula->id }}</td>

                    <td>
                        <strong>
                            {{ $matricula->nombre_estudiante }}
                        </strong>
                    </td>

                    <td>
                        {{ $matricula->codigo_estudiante }}
                    </td>

                    <td>
                        {{ $matricula->programa_academico }}
                    </td>

                    <td>
                        {{ $matricula->periodo_academico }}
                    </td>

                    <td>
                        {{ $matricula->curso }}
                    </td>

                    <td>
                        <span class="badge">
                            {{ ucfirst($matricula->estado_matricula) }}
                        </span>
                    </td>

                    <td>
                        {{ str_replace('_', ' ', $matricula->bloque) }}
                    </td>

                    <td>
                        @if($matricula->score_riesgo !== null)
                            {{ $matricula->score_riesgo }}
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        {{ $matricula->fecha_registro }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="10" class="empty">
                        No hay matrículas registradas.
                    </td>
                </tr>

            @endforelse

            </tbody>
        </table>

    </div>

    <div class="pagination">
        {{ $matriculas->links() }}
    </div>

</div>

@endsection