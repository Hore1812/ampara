<?php
$page_title = "Gestión de Empleados";
require_once 'includes/header.php';
require_once 'funciones.php';

$empleados = obtenerTodosEmpleados();
?>

<div class="container-fluid mt-4"> 
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestión de Empleados</h1>
        <a href="registrar_empleado.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Empleado
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="tablaEmpleados" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nombre Corto</th>
                        <th>DNI</th>
                        <th>Área</th>
                        <th>Cargo</th>
                        <th>Correo Corp.</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($empleados)):
                        foreach ($empleados as $emp):
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emp['idempleado']); ?></td>
                                <td>
                                    <a href="#" class="enlace-foto-empleado" 
                                       data-bs-toggle="modal" data-bs-target="#modalVerEmpleado"
                                       data-id="<?php echo htmlspecialchars($emp['idempleado']); ?>"
                                       title="Ver detalles de <?php echo htmlspecialchars($emp['nombrecorto']); ?>">
                                        <img src="<?php echo htmlspecialchars(!empty($emp['rutafoto']) && file_exists($emp['rutafoto']) ? $emp['rutafoto'] : 'img/fotos/empleados/default_avatar.png'); ?>" 
                                             alt="Foto de <?php echo htmlspecialchars($emp['nombrecorto']); ?>" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;">
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($emp['nombrecorto']); ?></td>
                                <td><?php echo htmlspecialchars($emp['dni']); ?></td>
                                <td><?php echo htmlspecialchars($emp['area']); ?></td>
                                <td><?php echo htmlspecialchars($emp['cargo']); ?></td>
                                <td><?php echo htmlspecialchars($emp['correocorporativo']); ?></td>
                                <td>
                                    <?php if ($emp['activo'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm ver-empleado" 
                                            data-id="<?php echo $emp['idempleado']; ?>" 
                                            data-bs-toggle="modal" data-bs-target="#modalVerEmpleado"
                                            title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="editar_empleado.php?id=<?php echo $emp['idempleado']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($emp['activo'] == 1): ?>
                                        <button type="button" class="btn btn-danger btn-sm toggle-estado-empleado" 
                                                data-id="<?php echo $emp['idempleado']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($emp['nombrecorto']); ?>"
                                                data-accion="desactivar"
                                                title="Desactivar">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-success btn-sm toggle-estado-empleado" 
                                                data-id="<?php echo $emp['idempleado']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($emp['nombrecorto']); ?>"
                                                data-accion="activar"
                                                title="Activar">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay empleados registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Ver Empleado (detalles completos) -->
<div class="modal fade" id="modalVerEmpleado" tabindex="-1" aria-labelledby="modalVerEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"> 
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalVerEmpleadoLabel">Detalles del Empleado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalVerEmpleadoBody">
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
    var tablaEmpleados = $('#tablaEmpleados').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
        responsive: true, order: [[0, 'desc']],
        columnDefs: [
            { responsivePriority: 1, targets: 2 }, { responsivePriority: 2, targets: 8 },
            { responsivePriority: 3, targets: 1 }, { orderable: false, targets: [1, 8] }
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // Listener para el modal de Ver Detalles
    $('#tablaEmpleados tbody').on('click', '.ver-empleado, .enlace-foto-empleado', function (e) {
        e.preventDefault();
        var empleadoId = $(this).data('id');
        var modalBody = $('#modalVerEmpleadoBody');
        var modalLabel = $('#modalVerEmpleadoLabel');
        
        modalBody.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        modalLabel.text('Detalles del Empleado');

        // Simulación de llamada AJAX. En un caso real, esto sería una petición a un script PHP.
        // Usamos los datos ya cargados en la página para el modal.
        var empleadosData = <?php echo json_encode($empleados ?? []); ?>; 
        var empleadoData = empleadosData.find(emp => emp.idempleado == empleadoId);

        if (empleadoData) {
            // Retraso simulado para imitar la carga de red
            setTimeout(() => {
                modalLabel.text('Detalles de: ' + (empleadoData.nombrecorto || 'Empleado'));
                var contentHtml = construirHtmlDetalleEmpleado(empleadoData);
                modalBody.html(contentHtml);
            }, 500);
        } else {
            modalBody.html('<p class="text-danger p-5">Error: No se pudieron cargar los datos para este empleado.</p>');
        }
    });

    function construirHtmlDetalleEmpleado(data) {
        let fotoSrc = (data.rutafoto && data.rutafoto !== '') ? data.rutafoto : 'img/fotos/empleados/default_avatar.png';
        const formatDate = (dateString) => {
            if (!dateString || dateString === '0000-00-00') return 'N/A';
            const date = new Date(dateString.replace(/-/g, '/')); // Reemplazar guiones para compatibilidad
            return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
        };

        return `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-md-4 text-center mb-3 mb-md-0">
                        <img src="${fotoSrc}" alt="Foto de ${data.nombrecorto || ''}" class="img-fluid rounded shadow-sm" style="max-height: 280px; border: 1px solid #dee2e6; padding: 4px; background-color: #fff;">
                        <h5 class="mt-2 mb-0">${data.nombrecorto || 'N/A'}</h5>
                        <p class="text-muted small">${data.cargo || 'N/A'}</p>
                        <span class="badge bg-${data.activo == 1 ? 'success' : 'danger'}">${data.activo == 1 ? 'Activo' : 'Inactivo'}</span>
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <ul class="nav nav-tabs" id="empleadoTab" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal">Personal</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#laboral">Laboral</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#previsional">Previsional</button></li>
                        </ul>
                        <div class="tab-content pt-3" id="empleadoTabContent">
                            <div class="tab-pane fade show active" id="personal">
                                <p><strong>Nombres:</strong> ${data.nombres || ''} ${data.paterno || ''} ${data.materno || ''}</p>
                                <p><strong>DNI:</strong> ${data.dni || 'N/A'}</p>
                                <p><strong>Nacimiento:</strong> ${formatDate(data.nacimiento)} en ${data.lugarnacimiento || 'N/A'}</p>
                                <p><strong>Correo:</strong> ${data.correopersonal || 'N/A'}</p>
                                <p><strong>Celular:</strong> ${data.telcelular || 'N/A'}</p>
                            </div>
                            <div class="tab-pane fade" id="laboral">
                                <p><strong>Área:</strong> ${data.area || 'N/A'}</p>
                                <p><strong>Cargo:</strong> ${data.cargo || 'N/A'}</p>
                                <p><strong>Correo Corp.:</strong> ${data.correocorporativo || 'N/A'}</p>
                                <p><strong>Meta Horas:</strong> ${data.horasmeta || 'N/A'}</p>
                            </div>
                            <div class="tab-pane fade" id="previsional">
                                <p><strong>Régimen:</strong> ${data.regimenpension || 'N/A'}</p>
                                <p><strong>Fondo:</strong> ${data.fondopension || 'N/A'}</p>
                                <p><strong>CUSSP:</strong> ${data.cussp || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    $('#tablaEmpleados tbody').on('click', '.toggle-estado-empleado', function () {
        var empleadoId = $(this).data('id');
        var empleadoNombre = $(this).data('nombre');
        var accion = $(this).data('accion'); // 'activar' o 'desactivar'

        const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        const modalTitle = $('#modalEliminar .modal-title');
        const modalBody = $('#modalEliminar .modal-body');
        const form = $('#formEliminar');
        const submitBtn = form.find('button[type="submit"]');

        if (accion === 'desactivar') {
            modalTitle.text('Confirmar Desactivación');
            modalBody.html(`¿Está seguro de que desea desactivar a <strong>${empleadoNombre}</strong>?`);
            submitBtn.removeClass('btn-success').addClass('btn-danger').text('Sí, Desactivar');
        } else {
            modalTitle.text('Confirmar Activación');
            modalBody.html(`¿Está seguro de que desea activar a <strong>${empleadoNombre}</strong>?`);
            submitBtn.removeClass('btn-danger').addClass('btn-success').text('Sí, Activar');
        }

        form.attr('action', 'procesar_empleado.php');
        form.find('input[name="idempleado"]').remove();
        form.find('input[name="accion"]').remove();
        form.append(`<input type="hidden" name="idempleado" value="${empleadoId}">`);
        form.append(`<input type="hidden" name="accion" value="${accion}">`);
        
        modal.show();
    });
});
</script>

