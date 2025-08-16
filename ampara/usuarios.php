<?php
$page_title = "Gestión de Usuarios";
require_once 'includes/header.php';
require_once 'funciones.php'; // Para obtenerTodosUsuarios()

// Simulación de datos de usuarios para diseño inicial
$usuarios_simulados = [
    [
        'idusuario' => 1,
        'nombre' => 'admin_user',
        'nombre_empleado' => 'Juan Pérez', // Nombre corto del empleado asociado
        'tipo' => 1, // 1: Admin, 2: Editor, 3: Consultor
        'activo' => 1,
        'rutafoto_empleado' => 'img/fotos/empleados/usuario01.png' // Foto del empleado asociado
    ],
    [
        'idusuario' => 2,
        'nombre' => 'editor_user',
        'nombre_empleado' => 'Ana López',
        'tipo' => 2,
        'activo' => 1,
        'rutafoto_empleado' => 'img/fotos/empleados/default_avatar.png'
    ],
    [
        'idusuario' => 3,
        'nombre' => 'consultor_user',
        'nombre_empleado' => 'Carlos Ruiz',
        'tipo' => 3,
        'activo' => 0, // Inactivo
        'rutafoto_empleado' => '' // Sin foto o foto no encontrada
    ]
];
$usuarios = obtenerTodosUsuarios(); // Descomentar cuando la función esté lista
//$usuarios = $usuarios_simulados; // Usar datos simulados por ahora

