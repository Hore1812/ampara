<?php
$page_title = "Editar Contrato de Cliente";
require_once 'includes/header.php';
require_once 'funciones.php';

$idcontratocli = null;
$contrato_actual = null;
$error_carga = '';

if (isset($_GET['id'])) {
    $idcontratocli = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idcontratocli) {
        $contrato_actual = obtenerContratoClientePorId($idcontratocli); // Descomentar
        if (!$contrato_actual) {
            $error_carga = "No se encontró el contrato con el ID proporcionado.";
            $_SESSION['mensaje_error'] = $error_carga;
        }
    } else {
        $error_carga = "ID de contrato no válido.";
        $_SESSION['mensaje_error'] = $error_carga;
    }
} else {
    $error_carga = "No se proporcionó ID de contrato.";
    $_SESSION['mensaje_error'] = $error_carga;
}

// Simulación de datos para diseño
// if (!$contrato_actual && $idcontratocli) {
//     $contrato_actual = [
//         'idcontratocli' => $idcontratocli,
//         'idcliente' => 1,
//         'lider' => 1,
//         'descripcion' => 'Contrato de soporte mensual estándar (Editado)',
//         'fechainicio' => '2023-01-15',
//         'fechafin' => '2023-12-31',
//         'horasfijasmes' => 40,
//         'costohorafija' => 50.00,
//         'mesescontrato' => 12,
//         'totalhorasfijas' => 480, // 40 * 12
//         'tipobolsa' => 'Retainer Mensual',
//         'costohoraextra' => 65.00,
//         'montofijomes' => 2000.00, // 40 * 50
//         'planmontomes' => 2500.00,
//         'planhoraextrames' => 10,
//         'status' => 'Vigente',
//         'tipohora' => 'Soporte',
//         'activo' => 1
//     ];
// }

$clientes = obtenerTodosClientes_crud(['activo' => 1]);
$lideres = obtenerEmpleadosActivosParaSelect();
// $clientes_simulados = [
//     ['idcliente' => 1, 'nombrecomercial' => 'ABC Corp'],
//     ['idcliente' => 2, 'nombrecomercial' => 'XYZ Servicios']
// ];
// $lideres_simulados = [
//     ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez'],
//     ['idempleado' => 2, 'nombrecorto' => 'Ana López']
// ];
$clientes = $clientes;
$lideres = $lideres;

