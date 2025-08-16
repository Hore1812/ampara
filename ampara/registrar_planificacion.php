<?php
$page_title = "Registrar Nueva Planificación";
require_once 'includes/header.php';
require_once 'funciones.php'; // Para obtenerContratosActivosParaSelect()

$contratos = obtenerContratosActivosParaSelect();

// Para repoblar el formulario en caso de error
$form_data = $_SESSION['form_data_planificacion'] ?? [];
unset($_SESSION['form_data_planificacion']);

$current_year = date('Y');
$current_month = date('m');

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
        <div class="card-header bg-success text-white">
            <h3>Registrar Nueva Planificación Mensual</h3>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['mensaje_error_flash_planif'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error_flash_planif']);?>
                </div>
            <?php endif; ?>

            <form id="formPlanificacion" action="procesar_planificacion.php" method="POST">
                <input type="hidden" name="accion" value="registrar">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="idcontratocli" class="form-label">Contrato del Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="idcontratocli" name="idcontratocli" required>
                            <option value="">Seleccione un contrato...</option>
                            <?php foreach ($contratos as $contrato): ?>
                                <option value="<?php echo htmlspecialchars($contrato['idcontratocli']); ?>" 
                                        <?php echo (isset($form_data['idcontratocli']) && $form_data['idcontratocli'] == $contrato['idcontratocli']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($contrato['nombre_cliente'] . " - " . $contrato['descripcion_completa_contrato']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre de la Planificación <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="150" value="<?php echo htmlspecialchars($form_data['nombre'] ?? 'Plan Mensual'); ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="anio_planificado" class="form-label">Año Planificado <span class="text-danger">*</span></label>
                        <select name="anio_planificado" id="anio_planificado" class="form-select" required>
                            <?php foreach ($anios_disponibles as $anio_opt): ?>
                                <option value="<?php echo $anio_opt; ?>" <?php echo (isset($form_data['anio_planificado']) ? ($form_data['anio_planificado'] == $anio_opt) : ($anio_opt == $current_year)) ? 'selected' : ''; ?>>
                                    <?php echo $anio_opt; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="mes_planificado" class="form-label">Mes Planificado <span class="text-danger">*</span></label>
                        <select name="mes_planificado" id="mes_planificado" class="form-select" required>
                            <?php foreach ($meses_espanol as $num => $nombre): ?>
                                <option value="<?php echo $num; ?>" <?php echo (isset($form_data['mes_planificado']) ? ($form_data['mes_planificado'] == $num) : ($num == $current_month)) ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label for="horas_planificadas" class="form-label">Horas Planificadas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="horas_planificadas" name="horas_planificadas" required min="0" step="0.01" value="<?php echo htmlspecialchars($form_data['horas_planificadas'] ?? '0.00'); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario (Opcional)</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3"><?php echo htmlspecialchars($form_data['comentario'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?php echo (!isset($form_data['activo']) || $form_data['activo'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Planificación Activa</label>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarPlanificacion" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Planificación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
require_once 'includes/modales.php'; 
require_once 'includes/footer.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCancelar = document.getElementById('btnCancelarPlanificacion');
    const modalCancelarElement = document.getElementById('modalCancelar'); // Usamos el modal genérico
    let modalCancelarInstance = modalCancelarElement ? new bootstrap.Modal(modalCancelarElement) : null;

    if (btnCancelar && modalCancelarInstance) {
        btnCancelar.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar el registro? Los datos no guardados se perderán.';
            
            if(modalFooter) {
                // Limpiar botones anteriores y añadir los específicos para esta acción
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacion" class="btn btn-warning">Sí, cancelar</button>
                `;
                // Añadir event listener al nuevo botón "Sí, cancelar"
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacion');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'planificaciones.php'; // Redirigir a la lista de planificaciones
                    }, { once: true }); // { once: true } para que el listener se ejecute una vez y se auto-elimine
                }
            }
            modalCancelarInstance.show();
        });
    }
    
    // Lógica para el modal de Confirmar Guardado (usa el modal genérico)
    const formPlanificacion = document.getElementById('formPlanificacion');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formPlanificacion && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formPlanificacion.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevenir el envío real del formulario
            
            // Personalizar el modal de confirmación si es necesario
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            if(modalTitle) modalTitle.textContent = 'Confirmar Registro de Planificación';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar esta nueva planificación?';
            
            modalConfirmarGuardadoInstance.show();
        });

        // El botón #btnConfirmarGuardarSubmit ya tiene un listener en modal_confirm_logic.js
        // que hará formPlanificacion.submit() cuando se usa un formulario con ID 'formLiquidacion'.
        // Necesitamos asegurar que también funcione para 'formPlanificacion' o hacer la lógica más genérica.
        // Por ahora, para ser explícitos, añadimos un listener aquí que llame a submit.
        // O mejor, modificamos modal_confirm_logic.js para que sea más genérico.
        // Por simplicidad aquí, si el ID del form es 'formPlanificacion', el botón del modal hará el submit.
        // Esto asume que modal_confirm_logic.js no interfiere si el ID del form es diferente.
        // Si btnConfirmarGuardarSubmit ya tiene un listener en modal_confirm_logic.js,
        // este podría ser redundante o necesitar coordinación.
        // Se asume que modal_confirm_logic.js es para #formLiquidacion
        // y aquí manejamos #formPlanificacion.
        
        // Si el botón de submit del modal es genérico y se espera que funcione para cualquier form,
        // el event listener del submit del form que muestra el modal es suficiente.
        // El botón del modal simplemente llamaría .submit() sobre el form activo.
        // Para este caso, nos aseguramos que el botón de "Sí, guardar" del modal haga el submit del formPlanificacion
         $(btnConfirmarGuardarSubmit).off('click').on('click', function() { // Desvincula handlers previos y añade el nuestro
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formPlanificacion.submit(); 
        });
    }
});
</script>
