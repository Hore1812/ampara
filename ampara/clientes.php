<?php
$page_title = "Gestión de Clientes";
require_once 'includes/header.php';
require_once 'funciones.php'; // Para obtenerTodosClientes()

$clientes = obtenerTodosClientes_crud(); // Descomentar cuando la función esté lista

?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestión de Clientes</h1>
        <a href="registrar_cliente.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Cliente
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="tablaClientes" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Razón Social</th>
                        <th>Nombre Comercial</th>
                        <th>RUC</th>
                        <th>Teléfono</th>
                        <th>Representante</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['idcliente']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['razonsocial']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['nombrecomercial']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['ruc']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['representante']); ?></td>
                                <td>
                                    <?php if ($cliente['activo'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm ver-cliente" 
                                            data-id="<?php echo $cliente['idcliente']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalVerCliente"
                                            title="Ver Detalles del Cliente">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="editar_cliente.php?id=<?php echo $cliente['idcliente']; ?>" class="btn btn-secondary btn-sm" title="Editar Cliente">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-<?php echo $cliente['activo'] ? 'orange' : 'success'; ?> btn-sm cambiar-estado-cliente" 
                                            data-id="<?php echo $cliente['idcliente']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($cliente['nombrecomercial']); ?>"
                                            data-estado-actual="<?php echo $cliente['activo']; ?>"
                                            title="<?php echo $cliente['activo'] ? 'Desactivar' : 'Activar'; ?> Cliente">
                                        <i class="fas fa-<?php echo $cliente['activo'] ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay clientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Ver Cliente -->
<div class="modal fade" id="modalVerCliente" tabindex="-1" aria-labelledby="modalVerClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalVerClienteLabel">Detalles del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalVerClienteBody">
                <!-- Contenido se cargará aquí vía AJAX o JS -->
                Cargando detalles...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<?php 
require_once 'includes/modales.php'; 
require_once 'includes/footer.php'; 
?>

<script>
$(document).ready(function() {
    var tablaClientes = $('#tablaClientes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true,
        order: [[1, 'asc']], // Ordenar por Razón Social por defecto
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // Razón Social
            { responsivePriority: 2, targets: 2 }, // Nombre Comercial
            { responsivePriority: 3, targets: 7 }, // Acciones
            { orderable: false, targets: 7 }      
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // Lógica para el modal "Ver Cliente" (se detallará en el siguiente paso del plan)
    $('#tablaClientes tbody').on('click', '.ver-cliente', function () {
        var clienteId = $(this).data('id');
        var modalBody = $('#modalVerClienteBody');
        var modalLabel = $('#modalVerClienteLabel');
        
        modalBody.html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        
        // Simulación de carga (reemplazar con AJAX y obtenerClientePorId más adelante)
        var clientesSimulados = <?php echo json_encode($clientes); ?>; // Usar los datos simulados ya definidos en PHP
        var data = clientesSimulados.find(c => c.idcliente == clienteId);

        if(data){
            modalLabel.text('Detalles de: ' + data.nombrecomercial);
            let estadoTexto = data.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            
            // Aquí se deben listar TODOS los campos de la tabla cliente de forma organizada.
            // Esta es una simulación básica.
            var contentHtml = `
                <p><strong>ID Cliente:</strong> ${data.idcliente}</p>
                <p><strong>Razón Social:</strong> ${data.razonsocial}</p>
                <p><strong>Nombre Comercial:</strong> ${data.nombrecomercial}</p>
                <p><strong>RUC:</strong> ${data.ruc}</p>
                <p><strong>Dirección:</strong> ${data.direccion || 'N/A'}</p>
                <p><strong>Teléfono:</strong> ${data.telefono}</p>
                <p><strong>Sitio Web:</strong> ${data.sitioweb ? '<a href="'+data.sitioweb+'" target="_blank">'+data.sitioweb+'</a>' : 'N/A'}</p>
                <hr>
                <p><strong>Representante:</strong> ${data.representante || 'N/A'}</p>
                <p><strong>Tel. Representante:</strong> ${data.telrepresentante || 'N/A'}</p>
                <p><strong>Correo Representante:</strong> ${data.correorepre || 'N/A'}</p>
                <hr>
                <p><strong>Gerente:</strong> ${data.gerente || 'N/A'}</p>
                <p><strong>Tel. Gerente:</strong> ${data.telgerente || 'N/A'}</p>
                <p><strong>Correo Gerente:</strong> ${data.correogerente || 'N/A'}</p>
                <hr>
                <p><strong>Estado:</strong> ${estadoTexto}</p>
            `;
            modalBody.html(contentHtml);
        } else {
            modalBody.html('<p class="text-danger">No se encontraron datos para este cliente.</p>');
        }
    });

    // Cambiar estado del cliente (Activar/Desactivar)
    $('#tablaClientes tbody').on('click', '.cambiar-estado-cliente', function () {
        var clienteId = $(this).data('id');
        var clienteNombre = $(this).data('nombre');
        var estadoActual = $(this).data('estado-actual');
        var nuevoEstadoTexto = estadoActual == 1 ? "desactivar" : "activar";
        
        const modalConfirmar = document.getElementById('modalConfirmarGuardado'); // Reutilizar modal genérico
        if (modalConfirmar) {
            const modalTitle = modalConfirmar.querySelector('.modal-title');
            const modalBody = modalConfirmar.querySelector('.modal-body');
            const btnConfirmarSubmit = modalConfirmar.querySelector('#btnConfirmarGuardarSubmit');
            
            if(modalTitle) modalTitle.textContent = `Confirmar ${nuevoEstadoTexto.charAt(0).toUpperCase() + nuevoEstadoTexto.slice(1)} Cliente`;
            if(modalBody) modalBody.innerHTML = `¿Está seguro que desea ${nuevoEstadoTexto} al cliente <strong>${clienteNombre}</strong>?`;
            if(btnConfirmarSubmit) btnConfirmarSubmit.textContent = `Sí, ${nuevoEstadoTexto.charAt(0).toUpperCase() + nuevoEstadoTexto.slice(1)}`;
            
            $(btnConfirmarSubmit).off('click').on('click', function() { 
                var form = $('<form action="procesar_cliente.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${nuevoEstadoTexto === 'desactivar' ? 'desactivar' : 'activar'}">`);
                form.append(`<input type="hidden" name="idcliente" value="${clienteId}">`);
                $('body').append(form);
                form.submit();
            });

            var modalInstance = bootstrap.Modal.getInstance(modalConfirmar) || new bootstrap.Modal(modalConfirmar);
            modalInstance.show();
        } else { 
            if (confirm(`¿Está seguro que desea ${nuevoEstadoTexto} al cliente ${clienteNombre}?`)) {
                var form = $('<form action="procesar_cliente.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${nuevoEstadoTexto === 'desactivar' ? 'desactivar' : 'activar'}">`);
                form.append(`<input type="hidden" name="idcliente" value="${clienteId}">`);
                $('body').append(form);
                form.submit();
            }
        }
    });
});
</script>
