<?php
$page_title = "Registrar Nuevo Empleado";
require_once 'includes/header.php';
// require_once 'funciones.php'; // Si se necesitan funciones específicas para llenar selects, etc.

// Lógica para manejar el envío del formulario se hará en procesar_empleado.php
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Registrar Nuevo Empleado</h3>
        </div>
        <div class="card-body">
            <form id="formEmpleado" action="procesar_empleado.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">

                <!-- Sección Información Personal -->
                <fieldset class="mb-3">
                    <legend>Información Personal</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="paterno" name="paterno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="materno" name="materno" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombrecorto" class="form-label">Nombre Corto</label>
                            <input type="text" class="form-control" id="nombrecorto" name="nombrecorto">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dni" name="dni" required maxlength="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="nacimiento" name="nacimiento" required>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="lugarnacimiento" class="form-label">Lugar de Nacimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lugarnacimiento" name="lugarnacimiento" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estadocivil" class="form-label">Estado Civil <span class="text-danger">*</span></label>
                            <select class="form-select" id="estadocivil" name="estadocivil" required>
                                <option value="">Seleccionar...</option>
                                <option value="Soltero">Soltero(a)</option>
                                <option value="Casado">Casado(a)</option>
                                <option value="Viudo">Viudo(a)</option>
                                <option value="Divorciado">Divorciado(a)</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- Sección Información de Contacto -->
                <fieldset class="mb-3">
                    <legend>Información de Contacto</legend>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="domicilio" class="form-label">Domicilio <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="domicilio" name="domicilio" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="correopersonal" class="form-label">Correo Personal <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="correopersonal" name="correopersonal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correocorporativo" class="form-label">Correo Corporativo <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="correocorporativo" name="correocorporativo" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telcelular" class="form-label">Teléfono Celular <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telcelular" name="telcelular" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telfijo" class="form-label">Teléfono Fijo</label>
                            <input type="tel" class="form-control" id="telfijo" name="telfijo">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="contactoemergencia" class="form-label">Contacto de Emergencia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contactoemergencia" name="contactoemergencia" required>
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="cantidadhijos" class="form-label">Cantidad de Hijos <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidadhijos" name="cantidadhijos" min="0" value="0" required>
                        </div>
                    </div>
                </fieldset>

                <!-- Sección Información Laboral -->
                <fieldset class="mb-3">
                    <legend>Información Laboral</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="area" class="form-label">Área <span class="text-danger">*</span></label>
                            <select class="form-select" id="area" name="area" required>
                                <option value="">Seleccionar...</option>
                                <option value="Técnico Regulatorio">Técnico Regulatorio</option>
                                <option value="Legal Regulatorio">Legal Regulatorio</option>
                                <option value="Recursos Humanos">Recursos Humanos</option>
                                <!-- Agregar más áreas si es necesario -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <select class="form-select" id="cargo" name="cargo" required>
                                <option value="">Seleccionar...</option>
                                <option value="Socio Fundador">Socio Fundador</option>
                                <option value="Asociado">Asociado</option>
                                <option value="Asistente">Asistente</option>
                                <!-- Agregar más cargos si es necesario -->
                            </select>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modalidad" class="form-label">Modalidad <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalidad" name="modalidad" required>
                                <option value="">Seleccionar...</option>
                                <option value="Planilla">Planilla</option>
                                <option value="Recibo por Honorarios">Recibo por Honorarios</option>
                            </select>
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="derechohabiente" class="form-label">Derecho Habiente <span class="text-danger">*</span></label>
                            <select class="form-select" id="derechohabiente" name="derechohabiente" required>
                                <option value="">Seleccionar...</option>
                                <option value="No aplica">No aplica</option>
                                <option value="Conyugue">Cónyuge</option>
                                <option value="Hijo">Hijo(a)</option>
                                <option value="Padre">Padre/Madre</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="horasmeta" class="form-label">Meta de Horas Mensuales <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="horasmeta" name="horasmeta" min="0" value="30" required>
                        </div>
                    </div>
                </fieldset>

                <!-- Sección Información Académica y Previsional -->
                <fieldset class="mb-3">
                    <legend>Información Académica y Previsional</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nivelestudios" class="form-label">Nivel de Estudios <span class="text-danger">*</span></label>
                            <select class="form-select" id="nivelestudios" name="nivelestudios" required>
                                <option value="">Seleccionar...</option>
                                <option value="Bachiller">Bachiller</option>
                                <option value="Titulado">Titulado</option>
                                <option value="Abogado">Abogado</option>
                                <option value="Ingeniero">Ingeniero</option>
                                <!-- Agregar más niveles si es necesario -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regimenpension" class="form-label">Régimen de Pensión <span class="text-danger">*</span></label>
                            <select class="form-select" id="regimenpension" name="regimenpension" required>
                                <option value="">Seleccionar...</option>
                                <option value="AFP">AFP</option>
                                <option value="ONP">ONP</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fondopension" class="form-label">Fondo de Pensión (AFP/Otro) <span class="text-danger">*</span></label>
                            <select class="form-select" id="fondopension" name="fondopension" required>
                                 <option value="">Seleccionar...</option>
                                <option value="PROFUTURO">PROFUTURO</option>
                                <option value="PRIMA">PRIMA</option>
                                <option value="HABITAT">HABITAT</option>
                                <option value="INTEGRA">INTEGRA</option>
                                <option value="ONP">ONP (si aplica directamente)</option>
                                <option value="OTRO">OTRO (especificar si es necesario)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cussp" class="form-label">CUSSP</label>
                            <input type="text" class="form-control" id="cussp" name="cussp">
                        </div>
                    </div>
                </fieldset>

                <!-- Sección Foto y Estado -->
                <fieldset class="mb-3">
                    <legend>Foto y Estado</legend>
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="rutafoto" class="form-label">Foto del Empleado</label>
                            <input type="file" class="form-control" id="rutafoto" name="rutafoto" accept="image/*">
                            <small class="form-text text-muted">Formatos permitidos: JPG, PNG. Tamaño máx: 2MB.</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <img id="fotoPreview" src="img/fotos/empleados/usuario01.png" alt="Vista previa de foto" class="img-thumbnail" style="max-height: 150px; max-width: 150px;">
                        </div>
                         <div class="col-md-3 mb-3 align-self-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarRegistro" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" id="btnGuardarEmpleado" class="btn btn-primary">Guardar Empleado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
