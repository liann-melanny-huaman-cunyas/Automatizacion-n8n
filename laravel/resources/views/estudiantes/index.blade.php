@extends('layouts.app')

@section('title','Estudiantes | CEFIC')
@section('header','Estudiantes')

@section('content')

<style>
    .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;
              align-items:center;justify-content:center;padding:15px;}
    .modal-bg.open{display:flex;}
    .modal-box{background:#fff;border-radius:10px;padding:22px;width:480px;max-width:100%;
               max-height:90vh;overflow-y:auto;}
    .modal-box label{display:block;margin-top:10px;font-weight:600;font-size:.9em;}
    .modal-box input,.modal-box select{width:100%;padding:8px;box-sizing:border-box;}
    .modal-actions{margin-top:18px;display:flex;gap:8px;justify-content:flex-end;}
    .err{color:#c0392b;font-size:.8em;}
    .alert-ok{background:#d4edda;color:#155724;padding:10px 14px;border-radius:6px;margin-bottom:12px;}
    .btn-sm{padding:5px 10px;font-size:.85em;}
</style>

<h1 class="page-title">Estudiantes</h1>
<p class="muted">Registro de estudiantes de CEFIC.</p>
<br>

@if(session('success'))
    <div class="alert-ok">{{ session('success') }}</div>
@endif

<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <form method="GET" style="margin:0;">
        <input style="width:350px;max-width:100%;" name="buscar" value="{{ $buscar }}"
               placeholder="Buscar nombre, código, DNI o correo...">
        <button class="btn">Buscar</button>
    </form>
    <button type="button" class="btn" onclick="abrirCrear()">+ Nuevo estudiante</button>
</div>
<br>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Código</th><th>Estudiante</th><th>DNI</th>
                    <th>Correo</th><th>Programa</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estudiantes as $e)
                <tr>
                    <td>{{ $e->codigo_estudiante }}</td>
                    <td><strong>{{ $e->nombres_apellidos }}</strong></td>
                    <td>{{ $e->dni }}</td>
                    <td>{{ $e->correo }}</td>
                    <td>{{ $e->programa_academico }}</td>
                    <td><span class="badge">{{ ucfirst($e->estado) }}</span></td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn btn-sm"
                            style="background:#f39c12;border-color:#e67e22;"
                            data-id="{{ $e->id }}"
                            data-codigo="{{ $e->codigo_estudiante }}"
                            data-nombres="{{ $e->nombres_apellidos }}"
                            data-dni="{{ $e->dni }}"
                            data-correo="{{ $e->correo }}"
                            data-programa="{{ $e->programa_academico }}"
                            data-estado="{{ $e->estado }}"
                            onclick="abrirEditar(this)">Editar</button>

                        <button type="button" class="btn btn-sm"
                            style="background:#e74c3c;border-color:#c0392b;"
                            data-id="{{ $e->id }}"
                            data-nombre="{{ $e->nombres_apellidos }}"
                            onclick="abrirEliminar(this)">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;">No se encontraron estudiantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;text-align:center;">
        {{ $estudiantes->links() }}
    </div>
</div>

{{-- ========== MODAL CREAR / EDITAR ========== --}}
<div class="modal-bg" id="modalEstudiante">
    <div class="modal-box">
        <h3 id="modalTitulo">Nuevo estudiante</h3>

        <form method="POST" id="formEstudiante">
            @csrf
            <input type="hidden" name="_method" id="methodInput" value="PUT" disabled>
            <input type="hidden" name="_id" id="f_id" value="{{ old('_id') }}">

            <label>Código</label>
            <input name="codigo_estudiante" id="f_codigo" value="{{ old('codigo_estudiante') }}">
            @error('codigo_estudiante')<div class="err">{{ $message }}</div>@enderror

            <label>Nombres y apellidos</label>
            <input name="nombres_apellidos" id="f_nombres" value="{{ old('nombres_apellidos') }}">
            @error('nombres_apellidos')<div class="err">{{ $message }}</div>@enderror

            <label>DNI</label>
            <input name="dni" id="f_dni" maxlength="8" value="{{ old('dni') }}">
            @error('dni')<div class="err">{{ $message }}</div>@enderror

            <label>Correo</label>
            <input type="email" name="correo" id="f_correo" value="{{ old('correo') }}">
            @error('correo')<div class="err">{{ $message }}</div>@enderror

            <label>Programa académico</label>
            <input name="programa_academico" id="f_programa" value="{{ old('programa_academico') }}">
            @error('programa_academico')<div class="err">{{ $message }}</div>@enderror

            <label>Estado</label>
            <select name="estado" id="f_estado">
                <option value="activo"   @selected(old('estado','activo')=='activo')>Activo</option>
                <option value="inactivo" @selected(old('estado')=='inactivo')>Inactivo</option>
            </select>
            @error('estado')<div class="err">{{ $message }}</div>@enderror

            <div class="modal-actions">
                <button type="button" class="btn" style="background:#95a5a6;border-color:#7f8c8d;"
                        onclick="cerrarModal('modalEstudiante')">Cancelar</button>
                <button class="btn">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- ========== MODAL ELIMINAR ========== --}}
