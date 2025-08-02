<?php
$page_title = "Registrar Nuevo Usuario";
require_once 'includes/header.php';
require_once 'funciones.php'; // Para obtenerEmpleadosActivosParaSelect()

$empleados = obtenerEmpleadosActivosParaSelect(); // Descomentar cuando la función esté lista
$empleados_simulados = [ // Simulación mientras no está la función
    ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez', 'rutafoto' => 'img/fotos/empleados/usuario01.png'],
    ['idempleado' => 2, 'nombrecorto' => 'Ana López', 'rutafoto' => 'img/fotos/empleados/default_avatar.png'],
    ['idempleado' => 3, 'nombrecorto' => 'Carlos Ruiz', 'rutafoto' => '']
];
$empleados = $empleados;

?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Registrar Nuevo Usuario</h3>
        </div>
        <div class="card-body">
            <form id="formUsuario" action="procesar_usuario.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idemp" class="form-label">Empleado Asociado <span class="text-danger">*</span></label>
                        <select class="form-select" id="idemp" name="idemp" required>
                            <option value="">Seleccionar Empleado...</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['idempleado']); ?>" data-rutafoto="<?php echo htmlspecialchars(!empty($emp['rutafoto']) && file_exists($emp['rutafoto']) ? $emp['rutafoto'] : 'img/fotos/empleados/default_avatar.png'); ?>">
                                    <?php echo htmlspecialchars($emp['nombrecorto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirmar_password" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar Tipo...</option>
                            <option value="1">Administrador</option>
                            <option value="2">Editor</option>
                            <option value="3">Consultor</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>
                    </div>
                     <div class="col-md-3 mb-3 text-center">
                        <img id="empleadoFotoPreview" src="img/fotos/empleados/default_avatar.png" alt="Foto Empleado" class="img-thumbnail mt-2" style="max-height: 100px; max-width: 100px; display: none;">
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" id="btnCancelarUsuario" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
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
    const idempSelect = document.getElementById('idemp');
    const empleadoFotoPreview = document.getElementById('empleadoFotoPreview');
    const defaultAvatar = 'img/fotos/empleados/default_avatar.png';

    if (idempSelect && empleadoFotoPreview) {
        idempSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const fotoSrc = selectedOption.dataset.rutafoto;
            if (fotoSrc) {
                empleadoFotoPreview.src = fotoSrc;
                empleadoFotoPreview.style.display = 'block';
            } else {
                empleadoFotoPreview.src = defaultAvatar; // O ocultar si no hay foto
                empleadoFotoPreview.style.display = 'none';
            }
        });
    }

    // Lógica para el botón Cancelar (similar a empleados)
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
            if (modalBody) modalBody.innerHTML = '¿Está seguro que desea cancelar el registro del usuario? Los datos no guardados se perderán.';
            
            if(modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btnModalConfirmarCancelacionUsuario" class="btn btn-danger">Sí, cancelar</button>
                `;
                const btnConfirmarSi = modalFooter.querySelector('#btnModalConfirmarCancelacionUsuario');
                if(btnConfirmarSi){
                     btnConfirmarSi.addEventListener('click', function() {
                        window.location.href = 'usuarios.php';
                    }, { once: true });
                }
            }
            modalCancelarInstance.show();
        });
    }

    // Lógica para el modal de Confirmar Guardado (adaptada para formUsuario)
    const formUsuario = document.getElementById('formUsuario');
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado');
    let modalConfirmarGuardadoInstance = null;
    if (modalConfirmarGuardadoElement) {
        modalConfirmarGuardadoInstance = new bootstrap.Modal(modalConfirmarGuardadoElement);
    }
    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit');

    if (formUsuario && modalConfirmarGuardadoInstance && btnConfirmarGuardarSubmit) {
        formUsuario.addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmarPassword = document.getElementById('confirmar_password').value;

            if (password !== confirmarPassword) {
                event.preventDefault();
                // Podríamos usar el modal de error genérico aquí
                const modalErrorElement = document.getElementById('modalError');
                if (modalErrorElement) {
                    const modalErrorBody = modalErrorElement.querySelector('#mensajeError');
                    if (modalErrorBody) modalErrorBody.textContent = 'Las contraseñas no coinciden.';
                    let errInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                    errInstance.show();
                } else {
                    alert('Las contraseñas no coinciden.');
                }
                return;
            }
            
            // Si las contraseñas coinciden, mostrar modal de confirmación de guardado
            event.preventDefault();
            const modalTitle = modalConfirmarGuardadoElement.querySelector('.modal-title');
            const modalBody = modalConfirmarGuardadoElement.querySelector('.modal-body');
            
            // Asumimos que es registro ya que no hay idusuario en este form
            if(modalTitle) modalTitle.textContent = 'Confirmar Registro de Usuario';
            if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar este nuevo usuario?';
            
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
