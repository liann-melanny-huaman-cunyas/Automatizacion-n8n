@extends('layouts.app')

@section('title','Matrículas | CEFIC')
@section('header','Matrículas')

@section('content')

<h1 class="page-title">Matrículas</h1>
<p class="muted">Gestión de matrículas registradas en el sistema.</p>
<br>

<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <form method="GET" action="{{ route('matriculas.index') }}" style="margin:0;">
        <input style="width:350px;max-width:100%;" type="text" name="buscar" value="{{ $buscar }}"
               placeholder="Buscar por estudiante, código, programa o curso...">
        <button type="submit" class="btn">Buscar</button>
    </form>
    <button type="button" class="btn" onclick="abrirCrear()">+ Nueva matrícula</button>
</div>
<br>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Estudiante</th><th>Código</th><th>Programa</th>
                    <th>Periodo</th><th>Curso</th><th>Estado</th><th>Bloque</th>
                    <th>Riesgo</th><th>Fecha</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matriculas as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td><strong>{{ $m->nombre_estudiante }}</strong></td>
                    <td>{{ $m->codigo_estudiante }}</td>
                    <td>{{ $m->programa_academico }}</td>
                    <td>{{ $m->periodo_academico }}</td>
                    <td>{{ $m->curso }}</td>
                    <td><span class="badge">{{ ucfirst($m->estado_matricula) }}</span></td>
                    <td>{{ str_replace('_', ' ', $m->bloque) }}</td>
                    <td>{{ $m->score_riesgo !== null ? $m->score_riesgo : '—' }}</td>
                    <td>{{ $m->fecha_registro }}</td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn btn-sm"
                            style="background:#f39c12;border-color:#e67e22;"
                            data-id="{{ $m->id }}"
                            data-nombre="{{ $m->nombre_estudiante }}"
                            data-codigo="{{ $m->codigo_estudiante }}"
                            data-programa="{{ $m->programa_academico }}"
                            data-periodo="{{ $m->periodo_academico }}"
                            data-curso="{{ $m->curso }}"
                            data-estado="{{ $m->estado_matricula }}"
                            data-bloque="{{ $m->bloque }}"
                            data-riesgo="{{ $m->score_riesgo }}"
                            data-fecha="{{ $m->fecha_registro ? \Illuminate\Support\Carbon::parse($m->fecha_registro)->format('Y-m-d') : '' }}"
                            onclick="abrirEditar(this)">Editar</button>

                        <button type="button" class="btn btn-sm"
                            style="background:#e74c3c;border-color:#c0392b;"
                            data-id="{{ $m->id }}"
                            data-nombre="{{ $m->nombre_estudiante }} - {{ $m->curso }}"
                            onclick="abrirEliminar(this)">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;">No hay matrículas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;text-align:center;">
        {{ $matriculas->links() }}
    </div>
</div>

{{-- ========== MODAL CREAR / EDITAR ========== --}}
<div class="modal-bg" id="modalMatricula">
    <div class="modal-box">
        <h3 id="modalTitulo">Nueva matrícula</h3>

        <form method="POST" id="formMatricula">
            @csrf
            <input type="hidden" name="_method" id="methodInput" value="PUT" disabled>
            <input type="hidden" name="_id" id="f_id" value="{{ old('_id') }}">

            <label>Nombre del estudiante</label>
            <input name="nombre_estudiante" id="f_nombre" value="{{ old('nombre_estudiante') }}">
            @error('nombre_estudiante')<div class="err">{{ $message }}</div>@enderror

            <div class="form-row">
                <div>
                    <label>Código</label>
                    <input name="codigo_estudiante" id="f_codigo" value="{{ old('codigo_estudiante') }}">
                    @error('codigo_estudiante')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Periodo académico</label>
                    <input name="periodo_academico" id="f_periodo" placeholder="2026-II" value="{{ old('periodo_academico') }}">
                    @error('periodo_academico')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>

            <label>Programa académico</label>
            <input name="programa_academico" id="f_programa" value="{{ old('programa_academico') }}">
            @error('programa_academico')<div class="err">{{ $message }}</div>@enderror

            <label>Curso</label>
            <input name="curso" id="f_curso" value="{{ old('curso') }}">
            @error('curso')<div class="err">{{ $message }}</div>@enderror

            <div class="form-row">
                <div>
                    <label>Estado</label>
                    <select name="estado_matricula" id="f_estado">
                        @foreach(['matriculado','retirado','finalizado','pendiente'] as $est)
                            <option value="{{ $est }}" @selected(old('estado_matricula','matriculado')==$est)>{{ ucfirst($est) }}</option>
                        @endforeach
                    </select>
                    @error('estado_matricula')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Bloque</label>
                    <input name="bloque" id="f_bloque" placeholder="bloque_1" value="{{ old('bloque') }}">
                    @error('bloque')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label>Score de riesgo (0-100)</label>
                    <input type="number" step="0.01" min="0" max="100" name="score_riesgo" id="f_riesgo" value="{{ old('score_riesgo') }}">
                    @error('score_riesgo')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Fecha de registro</label>
                    <input type="date" name="fecha_registro" id="f_fecha" value="{{ old('fecha_registro') }}">
                    @error('fecha_registro')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn" style="background:#95a5a6;border-color:#7f8c8d;"
                        onclick="cerrarModal('modalMatricula')">Cancelar</button>
                <button class="btn">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- ========== MODAL ELIMINAR ========== --}}
