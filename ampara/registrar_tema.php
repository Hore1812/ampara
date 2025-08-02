<?php
$page_title = "Registrar Nuevo Tema";
require_once 'includes/header.php';
require_once 'funciones.php'; // Para obtenerEmpleadosActivosParaSelect()

 $empleados = obtenerEmpleadosActivosParaSelect(); // Descomentar cuando la función esté lista
$empleados_simulados = [ // Simulación mientras no está la función
    ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez'],
    ['idempleado' => 2, 'nombrecorto' => 'Ana López'],
    ['idempleado' => 3, 'nombrecorto' => 'Carlos Ruiz']
];
$empleados = $empleados;
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Registrar Nuevo Tema</h3>
        </div>
        <div class="card-body">
            <form id="formTema" action="procesar_tema.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción del Tema <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="idencargado" class="form-label">Encargado del Tema</label>
                        <select class="form-select" id="idencargado" name="idencargado">
                            <option value="">Seleccionar Encargado</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['idempleado']); ?>">
                                    <?php echo htmlspecialchars($emp['nombrecorto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarTema" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Tema</button>
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
    const btnCancelarTema = document.getElementById('btnCancelarTema');
    const modalCancelarElement = document.getElementById('modalCancelar');
    let modalCancelarInstance = null;
    if (modalCancelarElement) {
        modalCancelarInstance = new bootstrap.Modal(modalCancelarElement);
    }

    if (btnCancelarTema && modalCancelarInstance) {
        btnCancelarTema.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar el registro del tema? Los datos no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionTema" class="btn btn-danger">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionTema');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'temas.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    // Lógica para el modal de Confirmar Guardado
    const formTema = document.getElementById('formTema');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = null;
    if (modalConfirmarGuardadoElement) {
        modalConfirmarGuardadoInstance = new bootstrap.Modal(modalConfirmarGuardadoElement);
    }
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formTema && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formTema.addEventListener('submit', function(event) {
            event.preventDefault();
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            
            if(modalTitle) modalTitle.textContent = 'Confirmar Registro de Tema';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar este nuevo tema?';
            
            modalConfirmarGuardadoInstance.show();
        });

        btnConfirmarGuardarSubmit.addEventListener('click', function() {
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formTema.submit(); 
        });
    }
});
</script>
