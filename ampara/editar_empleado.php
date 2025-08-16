<?php
$page_title = "Editar Empleado";
require_once 'includes/header.php';
require_once 'funciones.php'; // Para obtenerEmpleadoPorId y otras que se necesiten

$idempleado = null;
$empleado = null;
$error_carga = '';

if (isset($_GET['id'])) {
    $idempleado = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idempleado) {
       // Cuando la función esté lista en funciones.php, se usará:
        $empleado = obtenerEmpleadoPorId($idempleado); 
        if (!$empleado) {
            $error_carga = "No se encontró el empleado con el ID proporcionado.";
            $_SESSION['mensaje_error'] = $error_carga;
        }
    } else {
        $error_carga = "ID de empleado no válido.";
        $_SESSION['mensaje_error'] = $error_carga;
    }
} else {
    $error_carga = "No se proporcionó ID de empleado.";
    $_SESSION['mensaje_error'] = $error_carga;
}

// Simulación de datos de empleado para pruebas de diseño
if (!$empleado && $idempleado) { 
    $empleado = [
        'idempleado' => $idempleado,
        'nombres' => 'Juan Alberto (Simulado)',
        'paterno' => 'Pérez',
        'materno' => 'González',
        'nombrecorto' => 'Juan Pérez S.',
        'dni' => '12345678',
        'nacimiento' => '1990-05-15',
        'lugarnacimiento' => 'Lima, Perú',
        'domicilio' => 'Av. Siempre Viva 742',
        'estadocivil' => 'Casado',
        'correopersonal' => 'juan.perez.personal@example.com',
        'correocorporativo' => 'juan.perez@empresa.com',
        'telcelular' => '987654321',
        'telfijo' => '014567890',
        'area' => 'Legal Regulatorio',
        'cargo' => 'Asociado',
        'derechohabiente' => 'Conyugue',
        'cantidadhijos' => 2,
        'contactoemergencia' => 'Maria Rodriguez - 999888777',
        'nivelestudios' => 'Abogado',
        'regimenpension' => 'AFP',
        'fondopension' => 'PRIMA',
        'cussp' => 'JP12345XYZ',
        'modalidad' => 'Planilla',
        'rutafoto' => 'img/fotos/empleados/usuario01.png', 
        'activo' => 1
    ];
       // Descomentar esta línea si quieres forzar que no haya datos simulados y probar el error
    $empleado = null; $error_carga = "Simulación de empleado no encontrado.";
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Editar Empleado: <?php echo htmlspecialchars($empleado['nombrecorto'] ?? 'N/A'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_carga) && !$empleado && !isset($_GET['id'])):
            // No se proveyó ID, $error_carga tendrá "No se proporcionó ID de empleado."
            ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_carga); ?></div>
                <a href="empleados.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif (!$empleado && isset($_GET['id'])):
                 // Se proveyó ID, pero $empleado es null (no se encontró o ID inválido)
                 // $error_carga puede tener "ID de empleado no válido" o un mensaje de obtenerEmpleadoPorId
                 $mensaje_a_mostrar = !empty($error_carga) ? $error_carga : "No se pudieron cargar los datos del empleado con ID " . htmlspecialchars($_GET['id']) . ". Verifique que el empleado exista.";
            ?>
                 <div class="alert alert-danger"><?php echo $mensaje_a_mostrar; ?></div>
                <a href="empleados.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif ($empleado): ?>
            <form id="formEmpleado" action="procesar_empleado.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="idempleado" value="<?php echo htmlspecialchars($empleado['idempleado']); ?>">
                <input type="hidden" name="rutafoto_actual" value="<?php echo htmlspecialchars($empleado['rutafoto'] ?? ''); ?>">

                <!-- Sección Información Personal -->
                <fieldset class="mb-3">
                    <legend>Información Personal</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required value="<?php echo htmlspecialchars($empleado['nombres'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="paterno" name="paterno" required value="<?php echo htmlspecialchars($empleado['paterno'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="materno" name="materno" required value="<?php echo htmlspecialchars($empleado['materno'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombrecorto" class="form-label">Nombre Corto</label>
                            <input type="text" class="form-control" id="nombrecorto" name="nombrecorto" value="<?php echo htmlspecialchars($empleado['nombrecorto'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dni" name="dni" required maxlength="10" value="<?php echo htmlspecialchars($empleado['dni'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="nacimiento" name="nacimiento" required value="<?php echo htmlspecialchars($empleado['nacimiento'] ?? ''); ?>">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="lugarnacimiento" class="form-label">Lugar de Nacimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lugarnacimiento" name="lugarnacimiento" required value="<?php echo htmlspecialchars($empleado['lugarnacimiento'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estadocivil" class="form-label">Estado Civil <span class="text-danger">*</span></label>
                            <select class="form-select" id="estadocivil" name="estadocivil" required>
                                <option value="">Seleccionar...</option>
                                <option value="Soltero" <?php echo (isset($empleado['estadocivil']) && $empleado['estadocivil'] == 'Soltero') ? 'selected' : ''; ?>>Soltero(a)</option>
                                <option value="Casado" <?php echo (isset($empleado['estadocivil']) && $empleado['estadocivil'] == 'Casado') ? 'selected' : ''; ?>>Casado(a)</option>
                                <option value="Viudo" <?php echo (isset($empleado['estadocivil']) && $empleado['estadocivil'] == 'Viudo') ? 'selected' : ''; ?>>Viudo(a)</option>
                                <option value="Divorciado" <?php echo (isset($empleado['estadocivil']) && $empleado['estadocivil'] == 'Divorciado') ? 'selected' : ''; ?>>Divorciado(a)</option>
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
                            <input type="text" class="form-control" id="domicilio" name="domicilio" required value="<?php echo htmlspecialchars($empleado['domicilio'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="correopersonal" class="form-label">Correo Personal <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="correopersonal" name="correopersonal" required value="<?php echo htmlspecialchars($empleado['correopersonal'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correocorporativo" class="form-label">Correo Corporativo <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="correocorporativo" name="correocorporativo" required value="<?php echo htmlspecialchars($empleado['correocorporativo'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telcelular" class="form-label">Teléfono Celular <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telcelular" name="telcelular" required value="<?php echo htmlspecialchars($empleado['telcelular'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telfijo" class="form-label">Teléfono Fijo</label>
                            <input type="tel" class="form-control" id="telfijo" name="telfijo" value="<?php echo htmlspecialchars($empleado['telfijo'] ?? ''); ?>">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="contactoemergencia" class="form-label">Contacto de Emergencia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contactoemergencia" name="contactoemergencia" required value="<?php echo htmlspecialchars($empleado['contactoemergencia'] ?? ''); ?>">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="cantidadhijos" class="form-label">Cantidad de Hijos <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidadhijos" name="cantidadhijos" min="0" value="<?php echo htmlspecialchars($empleado['cantidadhijos'] ?? '0'); ?>" required>
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
                                <option value="Técnico Regulatorio" <?php echo (isset($empleado['area']) && $empleado['area'] == 'Técnico Regulatorio') ? 'selected' : ''; ?>>Técnico Regulatorio</option>
                                <option value="Legal Regulatorio" <?php echo (isset($empleado['area']) && $empleado['area'] == 'Legal Regulatorio') ? 'selected' : ''; ?>>Legal Regulatorio</option>
                                <option value="Recursos Humanos" <?php echo (isset($empleado['area']) && $empleado['area'] == 'Recursos Humanos') ? 'selected' : ''; ?>>Recursos Humanos</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <select class="form-select" id="cargo" name="cargo" required>
                                <option value="">Seleccionar...</option>
                                <option value="Socio Fundador" <?php echo (isset($empleado['cargo']) && $empleado['cargo'] == 'Socio Fundador') ? 'selected' : ''; ?>>Socio Fundador</option>
                                <option value="Asociado" <?php echo (isset($empleado['cargo']) && $empleado['cargo'] == 'Asociado') ? 'selected' : ''; ?>>Asociado</option>
                                <option value="Asistente" <?php echo (isset($empleado['cargo']) && $empleado['cargo'] == 'Asistente') ? 'selected' : ''; ?>>Asistente</option>
                            </select>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modalidad" class="form-label">Modalidad <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalidad" name="modalidad" required>
                                <option value="">Seleccionar...</option>
                                <option value="Planilla" <?php echo (isset($empleado['modalidad']) && $empleado['modalidad'] == 'Planilla') ? 'selected' : ''; ?>>Planilla</option>
                                <option value="Recibo por Honorarios" <?php echo (isset($empleado['modalidad']) && $empleado['modalidad'] == 'Recibo por Honorarios') ? 'selected' : ''; ?>>Recibo por Honorarios</option>
                            </select>
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="derechohabiente" class="form-label">Derecho Habiente <span class="text-danger">*</span></label>
                            <select class="form-select" id="derechohabiente" name="derechohabiente" required>
                                <option value="">Seleccionar...</option>
                                <option value="No aplica" <?php echo (isset($empleado['derechohabiente']) && $empleado['derechohabiente'] == 'No aplica') ? 'selected' : ''; ?>>No aplica</option>
                                <option value="Conyugue" <?php echo (isset($empleado['derechohabiente']) && $empleado['derechohabiente'] == 'Conyugue') ? 'selected' : ''; ?>>Cónyuge</option>
                                <option value="Hijo" <?php echo (isset($empleado['derechohabiente']) && $empleado['derechohabiente'] == 'Hijo') ? 'selected' : ''; ?>>Hijo(a)</option>
                                <option value="Padre" <?php echo (isset($empleado['derechohabiente']) && $empleado['derechohabiente'] == 'Padre') ? 'selected' : ''; ?>>Padre/Madre</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="horasmeta" class="form-label">Meta de Horas Mensuales <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="horasmeta" name="horasmeta" min="0" value="<?php echo htmlspecialchars($empleado['horasmeta'] ?? '30'); ?>" required>
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
                                <option value="Bachiller" <?php echo (isset($empleado['nivelestudios']) && $empleado['nivelestudios'] == 'Bachiller') ? 'selected' : ''; ?>>Bachiller</option>
                                <option value="Titulado" <?php echo (isset($empleado['nivelestudios']) && $empleado['nivelestudios'] == 'Titulado') ? 'selected' : ''; ?>>Titulado</option>
                                <option value="Abogado" <?php echo (isset($empleado['nivelestudios']) && $empleado['nivelestudios'] == 'Abogado') ? 'selected' : ''; ?>>Abogado</option>
                                <option value="Ingeniero" <?php echo (isset($empleado['nivelestudios']) && $empleado['nivelestudios'] == 'Ingeniero') ? 'selected' : ''; ?>>Ingeniero</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regimenpension" class="form-label">Régimen de Pensión <span class="text-danger">*</span></label>
                            <select class="form-select" id="regimenpension" name="regimenpension" required>
                                <option value="">Seleccionar...</option>
                                <option value="AFP" <?php echo (isset($empleado['regimenpension']) && $empleado['regimenpension'] == 'AFP') ? 'selected' : ''; ?>>AFP</option>
                                <option value="ONP" <?php echo (isset($empleado['regimenpension']) && $empleado['regimenpension'] == 'ONP') ? 'selected' : ''; ?>>ONP</option>
                                <option value="OTRO" <?php echo (isset($empleado['regimenpension']) && $empleado['regimenpension'] == 'OTRO') ? 'selected' : ''; ?>>OTRO</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fondopension" class="form-label">Fondo de Pensión (AFP/Otro) <span class="text-danger">*</span></label>
                            <select class="form-select" id="fondopension" name="fondopension" required>
                                 <option value="">Seleccionar...</option>
                                <option value="PROFUTURO" <?php echo (isset($empleado['fondopension']) && $empleado['fondopension'] == 'PROFUTURO') ? 'selected' : ''; ?>>PROFUTURO</option>
                                <option value="PRIMA" <?php echo (isset($empleado['fondopension']) && $empleado['fondopension'] == 'PRIMA') ? 'selected' : ''; ?>>PRIMA</option>
                                <option value="HABITAT" <?php echo (isset($empleado['fondopension']) && $empleado['fondopension'] == 'HABITAT') ? 'selected' : ''; ?>>HABITAT</option>
                                <option value="INTEGRA" <?php echo (isset($empleado['fondopension']) && $empleado['fondopension'] == 'INTEGRA') ? 'selected' : ''; ?>>INTEGRA</option>
                                <option value="ONP" <?php echo (isset($empleado['fondopension']) && $empleado['fondopension'] == 'ONP') ? 'selected' : ''; ?>>ONP (si aplica directamente)</option>
                                <option value="OTRO" <?php echo (isset($empleado['fondopension']) && $empleado['fondopension'] == 'OTRO') ? 'selected' : ''; ?>>OTRO (especificar si es necesario)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cussp" class="form-label">CUSSP</label>
                            <input type="text" class="form-control" id="cussp" name="cussp" value="<?php echo htmlspecialchars($empleado['cussp'] ?? ''); ?>">
                        </div>
                    </div>
                </fieldset>

                <!-- Sección Foto y Estado -->
                <fieldset class="mb-3">
                    <legend>Foto y Estado</legend>
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="rutafoto" class="form-label">Foto del Empleado (dejar vacío para no cambiar)</label>
                            <input type="file" class="form-control" id="rutafoto" name="rutafoto" accept="image/*">
                            <small class="form-text text-muted">Formatos permitidos: JPG, PNG. Tamaño máx: 2MB.</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Foto Actual:</label><br>
                            <img id="fotoPreview" 
                                 src="<?php echo (!empty($empleado['rutafoto']) && file_exists($empleado['rutafoto'])) ? htmlspecialchars($empleado['rutafoto']) : 'img/fotos/empleados/default_avatar.png'; ?>" 
                                 alt="Foto Actual" class="img-thumbnail" style="max-height: 150px; max-width: 150px;">
                        </div>
                         <div class="col-md-3 mb-3 align-self-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?php echo (isset($empleado['activo']) && $empleado['activo'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarEdicion" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" id="btnActualizarEmpleado" class="btn btn-primary">Actualizar Empleado</button>
                </div>
            </form>
            <?php else: // Este else es por si $empleado no está definido y no hubo error específico de ID (ej. no se pasó ID)
            ?> 
                <div class="alert alert-info">Por favor, seleccione un empleado de la lista para editar o verifique el ID proporcionado.</div>
                <a href="empleados.php" class="btn btn-primary">Volver a la lista</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
require_once 'includes/modales.php'; 
require_once 'includes/footer.php'; 
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rutafotoInput = document.getElementById('rutafoto');
    const fotoPreview = document.getElementById('fotoPreview');
    const defaultAvatar = 'img/fotos/empleados/usuario01.png';
    const fotoActualSrc = fotoPreview ? fotoPreview.src : defaultAvatar; 

    if (rutafotoInput && fotoPreview) {
        rutafotoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                fotoPreview.src = fotoActualSrc || defaultAvatar;
            }
        });
    }

    const btnCancelarEdicion = document.getElementById('btnCancelarEdicion');
    const modalCancelarElement = document.getElementById('modalCancelar');
    
    if (btnCancelarEdicion && modalCancelarElement) {
        let modalCancelarInstance = new bootstrap.Modal(modalCancelarElement);
        btnCancelarEdicion.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar la edición? Los cambios no guardados se perderán.';

            if(modalFooter) modalFooter.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" id="btnModalConfirmarCancelacionEdicion" class="btn btn-danger">Sí, cancelar</button> 
            `;

            const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionEdicion');
            if(btnConfirmarSi){
                 btnConfirmarSi.addEventListener('click', function() {
                    window.location.href = 'empleados.php';
                }, { once: true });
            }
            modalCancelarInstance.show();
        });
    }
    
    const formEmpleado = document.getElementById('formEmpleado');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formEmpleado && modalConfirmarGuardadoElement && btnConfirmarGuardarSubmit) {
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
