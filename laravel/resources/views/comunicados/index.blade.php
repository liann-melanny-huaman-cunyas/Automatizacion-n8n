@extends('layouts.app')

@section('title', 'Comunicados - CEFIC')
@section('header', 'Comunicados')

@section('content')

<div style="
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
">

    <div>
        <h2>Comunicados</h2>

        <p style="color:#6b7280; margin-top:5px;">
            Gestión y seguimiento de comunicaciones institucionales.
        </p>
    </div>

    <a
        href="{{ route('comunicados.create') }}"
        class="btn"
    >
        Nuevo comunicado
    </a>

</div>

<div class="card">

    <table>

        <thead>
            <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Destinatarios</th>
                <th>Escenario</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <tbody>

        @forelse($comunicados as $comunicado)

            <tr>

                {{-- ID --}}
                <td>
                    #{{ $comunicado->id }}
                </td>

                {{-- ASUNTO --}}
                <td>
                    <strong>
                        {{ $comunicado->asunto }}
                    </strong>

                    <br>

                    <small style="color:#6b7280;">
                        {{ \Illuminate\Support\Str::limit($comunicado->cuerpo, 80) }}
                    </small>
                </td>

                {{-- DESTINATARIOS --}}
                <td>
                    {{ $comunicado->destinatarios->count() }}

                    @if($comunicado->destinatarios->count() > 0)

                        <br>

                        <small style="color:#6b7280;">
                            @foreach($comunicado->destinatarios->take(3) as $destinatario)

                                {{ $destinatario->estudiante->nombres_apellidos ?? $destinatario->correo }}

                                @if(!$loop->last)
                                    ,
                                @endif

                            @endforeach

                            @if($comunicado->destinatarios->count() > 3)
                                ...
                            @endif
                        </small>

                    @endif
                </td>

                {{-- ESCENARIO --}}
                <td>

                    <span class="badge">

                        {{ $comunicado->bloque === 'con_seguridad'
                            ? 'CON SOAR'
                            : 'SIN seguridad'
                        }}

                    </span>

                </td>

                {{-- ESTADO --}}
                <td>

                    <span class="badge">
                        {{ ucfirst($comunicado->estado) }}
                    </span>

                </td>

                {{-- FECHA --}}
                <td>
                    {{ $comunicado->fecha_creacion }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="6">
                    No existen comunicados registrados.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

</div>

@endsection
