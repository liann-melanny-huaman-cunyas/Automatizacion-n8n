@extends('layouts.app')

@section('title', 'Métricas | CEFIC')
@section('header', 'Métricas de Seguridad')

@section('content')

<style>
    .metricas-container {
        width: 100%;
    }

    .page-title {
        margin-bottom: 8px;
    }

    .muted {
        color: #777;
    }

    .filtros-card {
        margin-top: 20px;
    }

    .filtros-form {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .filtro-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filtro-group label {
        font-size: 14px;
        font-weight: 600;
    }

    .filtro-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
    }

    .btn-consultar {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        white-space: nowrap;
    }

    .metricas-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }

    .metrica-card {
        padding: 20px;
        border-radius: 12px;
    }

    .metrica-titulo {
        font-size: 14px;
        margin-bottom: 10px;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .stat-number small {
        font-size: 15px;
        font-weight: 500;
    }

    .filtro-actual {
        margin-top: 20px;
        font-size: 14px;
    }

    /*
    |--------------------------------------------------------------------------
    | Tablet
    |--------------------------------------------------------------------------
    */

    @media (max-width: 900px) {

        .filtros-form {
            grid-template-columns: 1fr 1fr;
        }

        .btn-consultar {
            width: 100%;
        }

        .metricas-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Celular
    |--------------------------------------------------------------------------
    */

    @media (max-width: 600px) {

        .filtros-form {
            grid-template-columns: 1fr;
        }

        .metricas-grid {
            grid-template-columns: 1fr;
        }

        .stat-number {
            font-size: 28px;
        }

        .metrica-card {
            padding: 16px;
        }
    }
</style>


<div class="metricas-container">

    {{-- TÍTULO --}}

    <h1 class="page-title">
        Métricas de seguridad
    </h1>

    <p class="muted">
        Indicadores para la evaluación de los procesos automatizados.
    </p>


    {{-- FILTROS --}}

    <div class="card filtros-card">

        <form method="GET" class="filtros-form">

            {{-- ESCENARIO --}}

            <div class="filtro-group">

                <label for="escenario">
                    Escenario
                </label>

                <select name="escenario" id="escenario">

                    <option value="todos"
                        {{ $escenario === 'todos' ? 'selected' : '' }}>
                        Todos los escenarios
                    </option>

                    <option value="sin_seguridad"
                        {{ $escenario === 'sin_seguridad' ? 'selected' : '' }}>
                        Sin seguridad
                    </option>

                    <option value="con_seguridad"
                        {{ $escenario === 'con_seguridad' ? 'selected' : '' }}>
                        Con seguridad SOAR
                    </option>

                </select>

            </div>


            {{-- FLUJO --}}

            <div class="filtro-group">

                <label for="flujo">
                    Proceso
                </label>

                <select name="flujo" id="flujo">

                    <option value="todos">
                        Todos los procesos
                    </option>

                    @foreach($flujos as $f)

                        @php
                            $codigoFlujo = str_pad((string) $f, 2, '0', STR_PAD_LEFT);
                        @endphp

                        <option value="{{ $f }}"
                            {{ (string) $flujo === (string) $f ? 'selected' : '' }}>

                            {{ $nombresFlujos[$codigoFlujo] ?? 'Flujo ' . $codigoFlujo }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- BOTÓN --}}

            <button type="submit" class="btn btn-consultar">
                Consultar
            </button>

        </form>

    </div>


    {{-- FILTRO ACTUAL --}}

    <div class="filtro-actual muted">

        <strong>Escenario:</strong>

        @if($escenario === 'todos')
            Todos
        @elseif($escenario === 'sin_seguridad')
            Sin seguridad
        @else
            Con seguridad SOAR
        @endif

        &nbsp; | &nbsp;

        <strong>Proceso:</strong>

        @if($flujo === 'todos')

            Todos

        @else

            @php
                $codigoFlujoActual = str_pad((string) $flujo, 2, '0', STR_PAD_LEFT);
            @endphp

            {{ $nombresFlujos[$codigoFlujoActual] ?? 'Flujo ' . $codigoFlujoActual }}

        @endif

    </div>


    {{-- MÉTRICAS --}}

    <div class="metricas-grid">


        {{-- MTTD --}}

        <div class="card metrica-card">

            <div class="muted metrica-titulo">
                Tiempo Medio de Detección
            </div>

            <div class="stat-number">
                {{ number_format($metricas['mttd'], 2) }}
                <small>min</small>
            </div>

            <div class="muted">
                MTTD
            </div>

        </div>


        {{-- MTTR --}}

        <div class="card metrica-card">

            <div class="muted metrica-titulo">
                Tiempo Medio de Respuesta
            </div>

            <div class="stat-number">
                {{ number_format($metricas['mttr'], 2) }}
                <small>min</small>
            </div>

            <div class="muted">
                MTTR
            </div>

        </div>


        {{-- CLASIFICACIÓN --}}

        <div class="card metrica-card">

            <div class="muted metrica-titulo">
                Clasificación correcta
            </div>

            <div class="stat-number">
                {{ number_format($metricas['clasificacion'], 2) }}%
            </div>

            <div class="muted">
                Precisión de clasificación
            </div>

        </div>


        {{-- CONTROLES --}}

        <div class="card metrica-card">

            <div class="muted metrica-titulo">
                Integración de controles
            </div>

            <div class="stat-number">
                {{ number_format($metricas['controles'], 2) }}%
            </div>

            <div class="muted">
                Controles implementados
            </div>

        </div>


        {{-- EJECUCIONES SEGURAS --}}

        <div class="card metrica-card">

            <div class="muted metrica-titulo">
                Ejecuciones seguras
            </div>

            <div class="stat-number">
                {{ number_format($metricas['ejecuciones'], 2) }}%
            </div>

            <div class="muted">
                Ejecuciones correctas
            </div>

        </div>


        {{-- DISPONIBILIDAD --}}

        <div class="card metrica-card">

            <div class="muted metrica-titulo">
                Disponibilidad
            </div>

            <div class="stat-number">
                {{ number_format($metricas['disponibilidad'], 2) }}%
            </div>

            <div class="muted">
                Disponibilidad de los flujos
            </div>

        </div>

    </div>

</div>

@endsection