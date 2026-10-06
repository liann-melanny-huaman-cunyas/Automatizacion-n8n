@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>Certificados</h1>
        <p>Solicitudes y estado de certificados registrados.</p>
    </div>
</div>
<br>

<div class="card">
    <form method="GET"
          action="{{ route('certificados.index') }}"
          class="search-form">

        <input
            style="width: 350px; max-width: 100%;"
            type="text"
            name="buscar"
            value="{{ $buscar ?? '' }}"
            placeholder="Buscar por estudiante, DNI, correo o actividad..."
        >

        <button type="submit" class="btn">
            Buscar
        </button>

    </form>
</div>
<br>

<div class="card table-card">

    <!-- Se cambió 'table-wrapper' por 'table-responsive' para activar el scroll horizontal en celulares -->
    <div class="table-responsive">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Solicitante</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Actividad</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Riesgo</th>
                    <th>Bloque</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody>

            @forelse($certificados as $certificado)

                <tr>
                    <td>{{ $certificado->id }}</td>
                    <td>
                        <strong>{{ $certificado->nombres_apellidos }}</strong>
                    </td>
                    <td>{{ $certificado->dni }}</td>
                    <td>{{ $certificado->remitente_email }}</td>
                    <td>{{ $certificado->nombre_actividad }}</td>
                    <td>{{ $certificado->tipo_certificado }}</td>
                    <td>
                        <span class="badge">
                            {{ ucfirst($certificado->estado) }}
                        </span>
                    </td>
                    <td>
                        @if($certificado->score_riesgo !== null)
                            {{ $certificado->score_riesgo }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ str_replace('_', ' ', $certificado->bloque) }}</td>
                    <td>{{ $certificado->created_at }}</td>
                </tr>

            @empty

                <tr>
                    <td colspan="10" class="empty" style="text-align: center; color: #6b7280;">
                        No hay certificados registrados.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="pagination" style="margin-top: 20px;">
        {{ $certificados->links() }}
    </div>

</div>

@endsection