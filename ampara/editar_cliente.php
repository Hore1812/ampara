<?php
$page_title = "Editar Cliente";
require_once 'includes/header.php';
require_once 'funciones.php';

$idcliente = null;
$cliente_actual = null;
$error_carga = '';

if (isset($_GET['id'])) {
    $idcliente = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idcliente) {
        $cliente_actual = obtenerClientePorId($idcliente); // Descomentar cuando la función esté lista
        if (!$cliente_actual) {
            $error_carga = "No se encontró el cliente con el ID proporcionado.";
            $_SESSION['mensaje_error'] = $error_carga;
        }
    } else {
        $error_carga = "ID de cliente no válido.";
        $_SESSION['mensaje_error'] = $error_carga;
    }
} else {
    $error_carga = "No se proporcionó ID de cliente.";
    $_SESSION['mensaje_error'] = $error_carga;
}

// Simulación de datos para diseñoa
if (!$cliente_actual && $idcliente) {
    $cliente_actual = [
        'idcliente' => $idcliente,
        'razonsocial' => 'EMPRESA ABC S.A.C. (Edit)',
        'nombrecomercial' => 'ABC Corp (Edit)',
        'ruc' => '20123456789',
        'direccion' => 'Av. Falsa 123, Lima',
        'telefono' => '01-555-1234',
        'sitioweb' => 'https://www.abc-corp.com',
        'representante' => 'Juan Pérez Editado',
        'telrepresentante' => '999888777',
        'correorepre' => 'jperez@abccorp.com',
        'gerente' => 'Gerente General Editado',
        'telgerente' => '999111222',
        'correogerente' => 'gerencia@abccorp.com',
        'activo' => 1
    ];
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Editar Cliente: <?php echo htmlspecialchars($cliente_actual['nombrecomercial'] ?? 'N/A'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_carga) && !$cliente_actual): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_carga); ?></div>
                <a href="clientes.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif (!$cliente_actual && isset($_GET['id'])): ?>
                 <div class="alert alert-danger">No se pudieron cargar los datos del cliente. Verifique el ID.</div>
                <a href="clientes.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif ($cliente_actual): ?>
            <form id="formCliente" action="procesar_cliente.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="idcliente" value="<?php echo htmlspecialchars($cliente_actual['idcliente']); ?>">

                <fieldset class="mb-3">
                    <legend>Información General</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="razonsocial" class="form-label">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="razonsocial" name="razonsocial" required maxlength="50" value="<?php echo htmlspecialchars($cliente_actual['razonsocial'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombrecomercial" class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombrecomercial" name="nombrecomercial" required maxlength="50" value="<?php echo htmlspecialchars($cliente_actual['nombrecomercial'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ruc" class="form-label">RUC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ruc" name="ruc" required maxlength="15" value="<?php echo htmlspecialchars($cliente_actual['ruc'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="15" value="<?php echo htmlspecialchars($cliente_actual['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required maxlength="150" value="<?php echo htmlspecialchars($cliente_actual['direccion'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="sitioweb" class="form-label">Sitio Web</label>
                        <input type="url" class="form-control" id="sitioweb" name="sitioweb" maxlength="150" placeholder="https://www.ejemplo.com" value="<?php echo htmlspecialchars($cliente_actual['sitioweb'] ?? ''); ?>">
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Contacto Principal (Representante)</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="representante" class="form-label">Nombre del Representante</label>
                            <input type="text" class="form-control" id="representante" name="representante" maxlength="100" value="<?php echo htmlspecialchars($cliente_actual['representante'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telrepresentante" class="form-label">Teléfono del Representante</label>
                            <input type="tel" class="form-control" id="telrepresentante" name="telrepresentante" maxlength="15" value="<?php echo htmlspecialchars($cliente_actual['telrepresentante'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correorepre" class="form-label">Correo del Representante</label>
                        <input type="email" class="form-control" id="correorepre" name="correorepre" maxlength="150" value="<?php echo htmlspecialchars($cliente_actual['correorepre'] ?? ''); ?>">
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Contacto Gerente</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gerente" class="form-label">Nombre del Gerente</label>
                            <input type="text" class="form-control" id="gerente" name="gerente" maxlength="150" value="<?php echo htmlspecialchars($cliente_actual['gerente'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telgerente" class="form-label">Teléfono del Gerente</label>
                            <input type="tel" class="form-control" id="telgerente" name="telgerente" maxlength="15" value="<?php echo htmlspecialchars($cliente_actual['telgerente'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correogerente" class="form-label">Correo del Gerente</label>
                        <input type="email" class="form-control" id="correogerente" name="correogerente" maxlength="150" value="<?php echo htmlspecialchars($cliente_actual['correogerente'] ?? ''); ?>">
                    </div>
                </fieldset>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?php echo (isset($cliente_actual['activo']) && $cliente_actual['activo'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Cliente Activo</label>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarCliente" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">No se proporcionó un ID de cliente válido o el cliente no fue encontrado.</div>
                <a href="clientes.php" class="btn btn-primary">Volver a la lista</a>
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
    const btnCancelarCliente = document.getElementById('btnCancelarCliente');
    const modalCancelarElement = document.getElementById('modalCancelar');
    let modalCancelarInstance = null;
    if (modalCancelarElement) {
        modalCancelarInstance = new bootstrap.Modal(modalCancelarElement);
    }

    if (btnCancelarCliente && modalCancelarInstance) {
        btnCancelarCliente.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar la edición del cliente? Los cambios no guardados se perderán.';

            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionClienteEd" class="btn btn-warning">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionClienteEd');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'clientes.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    const formCliente = document.getElementById('formCliente');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = null;
    if (modalConfirmarGuardadoElement) {
        modalConfirmarGuardadoInstance = new bootstrap.Modal(modalConfirmarGuardadoElement);
    }
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formCliente && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formCliente.addEventListener('submit', function(event) {
            event.preventDefault();
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');

            if(modalTitle) modalTitle.textContent = 'Confirmar Actualización de Cliente';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios de este cliente?';

            modalConfirmarGuardadoInstance.show();
        });

        btnConfirmarGuardarSubmit.addEventListener('click', function() {
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formCliente.submit();
        });
    }
});
</script>
