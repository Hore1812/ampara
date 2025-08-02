<?php
$page_title = "Registrar Nuevo Cliente";
require_once 'includes/header.php';
// require_once 'funciones.php'; // No se necesitan funciones especiales para este formulario por ahora
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3>Registrar Nuevo Cliente</h3>
        </div>
        <div class="card-body">
            <form id="formCliente" action="procesar_cliente.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <fieldset class="mb-3">
                    <legend>Información General</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="razonsocial" class="form-label">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="razonsocial" name="razonsocial" required maxlength="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombrecomercial" class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombrecomercial" name="nombrecomercial" required maxlength="50">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ruc" class="form-label">RUC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ruc" name="ruc" required maxlength="15">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="15">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label for="sitioweb" class="form-label">Sitio Web</label>
                        <input type="url" class="form-control" id="sitioweb" name="sitioweb" maxlength="150" placeholder="https://www.ejemplo.com">
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Contacto Principal (Representante)</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="representante" class="form-label">Nombre del Representante</label>
                            <input type="text" class="form-control" id="representante" name="representante" maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telrepresentante" class="form-label">Teléfono del Representante</label>
                            <input type="tel" class="form-control" id="telrepresentante" name="telrepresentante" maxlength="15">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correorepre" class="form-label">Correo del Representante</label>
                        <input type="email" class="form-control" id="correorepre" name="correorepre" maxlength="150">
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend>Contacto Gerente</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gerente" class="form-label">Nombre del Gerente</label>
                            <input type="text" class="form-control" id="gerente" name="gerente" maxlength="150">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telgerente" class="form-label">Teléfono del Gerente</label>
                            <input type="tel" class="form-control" id="telgerente" name="telgerente" maxlength="15">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correogerente" class="form-label">Correo del Gerente</label>
                        <input type="email" class="form-control" id="correogerente" name="correogerente" maxlength="150">
                    </div>
                </fieldset>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">Cliente Activo</label>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarCliente" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
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
    // Lógica para el botón Cancelar
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
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar el registro del cliente? Los datos no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionCliente" class="btn btn-danger">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionCliente');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'clientes.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    // Lógica para el modal de Confirmar Guardado
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
            
            if(modalTitle) modalTitle.textContent = 'Confirmar Registro de Cliente';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar este nuevo cliente?';
            
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
