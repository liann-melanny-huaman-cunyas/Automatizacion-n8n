@extends('layouts.app')

@section('title', 'Nuevo comunicado | CEFIC')
@section('header', 'Nuevo comunicado')

@section('content')

<div style="
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
">
    <div>
        <h2>Nuevo comunicado</h2>

        <p style="color:#6b7280; margin-top:5px;">
            Registra un comunicado y selecciona los estudiantes destinatarios.
        </p>
    </div>

    <a href="{{ route('comunicados.index') }}" class="btn">
        Volver
    </a>
</div>

<div class="card">

    <form action="{{ route('comunicados.store') }}" method="POST">

        @csrf

        <div style="margin-bottom:20px;">
            <label for="asunto">
                <strong>Asunto</strong>
            </label>

            <input
                type="text"
                id="asunto"
                name="asunto"
                value="{{ old('asunto') }}"
                required
                style="width:100%; margin-top:8px;"
            >

            @error('asunto')
                <small style="color:red;">
                    {{ $message }}
                </small>
            @enderror
        </div>


        <div style="margin-bottom:20px;">
            <label for="cuerpo">
                <strong>Mensaje</strong>
            </label>

            <textarea
                id="cuerpo"
                name="cuerpo"
                rows="6"
                required
                style="width:100%; margin-top:8px;"
            >{{ old('cuerpo') }}</textarea>

            @error('cuerpo')
                <small style="color:red;">
                    {{ $message }}
                </small>
            @enderror
        </div>


        <div style="margin-bottom:20px;">

            <label>
                <strong>Seleccionar estudiantes</strong>
            </label>

            <p style="color:#6b7280;">
                Selecciona los estudiantes que recibirán el comunicado.
            </p>

            @error('estudiantes')
                <small style="color:red;">
                    {{ $message }}
                </small>
            @enderror

            <div class="card" style="margin-top:15px;">

                <table>

                    <thead>
                        <tr>
                            <th width="50">Seleccionar</th>
                            <th>Estudiante</th>
                            <th>DNI</th>
                            <th>Correo</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($estudiantes as $estudiante)

                        <tr>

                            <td>
                                <input
                                    type="checkbox"
                                    name="estudiantes[]"
                                    value="{{ $estudiante->id }}"
                                    {{ in_array(
                                        $estudiante->id,
                                        old('estudiantes', [])
                                    ) ? 'checked' : '' }}
                                >
                            </td>

                            <td>
                                {{ $estudiante->nombres_apellidos }}
                            </td>

                            <td>
                                {{ $estudiante->dni }}
                            </td>

                            <td>
                                {{ $estudiante->correo }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4">
                                No existen estudiantes activos.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        <div style="margin-top:25px;">

            <button type="submit" class="btn">
                Registrar comunicado
            </button>

            <a
                href="{{ route('comunicados.index') }}"
                class="btn"
                style="margin-left:10px;"
            >
                Cancelar
            </a>

        </div>

    </form>

</div>

@endsection