<div class="modal-bg" id="modalEliminar">
    <div class="modal-box" style="width:400px;">
        <h3>Eliminar estudiante</h3>
        <p>¿Seguro que deseas eliminar a <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.</p>
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
    const urlStore  = "{{ route('estudiantes.store') }}";
    const urlUpdate = "{{ route('estudiantes.update', ':id') }}";
    const urlDelete = "{{ route('estudiantes.destroy', ':id') }}";

    const form   = document.getElementById('formEstudiante');
    const method = document.getElementById('methodInput');

    function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }

    function configurarForm(modo, id = '') {
        if (modo === 'crear') {
            document.getElementById('modalTitulo').textContent = 'Nuevo estudiante';
            form.action = urlStore;
            method.disabled = true;
        } else {
            document.getElementById('modalTitulo').textContent = 'Editar estudiante';
            form.action = urlUpdate.replace(':id', id);
            method.disabled = false;
        }
        document.getElementById('f_id').value = id;
    }

    function abrirCrear() {
        configurarForm('crear');
        ['codigo','nombres','dni','correo','programa'].forEach(c => document.getElementById('f_'+c).value = '');
        document.getElementById('f_estado').value = 'activo';
        document.querySelectorAll('#modalEstudiante .err').forEach(e => e.remove());
        abrirModal('modalEstudiante');
    }

    function abrirEditar(btn) {
        const d = btn.dataset;
        configurarForm('editar', d.id);
        document.getElementById('f_codigo').value   = d.codigo;
        document.getElementById('f_nombres').value  = d.nombres;
        document.getElementById('f_dni').value      = d.dni;
        document.getElementById('f_correo').value   = d.correo;
        document.getElementById('f_programa').value = d.programa;
        document.getElementById('f_estado').value   = d.estado;
        document.querySelectorAll('#modalEstudiante .err').forEach(e => e.remove());
        abrirModal('modalEstudiante');
    }

    function abrirEliminar(btn) {
        document.getElementById('nombreEliminar').textContent = btn.dataset.nombre;
        document.getElementById('formEliminar').action = urlDelete.replace(':id', btn.dataset.id);
        abrirModal('modalEliminar');
    }

    // Cerrar al hacer clic fuera del cuadro
    document.querySelectorAll('.modal-bg').forEach(bg => {
        bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
    });

    // Si hubo errores de validación, reabrir el modal con los datos escritos
    @if($errors->any())
        configurarForm("{{ old('_id') ? 'editar' : 'crear' }}", "{{ old('_id') }}");
        abrirModal('modalEstudiante');
    @endif
</script>

@endsection