require_once 'includes/modales.php'; // Para modal de confirmación de cancelación
require_once 'includes/footer.php'; 
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rutafotoInput = document.getElementById('rutafoto');
    const fotoPreview = document.getElementById('fotoPreview');
    const defaultAvatar = 'img/fotos/empleados/default_avatar.png'; // Ruta a una imagen por defecto

    if (rutafotoInput) {
        rutafotoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                fotoPreview.src = defaultAvatar;
            }
        });
    }

    const btnCancelarRegistro = document.getElementById('btnCancelarRegistro');
    const modalCancelarElement = document.getElementById('modalCancelar'); 
    let modalCancelarInstance = modalCancelarElement ? new bootstrap.Modal(modalCancelarElement) : null;
    
    if (btnCancelarRegistro && modalCancelarInstance) {
        btnCancelarRegistro.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar el registro? Los datos no guardados se perderán.';
            
            let btnConfirmarCancelacion = modalCancelarElement.querySelector('#btnModalConfirmarCancelacion');
            if (!btnConfirmarCancelacion) {
                btnConfirmarCancelacion = document.createElement('button');
                btnConfirmarCancelacion.setAttribute('type', 'button');
                btnConfirmarCancelacion.setAttribute('id', 'btnModalConfirmarCancelacion');
                btnConfirmarCancelacion.classList.add('btn', 'btn-danger');
                btnConfirmarCancelacion.textContent = 'Sí, cancelar';
                const originalCancelButton = modalCancelarElement.querySelector('.modal-footer button[data-bs-dismiss="modal"]');
                if (originalCancelButton) {
                    originalCancelButton.insertAdjacentElement('beforebegin', btnConfirmarCancelacion);
                } else {
                    modalCancelarElement.querySelector('.modal-footer').appendChild(btnConfirmarCancelacion);
                }
            } else {
                btnConfirmarCancelacion.style.display = 'inline-block';
                const newBtn = btnConfirmarCancelacion.cloneNode(true); // Clonar para limpiar listeners
                btnConfirmarCancelacion.parentNode.replaceChild(newBtn, btnConfirmarCancelacion);
                btnConfirmarCancelacion = newBtn;
            }
            
            btnConfirmarCancelacion.addEventListener('click', function() {
                window.location.href = 'empleados.php';
            });
            
            const originalDeleteButton = modalCancelarElement.querySelector('form#formEliminar button[type="submit"]');
            if(originalDeleteButton) originalDeleteButton.style.display = 'none';
            const originalFormEliminar = modalCancelarElement.querySelector('form#formEliminar');
            if(originalFormEliminar) originalFormEliminar.style.display = 'none';

            modalCancelarInstance.show();
        });
    }

    const formEmpleado = document.getElementById('formEmpleado');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formEmpleado && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formEmpleado.addEventListener('submit', function (event) {
            event.preventDefault(); 
            
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            
            if (formEmpleado.querySelector('input[name="idempleado"]')) { 
               if(modalTitle) modalTitle.textContent = 'Confirmar Actualización';
               if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios en los datos del empleado?';
            } else {
               if(modalTitle) modalTitle.textContent = 'Confirmar Registro';
               if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar este nuevo empleado?';
            }
            modalConfirmarGuardadoInstance.show();
        });

        btnConfirmarGuardarSubmit.addEventListener('click', function () {
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formEmpleado.submit(); 
        });
    }
});
</script>