function getTipoUsuarioTexto($tipoId) {
    switch ($tipoId) {
        case 1: return 'Administrador';
        case 2: return 'Editor';
        case 3: return 'Consultor';
        default: return 'Desconocido';
    }
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestión de Usuarios</h1>
        <a href="registrar_usuario.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Usuario
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="tablaUsuarios" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Empleado Asociado</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['idusuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre_empleado']); ?></td>
                                <td><?php echo htmlspecialchars(getTipoUsuarioTexto($usuario['tipo'])); ?></td>
                                <td>
                                    <?php if ($usuario['activo'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm ver-usuario" 
                                            data-id="<?php echo $usuario['idusuario']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalVerUsuario"
                                            title="Ver Detalles y Cambiar Contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="editar_usuario.php?id=<?php echo $usuario['idusuario']; ?>" class="btn btn-warning btn-sm" title="Editar Usuario">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-<?php echo $usuario['activo'] ? 'danger' : 'success'; ?> btn-sm cambiar-estado-usuario" 
                                            data-id="<?php echo $usuario['idusuario']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                            data-estado-actual="<?php echo $usuario['activo']; ?>"
                                            title="<?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?> Usuario">
                                        <i class="fas fa-<?php echo $usuario['activo'] ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Ver Usuario y Cambiar Contraseña -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-labelledby="modalVerUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerUsuarioLabel">Detalles del Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalVerUsuarioBody">
                <!-- Contenido se cargará aquí vía AJAX o JS -->
                Cargando detalles...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<?php 
require_once 'includes/modales.php'; // Para modales genéricos de confirmación, error, éxito
require_once 'includes/footer.php'; 
?>

<script>
$(document).ready(function() {
    var tablaUsuarios = $('#tablaUsuarios').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']], // Ordenar por ID descendente por defecto
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // Nombre de Usuario
            { responsivePriority: 2, targets: 5 }, // Acciones
            { orderable: false, targets: 5 }      // No ordenar por Acciones
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // Lógica para el modal "Ver Usuario" (se detallará en el siguiente paso del plan)
    $('#tablaUsuarios tbody').on('click', '.ver-usuario', function () {
        var usuarioId = $(this).data('id');
        var modalBody = $('#modalVerUsuarioBody');
        var modalLabel = $('#modalVerUsuarioLabel');
        
        // Simulación de carga (reemplazar con AJAX más adelante)
        modalBody.html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        
        // Datos simulados para el modal (esto vendría de AJAX y obtenerUsuarioPorId)
        var usuariosSimulados = <?php echo json_encode($usuarios); ?>;
        var data = usuariosSimulados.find(u => u.idusuario == usuarioId);

        if(data){
            modalLabel.text('Detalles de: ' + data.nombre);
            let tipoTexto = '';
            switch(data.tipo) {
                case 1: tipoTexto = 'Administrador'; break;
                case 2: tipoTexto = 'Editor'; break;
                case 3: tipoTexto = 'Consultor'; break;
                default: tipoTexto = 'Desconocido';
            }
            let estadoTexto = data.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            let fotoEmpleado = data.rutafoto_empleado && data.rutafoto_empleado !== '' ? data.rutafoto_empleado : 'img/fotos/empleados/default_avatar.png';

            var contentHtml = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="${fotoEmpleado}" alt="Foto Empleado" class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <h5>${data.nombre_empleado || 'N/A'}</h5>
                    </div>
                    <div class="col-md-8">
                        <p><strong>ID Usuario:</strong> ${data.idusuario}</p>
                        <p><strong>Nombre de Usuario:</strong> ${data.nombre}</p>
                        <p><strong>Tipo:</strong> ${tipoTexto}</p>
                        <p><strong>Estado:</strong> ${estadoTexto}</p>
                    </div>
                </div>
                <hr>
                <h5>Cambiar Contraseña</h5>
                <form id="formCambiarPassword" class="mt-2">
                    <input type="hidden" name="idusuario_cp" value="${data.idusuario}">
                    <input type="hidden" name="accion" value="cambiar_password">
                    <div class="mb-3">
                        <label for="nueva_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="nueva_password_modal" name="nueva_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_nueva_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirmar_nueva_password_modal" name="confirmar_nueva_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Cambiar Contraseña</button>
                    <div id="mensajeCambioPass" class="mt-2"></div>
                </form>
            `;
            modalBody.html(contentHtml);
// Manejar submit del form de cambiar contraseña DENTRO del modal
            // Usar delegación de eventos en modalBody o .off().on() para evitar listeners duplicados
            modalBody.off('submit', '#formCambiarPassword').on('submit', '#formCambiarPassword', function(e){
                e.preventDefault();
                var form = $(this);
                var mensajeDiv = $('#mensajeCambioPass'); // Asegúrate que este ID exista dentro de tu contentHtml para el modal
                mensajeDiv.html(''); // Limpiar mensajes previos

                var nuevaPassword = form.find('#nueva_password_modal').val();
                var confirmarPassword = form.find('#confirmar_nueva_password_modal').val();

                if (nuevaPassword !== confirmarPassword) {
                    mensajeDiv.html('<div class="alert alert-danger">Las contraseñas no coinciden.</div>');
                    return;
                }
                if (nuevaPassword.length < 6) {
                     mensajeDiv.html('<div class="alert alert-danger">La contraseña debe tener al menos 6 caracteres.</div>');
                    return;
                }

                // Deshabilitar botón para prevenir múltiples submits
                form.find('button[type="submit"]').prop('disabled', true).text('Procesando...');

                $.ajax({
                    url: 'procesar_usuario.php',
                    method: 'POST',
                    data: form.serialize(), 
                    dataType: 'json', 
                    success: function(response) {
                        if (response.success) {
                            mensajeDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                            form[0].reset();
                            setTimeout(function() {
                                var modalToHide = bootstrap.Modal.getInstance(document.getElementById('modalVerUsuario'));
                                if (modalToHide) modalToHide.hide();
                            }, 2000); 
                        } else {
                            mensajeDiv.html('<div class="alert alert-danger">' + (response.message || 'Error desconocido al cambiar la contraseña.') + '</div>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        mensajeDiv.html('<div class="alert alert-danger">Error de conexión o del servidor: ' + textStatus + ', ' + errorThrown + '</div>');
                    },
                    complete: function() {
                        // Volver a habilitar el botón
                        form.find('button[type="submit"]').prop('disabled', false).text('Cambiar Contraseña');
                    }
                });
            });

        } else {
            modalBody.html('<p class="text-danger">No se encontraron datos para este usuario.</p>');
        }
    });

    // Cambiar estado del usuario (Activar/Desactivar)
    $('#tablaUsuarios tbody').on('click', '.cambiar-estado-usuario', function () {
        var usuarioId = $(this).data('id');
        var usuarioNombre = $(this).data('nombre');
        var estadoActual = $(this).data('estado-actual');
        var nuevoEstadoTexto = estadoActual == 1 ? "desactivar" : "activar";
        var nuevoEstadoValor = estadoActual == 1 ? 0 : 1;

        const modalConfirmar = document.getElementById('modalConfirmarGuardado'); // Reutilizar modal genérico
        if (modalConfirmar) {
            const modalTitle = modalConfirmar.querySelector('.modal-title');
            const modalBody = modalConfirmar.querySelector('.modal-body');
            const btnConfirmarSubmit = modalConfirmar.querySelector('#btnConfirmarGuardarSubmit');
            
            if(modalTitle) modalTitle.textContent = `Confirmar ${nuevoEstadoTexto.charAt(0).toUpperCase() + nuevoEstadoTexto.slice(1)} Usuario`;
            if(modalBody) modalBody.innerHTML = `¿Está seguro que desea ${nuevoEstadoTexto} al usuario <strong>${usuarioNombre}</strong>?`;
            if(btnConfirmarSubmit) btnConfirmarSubmit.textContent = `Sí, ${nuevoEstadoTexto.charAt(0).toUpperCase() + nuevoEstadoTexto.slice(1)}`;
            
            // Para enviar la acción correcta a procesar_usuario.php
            $(btnConfirmarSubmit).off('click').on('click', function() { 
                var form = $('<form action="procesar_usuario.php" method="POST" style="display:none;"></form>'); // Ocultar el formulario
                form.append(`<input type="hidden" name="accion" value="${nuevoEstadoTexto === 'desactivar' ? 'desactivar' : 'activar'}">`); // Asegurar la acción correcta
                form.append(`<input type="hidden" name="idusuario" value="${usuarioId}">`);
                $('body').append(form);
                form.submit();
            });

            var modalInstance = bootstrap.Modal.getInstance(modalConfirmar) || new bootstrap.Modal(modalConfirmar);
            modalInstance.show();
        } else { 
            if (confirm(`¿Está seguro que desea ${nuevoEstadoTexto} al usuario ${usuarioNombre}?`)) {
                var form = $('<form action="procesar_usuario.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${nuevoEstadoTexto === 'desactivar' ? 'desactivar' : 'activar'}">`);
                form.append(`<input type="hidden" name="idusuario" value="${usuarioId}">`);
                $('body').append(form);
                form.submit();
            }
        }
    });
});
</script>