<div class="modal-bg" id="modalEliminar">
    <div class="modal-box" style="width:400px;">
        <h3>Eliminar matrícula</h3>
        <p>¿Seguro que deseas eliminar la matrícula de <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.</p>
        <form method="POST" id="formEliminar">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="btn" style="background:#95a5a6;border-color:#7f8c8d;"
                        onclick="cerrarModal('modalEliminar')">Cancelar</button>
                <button class="btn" style="background:#e74c3c;border-color:#c0392b;">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const urlStore  = "{{ route('matriculas.store') }}";
    const urlUpdate = "{{ route('matriculas.update', ':id') }}";
    const urlDelete = "{{ route('matriculas.destroy', ':id') }}";

    const form   = document.getElementById('formMatricula');
    const method = document.getElementById('methodInput');

    function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }

    function configurarForm(modo, id = '') {
        if (modo === 'crear') {
            document.getElementById('modalTitulo').textContent = 'Nueva matrícula';
            form.action = urlStore;
            method.disabled = true;
        } else {
            document.getElementById('modalTitulo').textContent = 'Editar matrícula';
            form.action = urlUpdate.replace(':id', id);
            method.disabled = false;
        }
        document.getElementById('f_id').value = id;
    }

    function limpiarErrores() {
        document.querySelectorAll('#modalMatricula .err').forEach(e => e.remove());
    }

    function abrirCrear() {
        configurarForm('crear');
        ['nombre','codigo','periodo','programa','curso','bloque','riesgo','fecha']
            .forEach(c => document.getElementById('f_'+c).value = '');
        document.getElementById('f_estado').value = 'matriculado';
        limpiarErrores();
        abrirModal('modalMatricula');
    }

    function abrirEditar(btn) {
        const d = btn.dataset;
        configurarForm('editar', d.id);
        document.getElementById('f_nombre').value   = d.nombre;
        document.getElementById('f_codigo').value   = d.codigo;
        document.getElementById('f_periodo').value  = d.periodo;
        document.getElementById('f_programa').value = d.programa;
        document.getElementById('f_curso').value    = d.curso;
        document.getElementById('f_estado').value   = d.estado;
        document.getElementById('f_bloque').value   = d.bloque;
        document.getElementById('f_riesgo').value   = d.riesgo;
        document.getElementById('f_fecha').value    = d.fecha;
        limpiarErrores();
        abrirModal('modalMatricula');
    }

    function abrirEliminar(btn) {
        document.getElementById('nombreEliminar').textContent = btn.dataset.nombre;
        document.getElementById('formEliminar').action = urlDelete.replace(':id', btn.dataset.id);
        abrirModal('modalEliminar');
    }

    document.querySelectorAll('.modal-bg').forEach(bg => {
        bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
    });

    @if($errors->any())
        configurarForm("{{ old('_id') ? 'editar' : 'crear' }}", "{{ old('_id') }}");
        abrirModal('modalMatricula');
    @endif
</script>

@endsection