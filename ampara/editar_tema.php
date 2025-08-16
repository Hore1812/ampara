<?php
$page_title = "Editar Tema";
require_once 'includes/header.php';
require_once 'funciones.php';

$idtema = null;
$tema_actual = null;
$error_carga = '';

if (isset($_GET['id'])) {
    $idtema = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idtema) {
        $tema_actual = obtenerTemaPorId($idtema); // Descomentar cuando la función esté lista
        if (!$tema_actual) {
            $error_carga = "No se encontró el tema con el ID proporcionado.";
            $_SESSION['mensaje_error'] = $error_carga;
        }
    } else {
        $error_carga = "ID de tema no válido.";
        $_SESSION['mensaje_error'] = $error_carga;
    }
} else {
    $error_carga = "No se proporcionó ID de tema.";
    $_SESSION['mensaje_error'] = $error_carga;
}

// Simulación de datos para diseño
if (!$tema_actual && $idtema) {
    $tema_actual = [
        'idtema' => $idtema,
        'descripcion' => 'Descripción del tema a editar. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'idencargado' => 1, // ID del empleado encargado
        'comentario' => 'Comentario existente sobre el tema.'
    ];
}

$empleados = obtenerEmpleadosActivosParaSelect(); // Descomentar
$empleados_simulados = [
    ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez'],
    ['idempleado' => 2, 'nombrecorto' => 'Ana López'],
    ['idempleado' => 3, 'nombrecorto' => 'Carlos Ruiz']
];
$empleados = $empleados;

?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Editar Tema: #<?php echo htmlspecialchars($tema_actual['idtema'] ?? 'N/A'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_carga) && !$tema_actual): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_carga); ?></div>
                <a href="temas.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif (!$tema_actual && isset($_GET['id'])): ?>
                 <div class="alert alert-danger">No se pudieron cargar los datos del tema. Verifique el ID.</div>
                <a href="temas.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif ($tema_actual): ?>
            <form id="formTema" action="procesar_tema.php" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="idtema" value="<?php echo htmlspecialchars($tema_actual['idtema']); ?>">

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción del Tema <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?php echo htmlspecialchars($tema_actual['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="row">
                     <div class="col-md-12 mb-3">
                        <label for="idencargado" class="form-label">Encargado del Tema</label>
                        <select class="form-select" id="idencargado" name="idencargado">
                            <option value="">Seleccionar Encargado (Opcional)...</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['idempleado']); ?>"
                                    <?php echo (isset($tema_actual['idencargado']) && $tema_actual['idencargado'] == $emp['idempleado']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['nombrecorto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3"><?php echo htmlspecialchars($tema_actual['comentario'] ?? ''); ?></textarea>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarTema" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Tema</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">No se proporcionó un ID de tema válido o el tema no fue encontrado.</div>
                <a href="temas.php" class="btn btn-primary">Volver a la lista</a>
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
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar la edición del tema? Los cambios no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionTemaEd" class="btn btn-warning">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionTemaEd');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'temas.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

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
            
            if(modalTitle) modalTitle.textContent = 'Confirmar Actualización de Tema';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios en este tema?';
            
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
