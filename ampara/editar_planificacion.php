<?php
$page_title = "Editar Planificación";
require_once 'includes/header.php';
require_once 'funciones.php';

$idplanificacion = null;
$planificacion_actual = null;
$error_carga = '';

if (isset($_GET['id'])) {
    $idplanificacion = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idplanificacion) {
        $planificacion_actual = obtenerPlanificacionPorId($idplanificacion);
        if (!$planificacion_actual) {
            $error_carga = "No se encontró la planificación con el ID proporcionado.";
            $_SESSION['mensaje_error'] = $error_carga;
        }
    } else {
        $error_carga = "ID de planificación no válido.";
        $_SESSION['mensaje_error'] = $error_carga;
    }
} else {
    $error_carga = "No se proporcionó ID de planificación.";
    $_SESSION['mensaje_error'] = $error_carga;
}

// Para repoblar el formulario en caso de error de validación
// Prioriza $_SESSION['form_data_planificacion'], luego $planificacion_actual
$form_data = $_SESSION['form_data_planificacion'] ?? $planificacion_actual;

// Si $form_data proviene de $planificacion_actual (carga inicial, no un reintento después de error de POST),
// necesitamos asegurar que 'idcontratocli' (usado en el select) se popule desde 'idcontratocliente' (de la BD).
if ($planificacion_actual && !isset($_SESSION['form_data_planificacion'])) {
    $form_data['idcontratocli'] = $planificacion_actual['idcontratocliente'];
}


if (isset($form_data['mes_planificado']) && !isset($form_data['anio_planificado_val'])) { // Asegurar que mes y año se separen si vienen de la BD y no de un POST previo
    $fecha_plan_obj = new DateTime($form_data['mes_planificado']);
    $form_data['anio_planificado_val'] = $fecha_plan_obj->format('Y');
    $form_data['mes_planificado_val'] = $fecha_plan_obj->format('m');
}
unset($_SESSION['form_data_planificacion']);


$contratos = obtenerContratosActivosParaSelect();
$current_year = date('Y');
$anios_disponibles = [];
for ($i = $current_year - 2; $i <= $current_year + 5; $i++) {
    $anios_disponibles[] = $i;
}
$meses_espanol = [
    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];

?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h3>Editar Planificación: <?php echo htmlspecialchars($planificacion_actual['nombre_planificacion'] ?? 'N/A'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_carga) && !$planificacion_actual): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_carga); ?></div>
                <a href="planificaciones.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif (!$planificacion_actual && isset($_GET['id'])): ?>
                 <div class="alert alert-danger">No se pudieron cargar los datos de la planificación. Verifique el ID.</div>
                <a href="planificaciones.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif ($planificacion_actual): ?>

            <?php if (isset($_SESSION['mensaje_error_flash_planif'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error_flash_planif']);?>
                </div>
            <?php endif; ?>

            <form id="formPlanificacionEditar" action="procesar_planificacion.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="idplanificacion" value="<?php echo htmlspecialchars($planificacion_actual['idplanificacion']); ?>">
                <!-- Campo oculto para enviar el idcontratocli ya que el select estará deshabilitado -->
                <input type="hidden" name="idcontratocli" value="<?php echo htmlspecialchars($planificacion_actual['idcontratocliente']); ?>">


                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="idcontratocli_disabled" class="form-label">Contrato del Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="idcontratocli_disabled" name="idcontratocli_disabled" required disabled>
                            <option value="">Seleccione un contrato...</option>
                            <?php foreach ($contratos as $contrato): ?>
                                <option value="<?php echo htmlspecialchars($contrato['idcontratocli']); ?>" 
                                        <?php 
                                        $id_contrato_a_seleccionar = $form_data['idcontratocli'] ?? '';
                                        echo ($id_contrato_a_seleccionar == $contrato['idcontratocli']) ? 'selected' : ''; 
                                        ?>>
                                    <?php echo htmlspecialchars($contrato['nombre_cliente'] . " - " . $contrato['descripcion_completa_contrato']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre de la Planificación <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="150" value="<?php echo htmlspecialchars($form_data['nombre_planificacion'] ?? ($planificacion_actual['nombre_planificacion'] ?? '')); ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="anio_planificado" class="form-label">Año Planificado <span class="text-danger">*</span></label>
                        <select name="anio_planificado" id="anio_planificado" class="form-select" required>
                            <?php 
                            $anio_seleccionado = $form_data['anio_planificado_val'] ?? ($planificacion_actual ? (new DateTime($planificacion_actual['mes_planificado']))->format('Y') : date('Y'));
                            foreach ($anios_disponibles as $anio_opt): ?>
                                <option value="<?php echo $anio_opt; ?>" <?php echo ($anio_seleccionado == $anio_opt) ? 'selected' : ''; ?>>
                                    <?php echo $anio_opt; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="mes_planificado" class="form-label">Mes Planificado <span class="text-danger">*</span></label>
                        <select name="mes_planificado" id="mes_planificado" class="form-select" required>
                            <?php 
                            $mes_seleccionado = $form_data['mes_planificado_val'] ?? ($planificacion_actual ? (new DateTime($planificacion_actual['mes_planificado']))->format('m') : date('m'));
                            foreach ($meses_espanol as $num => $nombre): ?>
                                <option value="<?php echo $num; ?>" <?php echo ($mes_seleccionado == $num) ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label for="horas_planificadas" class="form-label">Horas Planificadas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="horas_planificadas" name="horas_planificadas" required min="0" step="1" value="<?php echo htmlspecialchars(isset($form_data['horas_planificadas']) ? number_format($form_data['horas_planificadas'], 2, '.', '') : ($planificacion_actual ? number_format($planificacion_actual['horas_planificadas'], 2, '.', '') : '0.00')); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario (Opcional)</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3"><?php echo htmlspecialchars($form_data['comentario'] ?? ($planificacion_actual['comentario'] ?? '')); ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" 
                               <?php 
                               $activo_val = $form_data['activo'] ?? ($planificacion_actual['activo'] ?? 1); // Default a 1 (activo) si no hay datos
                               echo ($activo_val == 1) ? 'checked' : ''; 
                               ?>>
                        <label class="form-check-label" for="activo">Planificación Activa</label>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarPlanificacionEdicion" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Planificación</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">No se pudo cargar la planificación para editar.</div>
                <a href="planificaciones.php" class="btn btn-primary">Volver a la lista</a>
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
    const btnCancelarEdicion = document.getElementById('btnCancelarPlanificacionEdicion');
    const modalCancelarElement = document.getElementById('modalCancelar'); // Usamos el modal genérico
    let modalCancelarInstance = modalCancelarElement ? new bootstrap.Modal(modalCancelarElement) : null;

    if (btnCancelarEdicion && modalCancelarInstance) {
        btnCancelarEdicion.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar la edición? Los cambios no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionEdicion" class="btn btn-warning">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionEdicion');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'planificaciones.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }
    
    const formPlanificacionEditar = document.getElementById('formPlanificacionEditar');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formPlanificacionEditar && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formPlanificacionEditar.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            if(modalTitle) modalTitle.textContent = 'Confirmar Actualización';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios en esta planificación?';
            
            modalConfirmarGuardadoInstance.show();
        });

        $(btnConfirmarGuardarSubmit).off('click').on('click', function() { 
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formPlanificacionEditar.submit(); 
        });
    }
});
</script>
