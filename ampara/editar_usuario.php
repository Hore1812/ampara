<?php
$page_title = "Editar Usuario";
require_once 'includes/header.php';
require_once 'funciones.php';

$idusuario = null;
$usuario_actual = null;
$error_carga = '';

if (isset($_GET['id'])) {
    $idusuario = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idusuario) {
        $usuario_actual = obtenerUsuarioPorId($idusuario); // Descomentar cuando la función esté lista
        if (!$usuario_actual) {
            $error_carga = "No se encontró el usuario con el ID proporcionado.";
            $_SESSION['mensaje_error'] = $error_carga;
        }
    } else {
        $error_carga = "ID de usuario no válido.";
        $_SESSION['mensaje_error'] = $error_carga;
    }
} else {
    $error_carga = "No se proporcionó ID de usuario.";
    $_SESSION['mensaje_error'] = $error_carga;
}

// Simulación de datos para diseño
if (!$usuario_actual && $idusuario) {
    $usuario_actual = [
        'idusuario' => $idusuario,
        'nombre' => 'usuario_a_editar',
        'tipo' => 2,
        'activo' => 1,
        'idemp' => 1, // ID del empleado asociado
        'nombre_empleado' => 'Juan Pérez', // Para mostrar
        'rutafoto_empleado' => 'img/fotos/empleados/usuario01.png' // Para mostrar
    ];
}

 $empleados = obtenerEmpleadosActivosParaSelect(); // Descomentar
$empleados_simulados = [
    ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez', 'rutafoto' => 'img/fotos/empleados/usuario01.png'],
    ['idempleado' => 2, 'nombrecorto' => 'Ana López', 'rutafoto' => 'img/fotos/empleados/default_avatar.png'],
    ['idempleado' => 3, 'nombrecorto' => 'Carlos Ruiz', 'rutafoto' => '']
];
//$empleados = $empleados_simulados;

?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Editar Usuario: <?php echo htmlspecialchars($usuario_actual['nombre'] ?? 'N/A'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($error_carga) && !$usuario_actual): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_carga); ?></div>
                <a href="usuarios.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif (!$usuario_actual && isset($_GET['id'])): ?>
                 <div class="alert alert-danger">No se pudieron cargar los datos del usuario. Verifique el ID.</div>
                <a href="usuarios.php" class="btn btn-primary">Volver a la lista</a>
            <?php elseif ($usuario_actual): ?>
            <form id="formUsuario" action="procesar_usuario.php" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="idusuario" value="<?php echo htmlspecialchars($usuario_actual['idusuario']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($usuario_actual['nombre'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idemp" class="form-label">Empleado Asociado <span class="text-danger">*</span></label>
                        <select class="form-select" id="idemp" name="idemp" required>
                            <option value="">Seleccionar Empleado...</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['idempleado']); ?>" 
                                        data-rutafoto="<?php echo htmlspecialchars(!empty($emp['rutafoto']) && file_exists($emp['rutafoto']) ? $emp['rutafoto'] : 'img/fotos/empleados/default_avatar.png'); ?>"
                                        <?php echo (isset($usuario_actual['idemp']) && $usuario_actual['idemp'] == $emp['idempleado']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['nombrecorto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar Tipo...</option>
                            <option value="1" <?php echo (isset($usuario_actual['tipo']) && $usuario_actual['tipo'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                            <option value="2" <?php echo (isset($usuario_actual['tipo']) && $usuario_actual['tipo'] == 2) ? 'selected' : ''; ?>>Editor</option>
                            <option value="3" <?php echo (isset($usuario_actual['tipo']) && $usuario_actual['tipo'] == 3) ? 'selected' : ''; ?>>Consultor</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?php echo (isset($usuario_actual['activo']) && $usuario_actual['activo'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 text-center">
                        <img id="empleadoFotoPreview" 
                             src="<?php echo htmlspecialchars(!empty($usuario_actual['rutafoto_empleado']) && file_exists($usuario_actual['rutafoto_empleado']) ? $usuario_actual['rutafoto_empleado'] : 'img/fotos/empleados/default_avatar.png'); ?>" 
                             alt="Foto Empleado" 
                             class="img-thumbnail mt-2" 
                             style="max-height: 100px; max-width: 100px; <?php echo empty($usuario_actual['idemp']) ? 'display: none;' : ''; ?>">
                    </div>
                </div>
                <p class="form-text" style="color:green">La contraseña solo se puede cambiar desde la opción <b>"Ver Detalles"</b> del listado de usuarios.</p>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarUsuario" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">No se proporcionó un ID de usuario válido o el usuario no fue encontrado.</div>
                <a href="usuarios.php" class="btn btn-primary">Volver a la lista</a>
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
    const idempSelect = document.getElementById('idemp');
    const empleadoFotoPreview = document.getElementById('empleadoFotoPreview');
    const defaultAvatar = 'img/fotos/empleados/usuario01.png';

    if (idempSelect && empleadoFotoPreview) {
        idempSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const fotoSrc = selectedOption.dataset.rutafoto;
            if (fotoSrc && fotoSrc !== defaultAvatar) {
                empleadoFotoPreview.src = fotoSrc;
                empleadoFotoPreview.style.display = 'block';
            } else {
                empleadoFotoPreview.src = defaultAvatar;
                empleadoFotoPreview.style.display = selectedOption.value ? 'block' : 'none'; // Mostrar default si hay empleado, ocultar si no
            }
        });
        // Disparar el evento change al cargar para mostrar la foto si ya hay un empleado seleccionado
        if(idempSelect.value){
            idempSelect.dispatchEvent(new Event('change'));
        } else {
            empleadoFotoPreview.style.display = 'none';
        }
    }

    const btnCancelarUsuario = document.getElementById('btnCancelarUsuario');
    const modalCancelarElement = document.getElementById('modalCancelar');
    let modalCancelarInstance = null;
    if (modalCancelarElement) {
        modalCancelarInstance = new bootstrap.Modal(modalCancelarElement);
    }

    if (btnCancelarUsuario && modalCancelarInstance) {
        btnCancelarUsuario.addEventListener('click', function() {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            if (modalTitle) modalTitle.textContent = 'Confirmar Cancelación';
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar la edición? Los cambios no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionUsuarioEd" class="btn btn-warning">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionUsuarioEd');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'usuarios.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    const formUsuario = document.getElementById('formUsuario');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = null;
    if (modalConfirmarGuardadoElement) {
        modalConfirmarGuardadoInstance = new bootstrap.Modal(modalConfirmarGuardadoElement);
    }
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formUsuario && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formUsuario.addEventListener('submit', function(event) {
            event.preventDefault();
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            
            if(modalTitle) modalTitle.textContent = 'Confirmar Actualización';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios en este usuario?';
            
            modalConfirmarGuardadoInstance.show();
        });

        btnConfirmarGuardarSubmit.addEventListener('click', function() {
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formUsuario.submit(); 
        });
    }
});
</script>