?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Editar Contrato de Cliente #<?php echo htmlspecialchars($contrato_actual['idcontratocli'] ?? 'N/A'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_carga) && !$contrato_actual): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_carga); ?></div>
                <a href="contratos_clientes.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif (!$contrato_actual && isset($_GET['id'])): ?>
                 <div class="alert alert-danger">No se pudieron cargar los datos del contrato. Verifique el ID.</div>
                <a href="contratos_clientes.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif ($contrato_actual): ?>
            <form id="formContratoCliente" action="procesar_contrato_cliente.php" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="idcontratocli" value="<?php echo htmlspecialchars($contrato_actual['idcontratocli']); ?>">

                <fieldset class="mb-3">
                    <legend>Información Principal del Contrato</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="idcliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" id="idcliente" name="idcliente" required>
                                <option value="">Seleccionar Cliente...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo htmlspecialchars($cliente['idcliente']); ?>" <?php echo (isset($contrato_actual['idcliente']) && $contrato_actual['idcliente'] == $cliente['idcliente']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nombrecomercial']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lider" class="form-label">Líder del Contrato <span class="text-danger">*</span></label>
                            <select class="form-select" id="lider" name="lider" required>
                                <option value="">Seleccionar Líder...</option>
                                <?php foreach ($lideres as $lider_emp): ?>
                                    <option value="<?php echo htmlspecialchars($lider_emp['idempleado']); ?>" <?php echo (isset($contrato_actual['lider']) && $contrato_actual['lider'] == $lider_emp['idempleado']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lider_emp['nombrecorto']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción del Contrato <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required maxlength="500"><?php echo htmlspecialchars($contrato_actual['descripcion'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fechainicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fechainicio" name="fechainicio" required value="<?php echo htmlspecialchars($contrato_actual['fechainicio'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fechafin" class="form-label">Fecha de Fin (Opcional)</label>
                            <input type="date" class="form-control" id="fechafin" name="fechafin" value="<?php echo htmlspecialchars($contrato_actual['fechafin'] ?? ''); ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Condiciones Económicas y Horas</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="horasfijasmes" class="form-label">Horas Fijas/Mes <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="horasfijasmes" name="horasfijasmes" min="0" required value="<?php echo htmlspecialchars($contrato_actual['horasfijasmes'] ?? '0'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="costohorafija" class="form-label">Costo por Hora Fija <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="costohorafija" name="costohorafija" step="0.01" min="0" required value="<?php echo htmlspecialchars($contrato_actual['costohorafija'] ?? '0.00'); ?>">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="montofijomes" class="form-label">Monto Fijo Mensual</label>
                            <input type="number" class="form-control" id="montofijomes" name="montofijomes" step="0.01" min="0" readonly title="Se calcula: Horas Fijas * Costo Hora Fija" value="<?php echo htmlspecialchars($contrato_actual['montofijomes'] ?? '0.00'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="mesescontrato" class="form-label">Meses de Contrato <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="mesescontrato" name="mesescontrato" min="1" required value="<?php echo htmlspecialchars($contrato_actual['mesescontrato'] ?? '1'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="totalhorasfijas" class="form-label">Total Horas Fijas Contrato</label>
                            <input type="number" class="form-control" id="totalhorasfijas" name="totalhorasfijas" min="0" readonly title="Se calcula: Horas Fijas/Mes * Meses Contrato" value="<?php echo htmlspecialchars($contrato_actual['totalhorasfijas'] ?? '0'); ?>">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="costohoraextra" class="form-label">Costo Hora Extra <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="costohoraextra" name="costohoraextra" step="0.01" min="0" required value="<?php echo htmlspecialchars($contrato_actual['costohoraextra'] ?? '0.00'); ?>">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipobolsa" class="form-label">Tipo de Bolsa / Adicional</label>
                            <input type="text" class="form-control" id="tipobolsa" name="tipobolsa" maxlength="50" value="<?php echo htmlspecialchars($contrato_actual['tipobolsa'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipohora" class="form-label">Tipo de Hora (Facturación) <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipohora" name="tipohora" required>
                                <option value="">Seleccionar...</option>
                                <option value="Soporte" <?php echo (isset($contrato_actual['tipohora']) && $contrato_actual['tipohora'] == 'Soporte') ? 'selected' : ''; ?>>Soporte</option>
                                <option value="No Soporte" <?php echo (isset($contrato_actual['tipohora']) && $contrato_actual['tipohora'] == 'No Soporte') ? 'selected' : ''; ?>>No Soporte</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Planificación y Estado</legend>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="planmontomes" class="form-label">Plan Monto/Mes (Facturación) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="planmontomes" name="planmontomes" step="0.01" min="0" required value="<?php echo htmlspecialchars($contrato_actual['planmontomes'] ?? '0.00'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="planhoraextrames" class="form-label">Plan Horas Extra/Mes <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="planhoraextrames" name="planhoraextrames" min="0" required value="<?php echo htmlspecialchars($contrato_actual['planhoraextrames'] ?? '0'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status del Contrato <span class="text-danger">*</span></label>
                             <input type="text" class="form-control" id="status" name="status" maxlength="50" required placeholder="Ej: Vigente, Finalizado, Pendiente" value="<?php echo htmlspecialchars($contrato_actual['status'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-center">
                             <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?php echo (isset($contrato_actual['activo']) && $contrato_actual['activo'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">Contrato Activo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarContrato" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Contrato</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">No se proporcionó un ID de contrato válido o el contrato no fue encontrado.</div>
                <a href="contratos_clientes.php" class="btn btn-primary">Volver a la lista</a>
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
    const horasFijasMesInput = document.getElementById('horasfijasmes');
    const costoHoraFijaInput = document.getElementById('costohorafija');
    const montoFijoMesInput = document.getElementById('montofijomes');
    const mesesContratoInput = document.getElementById('mesescontrato');
    const totalHorasFijasInput = document.getElementById('totalhorasfijas');

    function calcularValores() {
        const horasFijas = parseFloat(horasFijasMesInput.value) || 0;
        const costoHora = parseFloat(costoHoraFijaInput.value) || 0;
        const meses = parseInt(mesesContratoInput.value) || 0;

        if (montoFijoMesInput) {
            montoFijoMesInput.value = (horasFijas * costoHora).toFixed(2);
        }
        if (totalHorasFijasInput) {
            totalHorasFijasInput.value = (horasFijas * meses);
        }
    }

    if (horasFijasMesInput) horasFijasMesInput.addEventListener('input', calcularValores);
    if (costoHoraFijaInput) costoHoraFijaInput.addEventListener('input', calcularValores);
    if (mesesContratoInput) mesesContratoInput.addEventListener('input', calcularValores);
    
    // Calcular al cargar por si hay valores prellenados
    if(horasFijasMesInput && costoHoraFijaInput && mesesContratoInput) { // Asegurar que todos los inputs existan
        calcularValores();
    }


    const btnCancelarContrato = document.getElementById('btnCancelarContrato');
    const modalCancelarElement = document.getElementById('modalCancelar');
    let modalCancelarInstance = modalCancelarElement ? new bootstrap.Modal(modalCancelarElement) : null;

    if (btnCancelarContrato && modalCancelarInstance) {
        btnCancelarContrato.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar la edición del contrato? Los cambios no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionContratoEd" class="btn btn-warning">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionContratoEd');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'contratos_clientes.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    const formContrato = document.getElementById('formContratoCliente');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formContrato && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formContrato.addEventListener('submit', function(event) {
            event.preventDefault();
            // Recalcular por si acaso antes de enviar
            if(typeof calcularValores === "function") calcularValores();


            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            
            if(modalTitle) modalTitle.textContent = 'Confirmar Actualización de Contrato';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios de este contrato?';
            
            modalConfirmarGuardadoInstance.show();
        });

        btnConfirmarGuardarSubmit.addEventListener('click', function() {
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formContrato.submit(); 
        });
    }
});
</script>
