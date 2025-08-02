<?php
$page_title = "Registrar Nuevo Contrato de Cliente";
require_once 'includes/header.php';
require_once 'funciones.php'; 

$clientes = obtenerTodosClientes_crud(['activo' => 1]); // Filtrar por activos
$lideres = obtenerEmpleadosActivosParaSelect();
$clientes_simulados = [
    ['idcliente' => 1, 'nombrecomercial' => 'ABC Corp'],
    ['idcliente' => 2, 'nombrecomercial' => 'XYZ Servicios']
];
$lideres_simulados = [
    ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez'],
    ['idempleado' => 2, 'nombrecorto' => 'Ana López']
];
$clientes = $clientes;
$lideres = $lideres;
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Registrar Nuevo Contrato de Cliente</h3>
        </div>
        <div class="card-body">
            <form id="formContratoCliente" action="procesar_contrato_cliente.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <fieldset class="mb-3">
                    <legend>Información Principal del Contrato</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="idcliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" id="idcliente" name="idcliente" required>
                                <option value="">Seleccionar Cliente...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo htmlspecialchars($cliente['idcliente']); ?>">
                                        <?php echo htmlspecialchars($cliente['nombrecomercial']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lider" class="form-label">Líder del Contrato <span class="text-danger">*</span></label>
                            <select class="form-select" id="lider" name="lider" required>
                                <option value="">Seleccionar Líder...</option>
                                <?php foreach ($lideres as $lider): ?>
                                    <option value="<?php echo htmlspecialchars($lider['idempleado']); ?>">
                                        <?php echo htmlspecialchars($lider['nombrecorto']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción del Contrato <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required maxlength="500"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fechainicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fechainicio" name="fechainicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fechafin" class="form-label">Fecha de Fin (Opcional)</label>
                            <input type="date" class="form-control" id="fechafin" name="fechafin">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Condiciones Económicas y Horas</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="horasfijasmes" class="form-label">Horas Fijas/Mes <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="horasfijasmes" name="horasfijasmes" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="costohorafija" class="form-label">Costo por Hora Fija <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="costohorafija" name="costohorafija" step="0.01" min="0" required>
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="montofijomes" class="form-label">Monto Fijo Mensual</label>
                            <input type="number" class="form-control" id="montofijomes" name="montofijomes" step="0.01" min="0" readonly title="Se calcula: Horas Fijas * Costo Hora Fija">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="mesescontrato" class="form-label">Meses de Contrato <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="mesescontrato" name="mesescontrato" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="totalhorasfijas" class="form-label">Total Horas Fijas Contrato</label>
                            <input type="number" class="form-control" id="totalhorasfijas" name="totalhorasfijas" min="0" readonly title="Se calcula: Horas Fijas/Mes * Meses Contrato">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="costohoraextra" class="form-label">Costo Hora Extra <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="costohoraextra" name="costohoraextra" step="0.01" min="0" required>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipobolsa" class="form-label">Tipo de Bolsa / Adicional</label>
                            <input type="text" class="form-control" id="tipobolsa" name="tipobolsa" maxlength="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipohora" class="form-label">Tipo de Hora (Facturación) <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipohora" name="tipohora" required>
                                <option value="">Seleccionar...</option>
                                <option value="Soporte">Soporte</option>
                                <option value="No Soporte">No Soporte</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Planificación y Estado</legend>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="planmontomes" class="form-label">Plan Monto/Mes (Facturación) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="planmontomes" name="planmontomes" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="planhoraextrames" class="form-label">Plan Horas Extra/Mes <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="planhoraextrames" name="planhoraextrames" min="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status del Contrato <span class="text-danger">*</span></label>
                             <input type="text" class="form-control" id="status" name="status" maxlength="50" required placeholder="Ej: Vigente, Finalizado, Pendiente">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-center">
                             <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="activo">Contrato Activo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarContrato" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Contrato</button>
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
    
    // Calcular al cargar por si hay valores prellenados (en editar)
    // calcularValores(); // Se activará en editar_contrato_cliente.php

    // Lógica para el botón Cancelar
    const btnCancelarContrato = document.getElementById('btnCancelarContrato');
    const modalCancelarElement = document.getElementById('modalCancelar');
    let modalCancelarInstance = modalCancelarElement ? new bootstrap.Modal(modalCancelarElement) : null;

    if (btnCancelarContrato && modalCancelarInstance) {
        btnCancelarContrato.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar el registro del contrato? Los datos no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionContrato" class="btn btn-danger">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionContrato');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'contratos_clientes.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    // Lógica para el modal de Confirmar Guardado
    const formContrato = document.getElementById('formContratoCliente');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formContrato && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formContrato.addEventListener('submit', function(event) {
            event.preventDefault();
            // Recalcular por si acaso antes de enviar
            // calcularValores(); // Se hará en el backend de todas formas

            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            
            if(modalTitle) modalTitle.textContent = 'Confirmar Registro de Contrato';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar este nuevo contrato?';
            
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
