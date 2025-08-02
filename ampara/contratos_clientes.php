<?php
$page_title = "Gestión de Contratos de Clientes";
require_once 'includes/header.php';
require_once 'funciones.php'; 

// Obtener líderes para las tarjetas de filtro
$resumen_lideres = obtenerResumenContratosPorLider();

// Obtener empleados para el filtro desplegable
$empleados_para_filtro = obtenerEmpleadosActivosParaSelect(); 

// Manejo del filtro por líder desde la URL (para tarjetas y select)
$id_lider_filtro = null;
if (isset($_GET['id_lider_filtro']) && $_GET['id_lider_filtro'] !== '') {
    $id_lider_filtro_val = filter_var($_GET['id_lider_filtro'], FILTER_VALIDATE_INT);
    if ($id_lider_filtro_val !== false) {
        $id_lider_filtro = $id_lider_filtro_val;
    }
}

$filtros_aplicados = [];
if ($id_lider_filtro !== null) {
    $filtros_aplicados['id_lider_filtro'] = $id_lider_filtro;
}

$contratos = obtenerTodosContratosClientes($filtros_aplicados);

if (empty($contratos) && empty($filtros_aplicados)) {
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
    if(empty($resumen_lideres)) {
      $resumen_lideres = [
        ['idempleado' => 1, 'nombrecorto' => 'Juan Pérez (Sim)', 'cantidad_contratos' => 2],
        ['idempleado' => 2, 'nombrecorto' => 'Ana López (Sim)', 'cantidad_contratos' => 1]
      ];
    }
    $contratos_simulados_completos_para_fallback = [
        [
            'idcontratocli' => 101, 'idcliente' => 1, 'nombre_cliente' => 'Cliente Alfa (Sim)',
            'lider' => 1, 'nombre_lider' => 'Juan Pérez (Sim)',
            'descripcion' => 'Sim: Contrato de desarrollo app móvil.',
            'fechainicio' => '2024-01-10', 'fechafin' => '2024-07-10',
            'horasfijasmes' => 50, 'costohorafija' => 60.00, 'mesescontrato' => 6,
            'totalhorasfijas' => 300, 'montofijomes' => 3000.00,
            'tipobolsa' => 'Proyecto Cerrado', 'costohoraextra' => 75.00,
            'planmontomes' => 3000.00, 'planhoraextrames' => 10,
            'status' => 'En Progreso', 'tipohora' => 'No Soporte', 'activo' => 1
        ],
        [
            'idcontratocli' => 102, 'idcliente' => 2, 'nombre_cliente' => 'Empresa Beta (Sim)',
            'lider' => 2, 'nombre_lider' => 'Ana López (Sim)',
            'descripcion' => 'Sim: Soporte técnico mensual.',
            'fechainicio' => '2023-11-01', 'fechafin' => null,
            'horasfijasmes' => 20, 'costohorafija' => 55.00, 'mesescontrato' => 12, 
            'totalhorasfijas' => 240, 'montofijomes' => 1100.00,
            'tipobolsa' => 'Retainer', 'costohoraextra' => 60.00,
            'planmontomes' => 1100.00, 'planhoraextrames' => 5,
            'status' => 'Vigente', 'tipohora' => 'Soporte', 'activo' => 1
        ],
    ];
   $contratos_temp = $contratos_simulados_completos_para_fallback;
   if ($id_lider_filtro !== null) {
        $contratos_filtrados_simulados = [];
        foreach ($contratos_temp as $c) {
            if ($c['lider'] == $id_lider_filtro) { 
                $contratos_filtrados_simulados[] = $c;
            }
        }
        $contratos = $contratos_filtrados_simulados;
    } else {
        $contratos = $contratos_temp;
    }
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Contratos de Clientes</h1>
        <a href="registrar_contrato_cliente.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Contrato
        </a>
    </div>

    <?php if (!empty($resumen_lideres)):
    ?>
    <div class="row mb-3">
        <div class="col-12">
            <!-- <h5 class="text-primary">Filtro por Líder:</h5> -->
        </div>
        <?php foreach ($resumen_lideres as $lider): ?>
            <div class="col-xl-auto col-lg-3 col-md-4 col-sm-6 mb-3">
                <a href="contratos_clientes.php?id_lider_filtro=<?php echo htmlspecialchars($lider['idempleado']); ?>" class="text-decoration-none">
                    <div class="card h-100 <?php echo ($id_lider_filtro == $lider['idempleado']) ? 'border-primary shadow' : 'shadow-sm hover-shadow'; ?>">
                        <div class="card-body text-center px-3 py-2">
                            <h6 class="card-title mb-1"><?php echo htmlspecialchars($lider['nombrecorto']); ?></h6>
                            <span class="badge bg-info rounded-soft"><?php echo htmlspecialchars($lider['cantidad_contratos']); ?> Contrato(s)</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
        <div class="col-xl-auto col-lg-3 col-md-4 col-sm-6 mb-3">
             <a href="contratos_clientes.php" class="text-decoration-none"> 
                <div class="card h-100 <?php echo ($id_lider_filtro === null && !isset($_GET['id_lider_filtro'])) ? 'border-secondary shadow' : 'shadow-sm hover-shadow'; ?>">
                    <div class="card-body text-center px-3 py-2 d-flex flex-column justify-content-center">
                         <h6 class="card-title mb-1">Mostrar Todos</h6>
                         <i class="fas fa-list text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <hr class="mb-4">
    <?php endif; ?>
    
    <!-- <div class="card mb-3">
        <div class="card-header">Filtro Adicional</div>
        <div class="card-body">
            <form id="filtrosFormContratos" method="GET" action="contratos_clientes.php">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="id_lider_filtro_select" class="form-label">Líder (Select):</label>
                        <select id="id_lider_filtro_select" name="id_lider_filtro" class="form-select">
                            <option value="">Todos los Líderes</option>
                            <?php if(!empty($empleados_para_filtro)):
                                foreach ($empleados_para_filtro as $emp):
                            ?>
                                    <option value="<?php echo htmlspecialchars($emp['idempleado']); ?>" <?php echo ($id_lider_filtro == $emp['idempleado']) ? 'selected' : ''; ?>>
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
                        <a href="contratos_clientes.php" class="btn btn-secondary ms-2"><i class="fas fa-times me-1"></i>Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div> -->

    <div class="card">
        <div class="card-body">
            <table id="tablaContratosClientes" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Líder</th>
                        <th style="width: 30%;">Descripción</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Tipo Hora</th>
                        <th>Status</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contratos)):
                        foreach ($contratos as $contrato):
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contrato['idcontratocli']); ?></td>
                                <td><?php echo htmlspecialchars($contrato['nombre_cliente'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contrato['nombre_lider'] ?? 'N/A'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars(substr($contrato['descripcion'] ?? '', 0, 100) . (strlen($contrato['descripcion'] ?? '') > 100 ? '...' : ''))); ?></td>
                                <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($contrato['fechainicio']))); ?></td>
                                <td><?php echo $contrato['fechafin'] ? htmlspecialchars(date("d/m/Y", strtotime($contrato['fechafin']))) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($contrato['tipohora'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contrato['status'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (isset($contrato['activo']) && $contrato['activo'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm ver-contrato" 
                                            data-id="<?php echo $contrato['idcontratocli']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalVerContratoCliente"
                                            title="Ver Detalles del Contrato">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="editar_contrato_cliente.php?id=<?php echo $contrato['idcontratocli']; ?>" class="btn btn-warning btn-sm" title="Editar Contrato">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-<?php echo (isset($contrato['activo']) && $contrato['activo']) ? 'danger' : 'success'; ?> btn-sm cambiar-estado-contrato" 
                                            data-id="<?php echo $contrato['idcontratocli']; ?>"
                                            data-nombre="ID <?php echo htmlspecialchars($contrato['idcontratocli']); ?> (Cliente: <?php echo htmlspecialchars($contrato['nombre_cliente'] ?? 'N/A'); ?>)"
                                            data-estado-actual="<?php echo (isset($contrato['activo']) ? $contrato['activo'] : 0); ?>"
                                            title="<?php echo (isset($contrato['activo']) && $contrato['activo']) ? 'Desactivar' : 'Activar'; ?> Contrato">
                                        <i class="fas fa-<?php echo (isset($contrato['activo']) && $contrato['activo']) ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                        endforeach;
                    else: 
                    ?>
                        <tr>
                            <td colspan="10" class="text-center">No hay contratos registrados o que coincidan con el filtro.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerContratoCliente" tabindex="-1" aria-labelledby="modalVerContratoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerContratoClienteLabel">Detalles del Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalVerContratoClienteBody">
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
    $('#tablaContratosClientes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, 
            { responsivePriority: 2, targets: 3 }, 
            { responsivePriority: 3, targets: 9 }, 
            { orderable: false, targets: 9 }      
        ]
    });

    $('#tablaContratosClientes tbody').on('click', '.ver-contrato', function () {
        var contratoId = $(this).data('id');
        var modalBody = $('#modalVerContratoClienteBody');
        var modalLabel = $('#modalVerContratoClienteLabel');
        
        modalBody.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        
        var contratosData = <?php echo json_encode($contratos); ?>; 
        var data = contratosData.find(c => c.idcontratocli == contratoId);

        if(data){
            modalLabel.text(`Detalles Contrato #${data.idcontratocli} - Cliente: ${data.nombre_cliente || 'N/A'}`);
            
            const formatDate = (dateString) => {
                if (!dateString || dateString === '0000-00-00') return 'N/A';
                const dateParts = dateString.split('-');
                if (dateParts.length === 3) {
                    const date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                     return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                }
                return dateString; 
            };
            const formatCurrency = (value) => {
                const num = parseFloat(value);
                return isNaN(num) ? 'N/A' : `S/ ${num.toFixed(2)}`;
            };

            var contentHtml = `
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5><i class="fas fa-file-signature me-2 text-primary"></i>Información Principal</h5>
                            <p><strong>ID Contrato:</strong> ${data.idcontratocli}</p>
                            <p><strong>Cliente:</strong> ${data.nombre_cliente || 'N/A'}</p>
                            <p><strong>Líder Asignado:</strong> ${data.nombre_lider || 'N/A'}</p>
                            <p><strong>Descripción:</strong></p>
                            <div style="white-space: pre-wrap; background-color: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 150px; overflow-y: auto;">${data.descripcion || 'N/A'}</div>
                            <p class="mt-2"><strong>Fecha Inicio:</strong> ${formatDate(data.fechainicio)}</p>
                            <p><strong>Fecha Fin:</strong> ${formatDate(data.fechafin)}</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-coins me-2 text-success"></i>Condiciones Económicas y Horas</h5>
                            <p><strong>Horas Fijas/Mes:</strong> ${data.horasfijasmes !== null ? data.horasfijasmes : 'N/A'} hrs</p>
                            <p><strong>Costo por Hora Fija:</strong> ${formatCurrency(data.costohorafija)}</p>
                            <p><strong>Monto Fijo Mensual (Calc.):</strong> ${formatCurrency(data.montofijomes)}</p>
                            <p><strong>Meses de Contrato:</strong> ${data.mesescontrato !== null ? data.mesescontrato : 'N/A'}</p>
                            <p><strong>Total Horas Fijas (Calc.):</strong> ${data.totalhorasfijas !== null ? data.totalhorasfijas : 'N/A'} hrs</p>
                            <p><strong>Tipo de Bolsa/Adicional:</strong> ${data.tipobolsa || 'N/A'}</p>
                            <p><strong>Costo Hora Extra:</strong> ${formatCurrency(data.costohoraextra)}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-tasks me-2 text-info"></i>Planificación y Facturación</h5>
                            <p><strong>Plan Monto/Mes (Fact.):</strong> ${formatCurrency(data.planmontomes)}</p>
                            <p><strong>Plan Horas Extra/Mes:</strong> ${data.planhoraextrames !== null ? data.planhoraextrames : 'N/A'} hrs</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle me-2 text-warning"></i>Estado y Tipo</h5>
                            <p><strong>Status del Contrato:</strong> ${data.status || 'N/A'}</p>
                            <p><strong>Tipo de Hora (Fact.):</strong> ${data.tipohora || 'N/A'}</p>
                            <p><strong>Estado del Registro:</strong> ${(data.activo !== undefined && data.activo == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'}</p>
                        </div>
                    </div>
                </div>
            `;
            modalBody.html(contentHtml);
        } else {
            modalBody.html('<p class="text-danger">No se encontraron datos para este contrato (ID: '+contratoId+').</p>');
        }
    });

    $('#tablaContratosClientes tbody').on('click', '.cambiar-estado-contrato', function () {
        var contratoId = $(this).data('id');
        var contratoNombre = $(this).data('nombre'); 
        var estadoActual = parseInt($(this).data('estado-actual'));
        var accion = estadoActual === 1 ? "desactivar" : "activar";
        var textoAccion = estadoActual === 1 ? "Desactivar" : "Activar";
        
        const modalConfirmarElement = document.getElementById('modalConfirmarGuardado'); 
        if (modalConfirmarElement) {
            const modalTitle = modalConfirmarElement.querySelector('.modal-title');
            const modalBody = modalConfirmarElement.querySelector('.modal-body');
            const btnConfirmarSubmit = modalConfirmarElement.querySelector('#btnConfirmarGuardarSubmit');
            
            if (modalTitle) modalTitle.textContent = `Confirmar ${textoAccion} Contrato`;
            if (modalBody) modalBody.innerHTML = `¿Está seguro que desea ${accion} el contrato <strong>${contratoNombre}</strong>?`;
            if (btnConfirmarSubmit) btnConfirmarSubmit.textContent = `Sí, ${textoAccion}`;
            
            $(btnConfirmarSubmit).off('click').on('click', function() { 
                var form = $('<form action="procesar_contrato_cliente.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${accion}">`);
                form.append(`<input type="hidden" name="idcontratocli" value="${contratoId}">`);
                $('body').append(form);
                form.submit();
            });

            var modalInstance = bootstrap.Modal.getInstance(modalConfirmarElement) || new bootstrap.Modal(modalConfirmarElement);
            modalInstance.show();
        } else {
            if(confirm(`¿Está seguro que desea ${accion} el contrato ${contratoNombre}?`)){
                var form = $('<form action="procesar_contrato_cliente.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${accion}">`);
                form.append(`<input type="hidden" name="idcontratocli" value="${contratoId}">`);
                $('body').append(form);
                form.submit();
            }
        }
    });
});
</script>