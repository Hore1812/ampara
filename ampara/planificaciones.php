<?php
$page_title = "Gestión de Planificaciones";
require_once 'includes/header.php';
require_once 'funciones.php';

// Obtener filtros si existen
$filtros_get = [
    'anio_planificado' => $_GET['anio_planificado'] ?? date('Y'), // Año actual por defecto
    'mes_planificado' => $_GET['mes_planificado'] ?? '',   // Mes actual por defecto, vacío para todos
    'activo' => $_GET['activo'] ?? '' // Por defecto mostrar todos los estados (activos/inactivos)
];

$planificaciones = obtenerTodasPlanificaciones($filtros_get);
$clientes_filtro = obtenerClientesActivosParaSelect(); // Para el select de filtro del modal (aunque ya no se use para filtrar, puede ser útil si se reactiva)

$anios_disponibles = []; 
$current_year = date('Y');
for ($i = $current_year + 2; $i >= $current_year - 5; $i--) {
    $anios_disponibles[] = $i;
}
$meses_espanol = [
    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];

?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo $page_title; ?></h1>
        <div>
            <a href="reporte_planificacion_liquidacion.php" class="btn btn-success me-2">  
                <i class="fas fa-chart-line me-2"></i>Reporte General de Planificaciones
            </a>
            <a href="registrar_planificacion.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Agregar Nueva Planificación
            </a>
        </div>
    </div>

    <!-- Filtros de la página -->
    <div class="card mb-3">
        <div class="card-body">
            <form id="filtrosPlanificacionFormPagina" method="GET" action="planificaciones.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="anio_planificado_filtro_pagina" class="form-label">Año Planificado</label>
                    <select name="anio_planificado" id="anio_planificado_filtro_pagina" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($anios_disponibles as $anio_opt): ?>
                            <option value="<?php echo $anio_opt; ?>" <?php echo ($filtros_get['anio_planificado'] == $anio_opt) ? 'selected' : ''; ?>>
                                <?php echo $anio_opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="mes_planificado_filtro_pagina" class="form-label">Mes Planificado</label>
                    <select name="mes_planificado" id="mes_planificado_filtro_pagina" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses_espanol as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" <?php echo ($filtros_get['mes_planificado'] == $num) ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="activo_filtro_pagina" class="form-label">Estado</label>
                    <select name="activo" id="activo_filtro_pagina" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?php echo ($filtros_get['activo'] === '1') ? 'selected' : ''; ?>>Activas</option>
                        <option value="0" <?php echo ($filtros_get['activo'] === '0') ? 'selected' : ''; ?>>Inactivas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="planificaciones.php" class="btn btn-secondary">Limpiar</a>
                    <button type="button" id="btnActualizarDetalles" class="btn btn-orange ms-2">Actualizar Detalles</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="tablaPlanificaciones" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Planificación</th>
                        <th>Cliente</th>
                        <th>Contrato</th>
                        <th>Mes Planificado</th>
                        <th class="text-end">Horas Plan.</th>
                        <th>Líder</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($planificaciones)): ?>
                        <?php foreach ($planificaciones as $plan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plan['idplanificacion']); ?></td>
                                <td><?php echo htmlspecialchars($plan['nombre_planificacion']); ?></td>
                                <td><?php echo htmlspecialchars($plan['nombre_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($plan['descripcion_contrato'] ?: 'N/A'); ?> (ID: <?php echo htmlspecialchars($plan['idcontratocli']); ?>)</td>
                                <td><?php 
                                    $fecha_plan = new DateTime($plan['mes_planificado']);
                                    echo htmlspecialchars($meses_espanol[$fecha_plan->format('m')] . " " . $fecha_plan->format('Y')); 
                                ?></td>
                                <td class="text-end"><?php echo htmlspecialchars(number_format($plan['horas_planificadas'], 2, ',', '.')); ?></td>
                                <td><?php echo htmlspecialchars($plan['nombre_lider'] ?: 'N/A'); ?></td>
                                <td>
                                    <?php if ($plan['activo'] == 1): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm ver-reporte-grafico-planificacion" 
                                            data-idplanificacion="<?php echo $plan['idplanificacion']; ?>"
                                            data-nombreplan="<?php echo htmlspecialchars($plan['nombre_planificacion']); ?>"
                                            title="Ver Reporte Gráfico de Planificación">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <a href="editar_planificacion.php?id=<?php echo $plan['idplanificacion']; ?>" class="btn btn-secondary btn-sm" title="Editar Planificación">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-<?php echo $plan['activo'] ? 'warning' : 'success'; ?> btn-sm cambiar-estado-planificacion" 
                                            data-id="<?php echo $plan['idplanificacion']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($plan['nombre_planificacion']); ?>"
                                            data-estado-actual="<?php echo $plan['activo']; ?>"
                                            title="<?php echo $plan['activo'] ? 'Desactivar' : 'Activar'; ?> Planificación">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay planificaciones registradas para los filtros seleccionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Reporte Gráfico de Planificación -->
<div class="modal fade" id="modalVerReportePlanificacion" tabindex="-1" aria-labelledby="modalVerReportePlanificacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerReportePlanificacionLabel">Reporte Gráfico de Planificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalVerReportePlanificacionBody">
                <input type="hidden" id="idPlanificacionActualModal" value="">
                <!-- Filtros dentro del Modal eliminados -->
                
                <div id="spinnerCargaReporteModal" class="text-center my-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-2">Cargando datos del reporte...</p>
                </div>
                <div id="errorReporteModal" class="alert alert-danger" style="display: none;"></div>
                
                <div id="contenidoReporteGrafico" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-header bg-black text-white">Total Horas Planificadas</div>
                                <div class="card-body">
                                    <h3 class="card-title" id="totalHorasPlanificadasModal">0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-header bg-primary text-white">Total Horas Liquidadas (Todos los Estados)</div>
                                <div class="card-body">
                                    <h3 class="card-title" id="totalHorasLiquidadasTodosEstadosModal">0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-header bg-success text-white">% Cumplimiento General</div>
                                <div class="card-body">
                                    <h3 class="card-title" id="porcentajeCumplimientoGeneralModal">0.00%</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-7 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header"><h5 class="mb-0">Horas Planificadas vs. Liquidadas por Estado</h5></div>
                                <div class="card-body">
                                    <canvas id="graficoComparativoHorasModal"></canvas>
                                    <div id="noDataGraficoComparativo" class="text-center text-muted py-4" style="display:none;">No hay datos para mostrar.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header"><h5 class="mb-0">Distribución de Horas Liquidadas por Estado</h5></div>
                                <div class="card-body">
                                    <canvas id="graficoDistribucionEstadosModal"></canvas>
                                    <div id="noDataGraficoDistribucion" class="text-center text-muted py-4" style="display:none;">No hay datos para mostrar.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <p class="text-muted small mt-3">Reporte basado en la vista: <code>vista_reporte_planificacion_vs_liquidacion</code> para la planificación seleccionada.</p>
                </div> 
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
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- chartjs-plugin-datalabels -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
$(document).ready(function() {
    // Registrar el plugin globalmente
    Chart.register(ChartDataLabels);

    //var tablaPlanificaciones = $('#tablaPlanificaciones').DataTable({
    var tablaPlanificaciones = $('#tablaPlanificaciones').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
        responsive: true,
        order: [[0, 'desc']], 
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, 
            { responsivePriority: 2, targets: 2 }, 
            { responsivePriority: 3, targets: 8 }, 
            { orderable: false, targets: 8 }, 
            { className: 'text-end', targets: 5 } 
        ],
        dom: "<\'row\'<\'col-sm-12 col-md-6\'l><\'col-sm-12 col-md-6\'f>>" +
             "<\'row\'<\'col-sm-12\'tr>>" +
             "<\'row\'<\'col-sm-12 col-md-5\'i><\'col-sm-12 col-md-7\'p>>",
    });

    let chartComparativoHoras = null;
    let chartDistribucionEstados = null;

    function destruirGraficosExistentes() {
        if (chartComparativoHoras) {
            chartComparativoHoras.destroy();
            chartComparativoHoras = null;
        }
        if (chartDistribucionEstados) {
            chartDistribucionEstados.destroy();
            chartDistribucionEstados = null;
        }
    }

    function cargarDatosReporte(idPlanificacionParam) { 
        if (!idPlanificacionParam) {
            $('#errorReporteModal').text('ID de Planificación no proporcionado para el reporte.').show();
            $('#spinnerCargaReporteModal').hide();
            $('#contenidoReporteGrafico').hide();
            $('#noDataGraficoComparativo').show();
            $('#noDataGraficoDistribucion').show();
            return;
        }

        $('#spinnerCargaReporteModal').show();
        $('#contenidoReporteGrafico').hide();
        $('#errorReporteModal').hide().text('');
        destruirGraficosExistentes();
        $('#noDataGraficoComparativo').hide();
        $('#noDataGraficoDistribucion').hide();

        $.ajax({
            url: 'ajax/obtener_datos_reporte_planificacion.php',
            method: 'POST',
            data: { 
                idplanificacion: idPlanificacionParam
            },
            dataType: 'json',
            success: function(response) {
                $('#spinnerCargaReporteModal').hide();
                if (response.success && response.data) {
                    $('#contenidoReporteGrafico').show();
                    actualizarTotales(response.data.totales);
                    renderizarGraficoComparativo(response.data.grafico_comparativo, response.data.totales.total_horas_planificadas);
                    renderizarGraficoDistribucion(response.data.grafico_distribucion, response.data.totales.total_horas_liquidadas_todos_estados);

                    $('#noDataGraficoComparativo').toggle(!response.data.grafico_comparativo || !response.data.grafico_comparativo.labels || response.data.grafico_comparativo.labels.length === 0);
                    $('#noDataGraficoDistribucion').toggle(!response.data.grafico_distribucion || !response.data.grafico_distribucion.labels || response.data.grafico_distribucion.labels.length === 0 || response.data.grafico_distribucion.valores.every(v => v === 0) );
                } else {
                    $('#errorReporteModal').text(response.message || 'No se encontraron datos para la planificación.').show();
                    $('#noDataGraficoComparativo').show();
                    $('#noDataGraficoDistribucion').show();
                     // Limpiar totales si no hay datos
                    actualizarTotales({total_horas_planificadas: 0, total_horas_liquidadas_todos_estados: 0, porcentaje_cumplimiento_general: 0});
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#spinnerCargaReporteModal').hide();
                console.error("Error AJAX: ", textStatus, errorThrown, jqXHR.responseText);
                $('#errorReporteModal').text('Error al cargar los datos del reporte. Revise la consola.').show();
                $('#noDataGraficoComparativo').show();
                $('#noDataGraficoDistribucion').show();
                 actualizarTotales({total_horas_planificadas: 0, total_horas_liquidadas_todos_estados: 0, porcentaje_cumplimiento_general: 0});
            }
        });
    }

    function actualizarTotales(totales) {
        $('#totalHorasPlanificadasModal').text(parseFloat(totales.total_horas_planificadas || 0).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#totalHorasLiquidadasTodosEstadosModal').text(parseFloat(totales.total_horas_liquidadas_todos_estados || 0).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#porcentajeCumplimientoGeneralModal').text(parseFloat(totales.porcentaje_cumplimiento_general || 0).toFixed(2) + '%');
    }

    function renderizarGraficoComparativo(data, totalHorasPlanificadasDelPlan) {
        console.log('Datos para gráfico comparativo:', data);
        const ctx = document.getElementById('graficoComparativoHorasModal').getContext('2d');
        if (!data || !data.labels || data.labels.length === 0) {
            $('#noDataGraficoComparativo').show();
            if(chartComparativoHoras) chartComparativoHoras.destroy();
            chartComparativoHoras = null;
            return;
        }
        $('#noDataGraficoComparativo').hide();
        if (chartComparativoHoras) chartComparativoHoras.destroy();

        const datasetPlanificadas = {
            label: 'Horas Planificadas (Total Plan)',
            data: data.labels.map(() => totalHorasPlanificadasDelPlan),
            type: 'line',
            borderColor: 'rgba(0, 0, 0, 0.8)', 
            backgroundColor: 'rgba(0, 0, 0, 0.1)',
            borderWidth: 2,
            borderDash: [5, 5], 
            fill: false,
            pointRadius: 0,
            order: 1 
        };

        const datasetLiquidadas = {
            label: 'Horas Liquidadas por Estado',
            data: data.liquidadas,
            backgroundColor: data.colores_liquidadas, 
            borderColor: data.colores_liquidadas.map(color => color.replace(/, ?0\.[0-9]+\)/, ', 1)')),
            borderWidth: 1,
            order: 2
        };

        chartComparativoHoras = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [datasetPlanificadas, datasetLiquidadas]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Horas' } },
                    x: { title: { display: true, text: 'Estado de Liquidación' } }
                },
                plugins: { 
                    legend: { position: 'top' }, 
                    tooltip: { 
                        mode: 'index', 
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                let value = context.parsed.y;
                                if (value !== null) {
                                    label += value.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + 'h';
                                    if (context.dataset.label === 'Horas Liquidadas por Estado' && totalHorasPlanificadasDelPlan > 0 && value > 0) {
                                        const percentage = (value / totalHorasPlanificadasDelPlan) * 100;
                                        label += ` (${percentage.toFixed(1)}% del Plan)`;
                                    }
                                }
                                return label;
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { 
                            mode: 'index', 
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    let value = context.parsed.y;
                                    if (value !== null) {
                                        label += value.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + 'h';
                                        if (context.dataset.label === 'Horas Liquidadas por Estado' && totalHorasPlanificadasDelPlan > 0 && value > 0) {
                                            const percentage = (value / totalHorasPlanificadasDelPlan) * 100;
                                            label += ` (${percentage.toFixed(1)}% del Plan)`;
                                        }
                                    }
                                    return label;
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            anchor: 'center',
                            align: 'center',
                            color: (context) => {
                                const bgColor = context.dataset.backgroundColor;
                                if (typeof bgColor === 'string') {
                                    const rgb = bgColor.match(/\d+/g);
                                    if (rgb && rgb.length >= 3) {
                                        const yiq = ((parseInt(rgb[0])*299)+(parseInt(rgb[1])*587)+(parseInt(rgb[2])*114))/1000;
                                        return (yiq >= 128) ? '#333' : 'white';
                                    }
                                }
                                return '#333';
                            },
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            formatter: (value, context) => {
                                if (context.dataset.label === 'Horas Planificadas (Total Plan)') {
                                    return value > 0 ? value.toLocaleString('es-ES', {minimumFractionDigits:1, maximumFractionDigits:1}) + 'h' : '';
                                }
                                if (context.dataset.label === 'Horas Liquidadas por Estado' && totalHorasPlanificadasDelPlan > 0) {
                                    const percentage = (value / totalHorasPlanificadasDelPlan) * 100;
                                    return value > 0 ? `${value.toLocaleString('es-ES', {minimumFractionDigits:1, maximumFractionDigits:1})}h\n(${percentage.toFixed(1)}%)` : '';
                                }
                                return value > 0 ? value.toLocaleString('es-ES', {minimumFractionDigits:1, maximumFractionDigits:1}) + 'h' : '';
                            }
                        }
                    },
                    barThickness: 80
                }
            }
        });
    }

    function renderizarGraficoDistribucion(data, totalHorasLiquidadasGlobal) {
        console.log('Datos para gráfico de distribución:', data);
        const ctx = document.getElementById('graficoDistribucionEstadosModal').getContext('2d');
         if (!data || !data.labels || data.labels.length === 0 || data.valores.every(v => v === 0)) {
            $('#noDataGraficoDistribucion').show();
            if(chartDistribucionEstados) chartDistribucionEstados.destroy(); 
            chartDistribucionEstados = null;
            return;
        }
        $('#noDataGraficoDistribucion').hide();
        if (chartDistribucionEstados) chartDistribucionEstados.destroy();

        chartDistribucionEstados = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Distribución de Horas Liquidadas',
                    data: data.valores,
                    backgroundColor: data.colores,
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                if (label) { label += ': '; }
                                label += value.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + 'h';
                                if (totalHorasLiquidadasGlobal > 0 && value > 0) { // Asegurar que el valor sea > 0 para mostrar %
                                    const percentage = (value / totalHorasLiquidadasGlobal) * 100;
                                    label += ` (${percentage.toFixed(1)}%)`;
                                }
                                return label;
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    if (label) { label += ': '; }
                                    label += value.toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + 'h';
                                    if (totalHorasLiquidadasGlobal > 0 && value > 0) {
                                        const percentage = (value / totalHorasLiquidadasGlobal) * 100;
                                        label += ` (${percentage.toFixed(1)}%)`;
                                    }
                                    return label;
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            formatter: (value, ctx) => {
                                let sum = 0;
                                let dataArr = ctx.chart.data.datasets[0].data;
                                dataArr.map(data => {
                                    sum += data;
                                });
                                let percentage = (value*100 / sum).toFixed(1) + '%';
                                return value > 0 ? `${value.toLocaleString('es-ES', {minimumFractionDigits:1, maximumFractionDigits:1})}h\n(${percentage})` : '';
                            },
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            textAlign: 'center'
                        }
                    }
                }
            }
        });
    }

    // Abrir modal de REPORTE GRÁFICO
    $('#tablaPlanificaciones tbody').on('click', '.ver-reporte-grafico-planificacion', function () {
        var planId = $(this).data('idplanificacion');
        var nombrePlan = $(this).data('nombreplan');
        
        $('#modalVerReportePlanificacionLabel').text('Reporte Gráfico: ' + nombrePlan);
        $('#idPlanificacionActualModal').val(planId); 

        var modalEl = document.getElementById('modalVerReportePlanificacion');
        var modalInstance = new bootstrap.Modal(modalEl);
        
        modalEl.addEventListener('shown.bs.modal', function () {
            console.log('Modal mostrado, cargando datos para plan ID:', planId);
            setTimeout(function() {
                cargarDatosReporte(planId);
            }, 200);
        }, { once: true });

        modalInstance.show();
    });
    
    $('#modalVerReportePlanificacion').on('hidden.bs.modal', function () {
        destruirGraficosExistentes();
        $('#errorReporteModal').hide().text('');
        $('#contenidoReporteGrafico').hide(); // Ocultar contenido para la próxima vez
        $('#noDataGraficoComparativo').hide();
        $('#noDataGraficoDistribucion').hide();
        $('#idPlanificacionActualModal').val(''); // Limpiar ID
    });

    // Cambiar estado de la planificación (Activar/Desactivar) - Lógica existente
    $('#tablaPlanificaciones tbody').on('click', '.cambiar-estado-planificacion', function () {
        var planId = $(this).data('id');
        var planNombre = $(this).data('nombre');
        var estadoActual = $(this).data('estado-actual');
        var accion = (estadoActual == 1) ? "desactivar" : "activar";
        
        const modalConfirmar = document.getElementById('modalConfirmarGuardado'); 
        if (modalConfirmar) {
            const modalTitle = modalConfirmar.querySelector('.modal-title');
            const modalBodyContent = modalConfirmar.querySelector('.modal-body');
            const btnConfirmarSubmit = modalConfirmar.querySelector('#btnConfirmarGuardarSubmit');
            
            modalTitle.textContent = `Confirmar ${accion.charAt(0).toUpperCase() + accion.slice(1)} Planificación`;
            modalBodyContent.innerHTML = `¿Está seguro que desea ${accion} la planificación <strong>${planNombre}</strong>?`;
            btnConfirmarSubmit.className = `btn btn-${accion === 'desactivar' ? 'warning' : 'success'}`;
            btnConfirmarSubmit.textContent = `Sí, ${accion.charAt(0).toUpperCase() + accion.slice(1)}`;
            
            $(btnConfirmarSubmit).off('click').on('click', function() { 
                var form = $('<form action="procesar_planificacion.php" method="POST" style="display:none;"></form>');
                form.append(`<input type="hidden" name="accion" value="${accion}">`);
                form.append(`<input type="hidden" name="idplanificacion" value="${planId}">`);
                form.append(`<input type="hidden" name="editor" value="<?php echo $_SESSION['idemp'] ?? 1; ?>">`);
                $('body').append(form);
                form.submit();
            });

            var modalInstance = bootstrap.Modal.getInstance(modalConfirmar) || new bootstrap.Modal(modalConfirmar);
            modalInstance.show();
        }
    });

    $('#btnActualizarDetalles').on('click', function() {
        $.ajax({
            url: 'procesar_actualizacion.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                const modal = new bootstrap.Modal(document.getElementById('modalMensaje'));
                const modalTitle = document.getElementById('modalMensajeTitle');
                const modalBody = document.getElementById('modalMensajeBody');

                if (response.success) {
                    modalTitle.textContent = 'Éxito';
                    modalBody.textContent = response.message;
                    modal.show();
                    $('#modalMensaje').on('hidden.bs.modal', function () {
                        location.reload();
                    });
                } else {
                    modalTitle.textContent = 'Error';
                    modalBody.textContent = response.message;
                    modal.show();
                }
            },
            error: function() {
                const modal = new bootstrap.Modal(document.getElementById('modalMensaje'));
                const modalTitle = document.getElementById('modalMensajeTitle');
                const modalBody = document.getElementById('modalMensajeBody');
                modalTitle.textContent = 'Error';
                modalBody.textContent = 'Error al conectar con el servidor.';
                modal.show();
            }
        });
    });
});
</script>

<!-- Modal para Mensajes -->
<div class="modal fade" id="modalMensaje" tabindex="-1" aria-labelledby="modalMensajeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalMensajeTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalMensajeBody">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>