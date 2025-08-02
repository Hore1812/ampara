<?php
$page_title = "Gestión de Temas";
require_once 'includes/header.php';
require_once 'funciones.php'; 

// Obtener empleados para el filtro desplegable
$empleados_para_filtro = obtenerEmpleadosActivosParaSelect(); 

// Obtener resumen de temas por encargado para las tarjetas
$resumen_encargados = obtenerResumenTemasPorEncargado();

// Manejo del filtro por encargado
$idencargado_filtro = null; 
if (isset($_GET['idencargado_filtro']) && $_GET['idencargado_filtro'] !== '') {
    $idencargado_filtro_val = filter_var($_GET['idencargado_filtro'], FILTER_VALIDATE_INT);
    if ($idencargado_filtro_val !== false) { 
        $idencargado_filtro = $idencargado_filtro_val;
    }
}

$filtros_aplicados = [];
if ($idencargado_filtro !== null) { 
    $filtros_aplicados['idencargado'] = $idencargado_filtro;
}

$temas = obtenerTodosTemas_crud($filtros_aplicados); 

if (empty($temas) && empty($filtros_aplicados) && empty($resumen_encargados)) {
    if(empty($empleados_para_filtro) && function_exists('obtenerEmpleadosActivosParaSelect')) {
         $empleados_para_filtro = [
            ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez (Sim)'],
            ['idempleado' => 2, 'nombrecorto' => 'Ana López (Sim)'],
        ];
    } else if (empty($empleados_para_filtro)) {
        $empleados_para_filtro = [
            ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez (Sim)'],
            ['idempleado' => 2, 'nombrecorto' => 'Ana López (Sim)'],
        ];
    }
    $resumen_encargados_simulados = [
        ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez (Sim)', 'cantidad_temas' => 2],
        ['idempleado' => 2, 'nombrecorto' => 'Ana López (Sim)', 'cantidad_temas' => 1],
    ];
    if(empty($resumen_encargados)) $resumen_encargados = $resumen_encargados_simulados;
    $temas_simulados_todos = [
        ['idtema' => 1, 'descripcion' => 'Simulado: Tema A sobre renovables.', 'idencargado' => 1, 'nombre_encargado' => 'Juan Pérez (Sim)', 'comentario' => 'Simulado: Prioridad alta', 'activo' => 1],
        ['idtema' => 2, 'descripcion' => 'Simulado: Tema B sobre protección de datos.', 'idencargado' => 2, 'nombre_encargado' => 'Ana López (Sim)', 'comentario' => 'Simulado: Revisión legal', 'activo' => 1],
        ['idtema' => 3, 'descripcion' => 'Simulado: Tema C asignado a Juan.', 'idencargado' => 1, 'nombre_encargado' => 'Juan Pérez (Sim)', 'comentario' => 'Simulado: Otro de Juan.', 'activo' => 0],
        ['idtema' => 4, 'descripcion' => 'Simulado: Tema D sin encargado específico.', 'idencargado' => null, 'nombre_encargado' => 'N/A', 'comentario' => 'Simulado: Pendiente asignar.', 'activo' => 1]
    ];
    $temas_a_mostrar_simulados = [];
    if ($idencargado_filtro !== null) {
        foreach ($temas_simulados_todos as $tema_sim) {
            if ($tema_sim['idencargado'] == $idencargado_filtro && ($filtros_aplicados['activo'] ?? 1) == $tema_sim['activo'] ) {
                $temas_a_mostrar_simulados[] = $tema_sim;
            }
        }
    } else { 
        foreach ($temas_simulados_todos as $tema_sim) {
            if (($filtros_aplicados['activo'] ?? 1) == $tema_sim['activo']) {
                $temas_a_mostrar_simulados[] = $tema_sim;
            }
        }
    }
    $temas = $temas_a_mostrar_simulados;
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestión de Temas</h1>
        <a href="registrar_tema.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Tema
        </a>
    </div>

    <?php if (!empty($resumen_encargados)):
    ?>
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="text-primary">Filtro por Encargado:</h5>
        </div>
        <?php foreach ($resumen_encargados as $encargado_resumen): ?>
            <div class="col-xl-auto col-lg-3 col-md-4 col-sm-6 mb-3">
                <a href="temas.php?idencargado_filtro=<?php echo htmlspecialchars($encargado_resumen['idempleado']); ?>" class="text-decoration-none">
                    <div class="bg-primary text-white card h-100 <?php echo ($idencargado_filtro == $encargado_resumen['idempleado']) ? 'border-primary shadow' : 'shadow-sm hover-shadow'; ?>">
                        <div class="card-body text-center px-3 py-2">
                            <h6 class="card-title mb-1"><?php echo htmlspecialchars($encargado_resumen['nombrecorto']); ?></h6>
                            <span class="badge bg-secondary rounded-soft"><?php echo htmlspecialchars($encargado_resumen['cantidad_temas']); ?> Tema(s)</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
         <div class="col-xl-auto col-lg-3 col-md-4 col-sm-6 mb-3">
             <a href="temas.php" class="text-decoration-none">
                <div class="card h-100 <?php echo ($idencargado_filtro === null && !isset($_GET['idencargado_filtro'])) ? 'border-secondary shadow' : 'shadow-sm hover-shadow'; ?>">
                    <div class="card-body text-center px-3 py-2 d-flex flex-column justify-content-center">
                         <h6 class="card-title mb-1">Mostrar Todos (Activos)</h6>
                         <i class="fas fa-list text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <hr class="mb-4">
    <?php endif; ?>

    <!-- <div class="card mb-3">
        <div class="card-header">Filtro Adicional por Encargado</div>
        <div class="card-body">
            <form id="filtrosFormTemas" method="GET" action="temas.php">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="idencargado_filtro_select" class="form-label">Seleccionar Encargado:</label>
                        <select id="idencargado_filtro_select" name="idencargado_filtro" class="form-select">
                            <option value="">Todos los Encargados (Temas Activos)</option>
                            <?php if (!empty($empleados_para_filtro)):
                                foreach ($empleados_para_filtro as $emp):
                            ?>
                                    <option value="<?php echo htmlspecialchars($emp['idempleado']); ?>" <?php echo ($idencargado_filtro == $emp['idempleado']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['nombrecorto']); ?>
                                    </option>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-auto mt-3 mt-md-0">
                        <button type="submit" class="btn btn-info"><i class="fas fa-filter me-1"></i>Filtrar</button>
                        <a href="temas.php" class="btn btn-secondary ms-2"><i class="fas fa-times me-1"></i>Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div> -->

    <div class="card">
        <div class="card-body">
            <table id="tablaTemas" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th style="width: 35%;">Descripción</th>
                        <th>Encargado</th>
                        <th style="width: 25%;">Comentario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($temas)):
                        foreach ($temas as $tema):
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tema['idtema']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars(substr($tema['descripcion'], 0, 200) . (strlen($tema['descripcion']) > 200 ? '...' : ''))); ?></td>
                                <td><?php echo htmlspecialchars($tema['nombre_encargado'] ?? 'N/A'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars(substr($tema['comentario'], 0, 150) . (strlen($tema['comentario']) > 150 ? '...' : ''))); ?></td>
                                <td>
                                    <?php if (isset($tema['activo']) && $tema['activo'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?> 
                                </td>
                                <td>
                                    <a href="editar_tema.php?id=<?php echo $tema['idtema']; ?>" class="btn btn-primary btn-sm" title="Editar Tema">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-<?php echo (isset($tema['activo']) && $tema['activo'] == 1) ? 'success' : 'danger'; ?> btn-sm cambiar-estado-tema" 
                                            data-id="<?php echo $tema['idtema']; ?>"
                                            data-nombre="<?php echo htmlspecialchars(substr($tema['descripcion'], 0, 50) . '...'); ?>"
                                            data-estado-actual="<?php echo (isset($tema['activo']) ? $tema['activo'] : 1); ?>"
                                            title="<?php echo (isset($tema['activo']) && $tema['activo'] == 1) ? 'Desactivar' : 'Activar'; ?> Tema">
                                        <i class="fas fa-<?php echo (isset($tema['activo']) && $tema['activo'] == 1) ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                        endforeach;
                    else: 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay temas que coincidan con los filtros aplicados o no hay temas registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
require_once 'includes/modales.php'; 
require_once 'includes/footer.php'; 
?>

<script>
$(document).ready(function() {
    $('#tablaTemas').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']], 
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, 
            { responsivePriority: 2, targets: 5 }, 
            { orderable: false, targets: 5 }      
        ]
    });

    $('#tablaTemas tbody').on('click', '.cambiar-estado-tema', function () {
        var temaId = $(this).data('id');
        var temaDescripcion = $(this).data('nombre'); 
        var estadoActual = parseInt($(this).data('estado-actual'));
        var accion = estadoActual === 1 ? "desactivar" : "activar";
        var textoAccion = estadoActual === 1 ? "Desactivar" : "Activar";
        
        const modalConfirmarElement = document.getElementById('modalConfirmarGuardado');
        if (modalConfirmarElement) {
            const modalTitle = modalConfirmarElement.querySelector('.modal-title');
            const modalBody = modalConfirmarElement.querySelector('.modal-body');
            const btnConfirmarSubmit = modalConfirmarElement.querySelector('#btnConfirmarGuardarSubmit');
            
            if (modalTitle) modalTitle.textContent = `Confirmar ${textoAccion} Tema`;
            if (modalBody) modalBody.innerHTML = `¿Está seguro que desea ${accion} el tema "<strong>${temaDescripcion}</strong>"?`;
            if (btnConfirmarSubmit) btnConfirmarSubmit.textContent = `Sí, ${textoAccion}`;
            
            $(btnConfirmarSubmit).off('click').on('click', function() { 
                var form = $('<form action="procesar_tema.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${accion}">`);
                form.append(`<input type="hidden" name="idtema" value="${temaId}">`);
                $('body').append(form);
                form.submit();
            });

            var modalInstance = bootstrap.Modal.getInstance(modalConfirmarElement) || new bootstrap.Modal(modalConfirmarElement);
            modalInstance.show();
        } else {
            if(confirm(`¿Está seguro que desea ${accion} el tema "${temaDescripcion}"?`)){
                var form = $('<form action="procesar_tema.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${accion}">`);
                form.append(`<input type="hidden" name="idtema" value="${temaId}">`);
                $('body').append(form);
                form.submit();
            }
        }
    });
});
</script>