<?php
// views/usuarios/index.php - The "View" for the Usuarios module

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-users-cog me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal" data-id="0">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Usuario
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtros</h5>
            <form method="GET" action="usuarios.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="activo_filtro" class="form-label">Estado</label>
                    <select name="activo" id="activo_filtro" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?php echo ($filtros['activo'] === '1') ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo ($filtros['activo'] === '0') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-2"></i>Filtrar</button>
                    <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-sync-alt me-2"></i>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <table id="tablaUsuarios" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Empleado Asignado</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['idusuario']; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($usuario['rutafoto_empleado'] ?? 'img/fotos/empleados/usuario01.png'); ?>" alt="Foto" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($usuario['nombre_empleado'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                switch ($usuario['tipo']) {
                                    case 1: echo '<span class="badge bg-danger">Admin</span>'; break;
                                    case 2: echo '<span class="badge bg-info">Editor</span>'; break;
                                    case 3: echo '<span class="badge bg-secondary">Lector</span>'; break;
                                    default: echo '<span class="badge bg-light text-dark">Desconocido</span>'; break;
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($usuario['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Edit User Button -->
                                <button class="btn btn-secondary btn-sm" title="Editar Usuario"
                                        data-bs-toggle="modal" data-bs-target="#usuarioModal"
                                        data-id="<?php echo $usuario['idusuario']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                        data-idemp="<?php echo $usuario['idemp']; ?>"
                                        data-tipo="<?php echo $usuario['tipo']; ?>"
                                        data-activo="<?php echo $usuario['activo']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Change Password Button -->
                                <button class="btn btn-info btn-sm" title="Cambiar Contraseña"
                                        data-bs-toggle="modal" data-bs-target="#passwordModal"
                                        data-id="<?php echo $usuario['idusuario']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                                    <i class="fas fa-key"></i>
                                </button>
                                <!-- Activate/Deactivate Form -->
                                <form action="usuarios.php" method="POST" class="d-inline">
                                    <input type="hidden" name="idusuario" value="<?php echo $usuario['idusuario']; ?>">
                                    <?php if ($usuario['activo']): ?>
                                        <input type="hidden" name="accion" value="desactivar">
                                        <button type="submit" class="btn btn-warning btn-sm" title="Desactivar Usuario" onclick="return confirm('¿Está seguro?');">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="accion" value="activar">
                                        <button type="submit" class="btn btn-success btn-sm" title="Activar Usuario" onclick="return confirm('¿Está seguro?');">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="usuarios.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="usuarioModalLabel">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idusuario" id="idusuario">
                    <input type="hidden" name="accion" id="accion" value="registrar">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="idemp" class="form-label">Empleado Asignado <span class="text-danger">*</span></label>
                        <select class="form-select" id="idemp" name="idemp" required>
                            <option value="">-- Seleccione un Empleado --</option>
                            <?php foreach ($empleados as $empleado): ?>
                                <option value="<?php echo $empleado['idempleado']; ?>"><?php echo htmlspecialchars($empleado['nombrecorto']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="1">Admin</option>
                            <option value="2">Editor</option>
                            <option value="3" selected>Lector</option>
                        </select>
                    </div>
                    <div class="mb-3" id="password-group">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8">
                        <small class="form-text text-muted">Mínimo 8 caracteres. Dejar en blanco si no se desea cambiar al editar.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Change Password -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="usuarios.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idusuario" id="password_idusuario">
                    <input type="hidden" name="accion" value="cambiar_password">
                    <p>Cambiando contraseña para el usuario: <strong id="password_nombre_usuario"></strong></p>
                    <div class="mb-3">
                        <label for="password_new" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_new" name="password" required minlength="8">
                        <small class="form-text text-muted">Mínimo 8 caracteres.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaUsuarios').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
        responsive: true,
        order: [[0, 'desc']]
    });

    const usuarioModal = document.getElementById('usuarioModal');
    usuarioModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-id');
        const form = usuarioModal.querySelector('form');

        // Reset form to default state
        form.reset();
        form.querySelector('#accion').value = 'registrar';
        usuarioModal.querySelector('.modal-title').textContent = 'Agregar Usuario';
        form.querySelector('#password').required = true;
        form.querySelector('#password-group').style.display = 'block';

        if (userId && userId !== '0') {
            // Edit mode
            usuarioModal.querySelector('.modal-title').textContent = 'Editar Usuario';
            form.querySelector('#accion').value = 'editar';
            form.querySelector('#idusuario').value = userId;
            form.querySelector('#nombre').value = button.getAttribute('data-nombre');
            form.querySelector('#idemp').value = button.getAttribute('data-idemp');
            form.querySelector('#tipo').value = button.getAttribute('data-tipo');
            form.querySelector('#activo').checked = button.getAttribute('data-activo') === '1';

            // Password is not required when editing
            form.querySelector('#password').required = false;
            form.querySelector('#password-group').style.display = 'none'; // Hide password field on edit
        }
    });

    const passwordModal = document.getElementById('passwordModal');
    passwordModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-id');
        const userName = button.getAttribute('data-nombre');

        passwordModal.querySelector('#password_idusuario').value = userId;
        passwordModal.querySelector('#password_nombre_usuario').textContent = userName;
        passwordModal.querySelector('form').reset();
    });
});
</script>
