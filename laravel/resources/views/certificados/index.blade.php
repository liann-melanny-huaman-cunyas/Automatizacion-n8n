@extends('layouts.app')

@section('title','Certificados | CEFIC')
@section('header','Certificados')

@section('content')

<h1 class="page-title">Certificados</h1>
<p class="muted">Solicitudes y estado de certificados registrados.</p>
<br>

<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <form method="GET" action="{{ route('certificados.index') }}" style="margin:0;">
        <input style="width:350px;max-width:100%;" type="text" name="buscar" value="{{ $buscar ?? '' }}"
               placeholder="Buscar por estudiante, DNI, correo o actividad...">
        <button type="submit" class="btn">Buscar</button>
    </form>
    <button type="button" class="btn" onclick="abrirCrear()">+ Nuevo certificado</button>
</div>
<br>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Solicitante</th><th>DNI</th><th>Correo</th>
                    <th>Actividad</th><th>Tipo</th><th>Estado</th><th>Riesgo</th>
                    <th>Bloque</th><th>Fecha</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($certificados as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td><strong>{{ $c->nombres_apellidos }}</strong></td>
                    <td>{{ $c->dni }}</td>
                    <td>{{ $c->remitente_email }}</td>
                    <td>{{ $c->nombre_actividad }}</td>
                    <td>{{ $c->tipo_certificado }}</td>
                    <td><span class="badge">{{ ucfirst($c->estado) }}</span></td>
                    <td>{{ $c->score_riesgo !== null ? $c->score_riesgo : '—' }}</td>
                    <td>{{ str_replace('_', ' ', $c->bloque) }}</td>
                    <td>{{ $c->created_at }}</td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="btn btn-sm"
                            style="background:#f39c12;border-color:#e67e22;"
                            data-id="{{ $c->id }}"
                            data-nombres="{{ $c->nombres_apellidos }}"
                            data-dni="{{ $c->dni }}"
                            data-email="{{ $c->remitente_email }}"
                            data-actividad="{{ $c->nombre_actividad }}"
                            data-tipo="{{ $c->tipo_certificado }}"
                            data-estado="{{ $c->estado }}"
                            data-riesgo="{{ $c->score_riesgo }}"
                            data-bloque="{{ $c->bloque }}"
                            onclick="abrirEditar(this)">Editar</button>

                        <button type="button" class="btn btn-sm"
                            style="background:#e74c3c;border-color:#c0392b;"
                            data-id="{{ $c->id }}"
                            data-nombre="{{ $c->nombres_apellidos }} - {{ $c->nombre_actividad }}"
                            onclick="abrirEliminar(this)">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;color:#6b7280;">No hay certificados registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;text-align:center;">
        {{ $certificados->links() }}
    </div>
</div>

{{-- ========== MODAL CREAR / EDITAR ========== --}}
<div class="modal-bg" id="modalCertificado">
    <div class="modal-box">
        <h3 id="modalTitulo">Nuevo certificado</h3>

        <form method="POST" id="formCertificado">
            @csrf
            <input type="hidden" name="_method" id="methodInput" value="PUT" disabled>
            <input type="hidden" name="_id" id="f_id" value="{{ old('_id') }}">

            <label>Nombres y apellidos</label>
            <input name="nombres_apellidos" id="f_nombres" value="{{ old('nombres_apellidos') }}">
            @error('nombres_apellidos')<div class="err">{{ $message }}</div>@enderror

            <div class="form-row">
                <div>
                    <label>DNI</label>
                    <input name="dni" id="f_dni" maxlength="8" value="{{ old('dni') }}">
                    @error('dni')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Correo</label>
                    <input type="email" name="remitente_email" id="f_email" value="{{ old('remitente_email') }}">
                    @error('remitente_email')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>

            <label>Actividad</label>
            <input name="nombre_actividad" id="f_actividad" value="{{ old('nombre_actividad') }}">
            @error('nombre_actividad')<div class="err">{{ $message }}</div>@enderror

            <div class="form-row">
                <div>
                    <label>Tipo de certificado</label>
                    <input name="tipo_certificado" id="f_tipo" list="tiposCert" value="{{ old('tipo_certificado') }}">
                    <datalist id="tiposCert">
                        <option value="Asistencia">
                        <option value="Participación">
                        <option value="Aprobación">
                    </datalist>
                    @error('tipo_certificado')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Estado</label>
                    <select name="estado" id="f_estado">
                        @foreach(['pendiente','aprobado','rechazado','entregado'] as $est)
                            <option value="{{ $est }}" @selected(old('estado','pendiente')==$est)>{{ ucfirst($est) }}</option>
                        @endforeach
                    </select>
                    @error('estado')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label>Score de riesgo (0-100)</label>
                    <input type="number" step="0.01" min="0" max="100" name="score_riesgo" id="f_riesgo" value="{{ old('score_riesgo') }}">
                    @error('score_riesgo')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Bloque</label>
                    <input name="bloque" id="f_bloque" placeholder="bloque_1" value="{{ old('bloque') }}">
                    @error('bloque')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn" style="background:#95a5a6;border-color:#7f8c8d;"
                        onclick="cerrarModal('modalCertificado')">Cancelar</button>
                <button class="btn">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- ========== MODAL ELIMINAR ========== --}}
<div class="modal-bg" id="modalEliminar">
    <div class="modal-box" style="width:400px;">
        <h3>Eliminar certificado</h3>
        <p>¿Seguro que deseas eliminar el certificado de <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.</p>
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
    const urlStore  = "{{ route('certificados.store') }}";
    const urlUpdate = "{{ route('certificados.update', ':id') }}";
    const urlDelete = "{{ route('certificados.destroy', ':id') }}";

    const form   = document.getElementById('formCertificado');
    const method = document.getElementById('methodInput');

    function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }

    function configurarForm(modo, id = '') {
        if (modo === 'crear') {
            document.getElementById('modalTitulo').textContent = 'Nuevo certificado';
            form.action = urlStore;
            method.disabled = true;
        } else {
            document.getElementById('modalTitulo').textContent = 'Editar certificado';
            form.action = urlUpdate.replace(':id', id);
            method.disabled = false;
        }
        document.getElementById('f_id').value = id;
    }

    function limpiarErrores() {
        document.querySelectorAll('#modalCertificado .err').forEach(e => e.remove());
    }

    function abrirCrear() {
        configurarForm('crear');
        ['nombres','dni','email','actividad','tipo','riesgo','bloque']
            .forEach(c => document.getElementById('f_'+c).value = '');
        document.getElementById('f_estado').value = 'pendiente';
        limpiarErrores();
        abrirModal('modalCertificado');
    }

    function abrirEditar(btn) {
        const d = btn.dataset;
        configurarForm('editar', d.id);
        document.getElementById('f_nombres').value   = d.nombres;
        document.getElementById('f_dni').value       = d.dni;
        document.getElementById('f_email').value     = d.email;
        document.getElementById('f_actividad').value = d.actividad;
        document.getElementById('f_tipo').value      = d.tipo;
        document.getElementById('f_estado').value    = d.estado;
        document.getElementById('f_riesgo').value    = d.riesgo;
        document.getElementById('f_bloque').value    = d.bloque;
        limpiarErrores();
        abrirModal('modalCertificado');
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
        abrirModal('modalCertificado');
    @endif
</script>

@endsection