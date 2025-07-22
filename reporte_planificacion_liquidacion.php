<?php
$page_title = "Reporte Planificación vs Liquidación";
require_once 'includes/header.php';
require_once 'funciones.php';

// Para poblar los selects de filtro
$anios_disponibles = [];
$current_year = date('Y');
for ($i = $current_year + 1; $i >= $current_year - 5; $i--) {
    $anios_disponibles[] = $i;
}
$meses_espanol = [
    '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril',
    '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto',
    '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];
$clientes = obtenerClientes();
?>

<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="mb-0"><i class="fas fa-chart-line me-2"></i><?php echo $page_title; ?></h3>
    </div>

    <!-- Filtros del Reporte -->
    <div class="card mb-3">
        <div class="card-body p-2">
            <form id="filtrosReporteForm" class="row gx-2 gy-2 align-items-end">
                <div class="col-md-3">
                    <select id="anio" name="anio" class="form-select form-select-sm">
                        <?php foreach ($anios_disponibles as $anio): ?>
                            <option value="<?php echo $anio; ?>" <?php echo ($anio == $current_year) ? 'selected' : ''; ?>><?php echo $anio; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="mes" name="mes" class="form-select form-select-sm">
                        <option value="">Todos los Meses</option>
                        <?php foreach ($meses_espanol as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="idcliente" name="idcliente" class="form-select form-select-sm">
                        <option value="">Todos los Clientes</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['idcliente']; ?>"><?php echo htmlspecialchars($cliente['nombrecomercial']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="btnGenerarReporte" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-sync-alt me-1"></i>Generar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Indicador de Carga y Errores -->
    <div id="spinnerCarga" class="text-center my-5" style="display: none;">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Cargando datos...</p>
    </div>
    <div id="errorReporte" class="alert alert-danger" style="display: none;"></div>
    <div id="noDatos" class="alert alert-warning text-center" style="display: none;">
        <i class="fas fa-info-circle me-2"></i>No se encontraron datos para los filtros seleccionados.
    </div>

    <!-- Summary Cards -->
    <div id="summaryCards" class="row mb-3" style="display: none;">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Horas Planificadas</div>
                            <div id="totalHorasPlanificadas" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Horas Liquidadas</div>
                            <div id="totalHorasLiquidadas" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Cumplimiento (Completo vs Plan)</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div id="porcentajeCompletado" class="h5 mb-0 mr-3 font-weight-bold text-gray-800">0%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div id="porcentajeCompletadoProgress" class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cumplimiento General</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div id="porcentajeGeneral" class="h5 mb-0 mr-3 font-weight-bold text-gray-800">0%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div id="porcentajeGeneralProgress" class="progress-bar bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor para la Tabla de Doble Entrada -->
    <div id="contenedorDobleEntrada" class="card mb-3" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-th me-2"></i>Horas Liquidadas por Contrato y Estado</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-9">
                    <div class="table-responsive">
                        <table id="tablaDobleEntrada" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <!-- Cabeceras de estados se insertarán dinámicamente -->
                            </thead>
                            <tbody>
                                <!-- Filas de contratos se insertarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="contenedorCanvasGraficoDobleEntrada" class="card mb-3" style="min-height: 250px;">
                        <canvas id="graficoDobleEntrada"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor para Gráfico de Barras -->
    <div id="contenedorGraficoBarras" class="card mb-3" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Horas por Estado de Liquidación</h5>
        </div>
        <div id="contenedorCanvasGrafico" class="card-body" style="min-height: 800px;">
            <canvas id="graficoBarras"></canvas>
        </div>
    </div>

    <!-- Contenedor para Detalles de Colaboradores -->
    <div id="contenedorColaboradores" class="card" style="display: none;">
        <div class="card-header">
             <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detalle por Colaborador</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <!-- Gráfico de Colaboradores -->
                    <div id="contenedorCanvasColaboradores" class="card mb-3" style="min-height: 300px;">
                         <canvas id="graficoColaboradores"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Tabla de Colaboradores -->
                    <div class="table-responsive">
                        <table id="tablaColaboradores" class="table table-striped table-hover" style="width:100%">
                    <thead class="table-dark text-center">
                                <tr>
                                    <th>Colaborador</th>
                                    <th>Horas Asignadas</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

<!-- Chart.js, DataTables y plugins -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<script>
$(document).ready(function() {
    let chartBarras = null;
    let chartColaboradores = null;
    let tablaDobleEntrada = null;
    let tablaColaboradores = null;
    let chartDobleEntrada = null;

    Chart.register(ChartDataLabels);

    function generarReporte() {
        $('#spinnerCarga').show();
        $('#contenedorDobleEntrada, #contenedorGraficoBarras, #contenedorColaboradores, #noDatos, #errorReporte').hide();

        $.ajax({
            url: 'ajax/obtener_reporte_planificacion_liquidacion.php',
            method: 'POST',
            data: $('#filtrosReporteForm').serialize(),
            dataType: 'json',
            success: function(response) {
                $('#spinnerCarga').hide();
                if (response.success && response.data) {
                    $('#summaryCards').show();
                    $('#contenedorDobleEntrada').show();
                    $('#contenedorGraficoBarras').show();
                    $('#contenedorColaboradores').show();
                    
                    renderizarSummaryCards(response.data.summary);
                    renderizarTablaDobleEntrada(response.data.contratos);
                    renderizarGraficoDobleEntrada(response.data.estados);
                    renderizarGraficoBarras(response.data.estados);
                    renderizarGraficoColaboradores(response.data.colaboradores);
                    renderizarTablaColaboradores(response.data.colaboradores);
                } else if (response.success) {
                    $('#noDatos').show();
                } else {
                    $('#errorReporte').text(response.message || 'Ocurrió un error.').show();
                }
            },
            error: function() {
                $('#spinnerCarga').hide();
                $('#errorReporte').text('Error de conexión al generar el reporte.').show();
            }
        });
    }

    function renderizarTablaDobleEntrada(data) {
        const tabla = $('#tablaDobleEntrada');
        const thead = tabla.find('thead');
        const tbody = tabla.find('tbody');
        thead.empty();
        tbody.empty();

        if (data.length === 0) return;

        // Agrupar por contrato
        const contratos = {};
        const estados = new Set();
        data.forEach(item => {
            contratos[item.contrato_cliente] = {
                horas_planificadas: parseFloat(item.horas_planificadas),
                estados: item.estados
            };
            for (const estado in item.estados) {
                estados.add(estado);
            }
        });

        const estadosArray = Array.from(estados);

        // Crear cabecera
        const colorMapping = {};
        const backgroundColors = [
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)'
        ];
        let headerRow = '<tr class="text-center"><th>Contrato</th><th>Horas Planificadas</th>';
        estadosArray.forEach((estado, index) => {
            colorMapping[estado] = backgroundColors[index % backgroundColors.length];
            headerRow += `<th style="background-color: ${colorMapping[estado]};">${estado}</th>`;
        });
        headerRow += '</tr>';
        thead.append(headerRow);

        // Crear cuerpo
        const totales = {};
        for (const contrato in contratos) {
            let bodyRow = `<tr><td>${contrato}</td><td>${parseFloat(contratos[contrato].horas_planificadas).toFixed(2)}h</td>`;
            estadosArray.forEach(estado => {
                const horas = parseFloat(contratos[contrato].estados[estado] || 0);
                const porcentaje = (horas / parseFloat(contratos[contrato].horas_planificadas) * 100);
                const color = getCellColor(porcentaje);
                bodyRow += `<td style="background-color: ${color};">${horas}h (${porcentaje.toFixed(2)}%)</td>`;
                if (!totales[estado]) {
                    totales[estado] = 0;
                }
                totales[estado] += horas;
            });
            bodyRow += '</tr>';
            tbody.append(bodyRow);
        }

        // Crear fila de totales
        let totalHorasPlanificadas = 0;
        for (const contrato in contratos) {
            totalHorasPlanificadas += contratos[contrato].horas_planificadas;
        }
        let totalRow = `<tr class="text-center">
                            <td style="background-color: #212529; color: white;"><strong>Total</strong></td>
                            <td style="background-color: #212529; color: white;"><strong>${totalHorasPlanificadas.toFixed(2)}h</strong></td>`;
        estadosArray.forEach(estado => {
            const totalHoras = totales[estado] || 0;
            const totalPorcentaje = (totalHoras / totalHorasPlanificadas * 100).toFixed(2);
            const color = colorMapping[estado];
            totalRow += `<td style="background-color: ${color}; color: white;"><strong>${totalHoras}h (${totalPorcentaje}%)</strong></td>`;
        });
        totalRow += '</tr>';
        tbody.append(totalRow);
    }

    function renderizarGraficoBarras(data) {
        const ctx = document.getElementById('graficoBarras').getContext('2d');
        if (chartBarras) chartBarras.destroy();

        if (data.length === 0) return;

        const contratos = {};
        const estados = new Set();
        data.forEach(item => {
            if (!contratos[item.contrato_cliente]) {
                contratos[item.contrato_cliente] = {
                    horas_planificadas: parseFloat(item.horas_planificadas),
                    estados: {}
                };
            }
            contratos[item.contrato_cliente].estados[item.estado_liquidacion] = parseFloat(item.total_horas);
            estados.add(item.estado_liquidacion);
        });

        const labels = Object.keys(contratos);
        const estadosArray = Array.from(estados);
        const datasets = [];

        const colorMapping = {};
        const backgroundColors = [
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)'
        ];
        estadosArray.forEach((estado, index) => {
            colorMapping[estado] = backgroundColors[index % backgroundColors.length];
            datasets.push({
                label: estado,
                data: labels.map(contrato => contratos[contrato].estados[estado] || 0),
                backgroundColor: colorMapping[estado],
            });
        });

        datasets.forEach(dataset => {
            dataset.barThickness = 33;
        });

        datasets.unshift({
            label: 'Horas Planificadas',
            data: labels.map(contrato => contratos[contrato] ? contratos[contrato].horas_planificadas : 0),
            backgroundColor: 'rgba(0, 0, 0, 0.2)',
            stack: 'planificadas',
            barPercentage: 0.9,
            categoryPercentage: 0.9,
        });


        chartBarras = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                },
                plugins: {
                    legend: { display: true },
                    datalabels: {
                        anchor: 'center',
                        align: 'center',
                        formatter: (value, context) => {
                            const dataset = context.chart.data.datasets[context.datasetIndex];
                            const total = dataset.data.reduce((acc, cur) => acc + cur, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(2) : 0;
                            return `${value.toFixed(2)}h (${percentage}%)`;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    }

    function renderizarGraficoColaboradores(data) {
        const ctx = document.getElementById('graficoColaboradores').getContext('2d');
        if (chartColaboradores) chartColaboradores.destroy();

        if (data.length === 0) return;

        const labels = data.map(item => item.colaborador);
        const values = data.map(item => parseFloat(item.horas_asignadas));

        chartColaboradores = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Horas Asignadas',
                    data: values,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'center',
                        align: 'center',
                        formatter: (value, context) => {
                            let sum = 0;
                            let dataArr = context.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                            return `${value.toFixed(2)}h (${percentage})`;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    }

    function renderizarTablaColaboradores(data) {
        const tabla = $('#tablaColaboradores');
        const tbody = tabla.find('tbody');
        tbody.empty();

        if (data.length === 0) return;

        let totalHorasAsignadas = 0;
        data.forEach(item => {
            totalHorasAsignadas += parseFloat(item.horas_asignadas);
        });

        data.forEach(item => {
            const horasAsignadas = parseFloat(item.horas_asignadas);
            const porcentaje = totalHorasAsignadas > 0 ? (horasAsignadas / totalHorasAsignadas * 100).toFixed(2) : 0;
            let row = `<tr>
                <td>${item.colaborador}</td>
                <td>${horasAsignadas.toFixed(2)}</td>
                <td>${porcentaje}%</td>
            </tr>`;
            tbody.append(row);
        });

        // Add totals row
        let totalRow = `<tr>
            <td><strong>Total</strong></td>
            <td><strong>${totalHorasAsignadas.toFixed(2)}</strong></td>
            <td><strong>100%</strong></td>
        </tr>`;
        tbody.append(totalRow);
    }

    function renderizarSummaryCards(data) {
        const totalHorasPlanificadas = parseFloat(data.total_horas_planificadas) || 0;
        const totalHorasLiquidadas = parseFloat(data.total_horas_liquidadas) || 0;
        const totalHorasCompletadas = parseFloat(data.total_horas_completadas) || 0;
        const porcentajeGeneral = totalHorasPlanificadas > 0 ? (totalHorasLiquidadas / totalHorasPlanificadas * 100).toFixed(2) : 0;
        const porcentajeCompletado = totalHorasPlanificadas > 0 ? (totalHorasCompletadas / totalHorasPlanificadas * 100).toFixed(2) : 0;

        $('#totalHorasPlanificadas').text(totalHorasPlanificadas.toFixed(2) + 'h');
        $('#totalHorasLiquidadas').text(totalHorasLiquidadas.toFixed(2) + 'h');
        $('#porcentajeGeneral').text(porcentajeGeneral + '%');
        $('#porcentajeGeneralProgress').css('width', porcentajeGeneral + '%').attr('aria-valuenow', porcentajeGeneral);
        $('#porcentajeCompletado').text(porcentajeCompletado + '%');
        $('#porcentajeCompletadoProgress').css('width', porcentajeCompletado + '%').attr('aria-valuenow', porcentajeCompletado);
    }

    function getCellColor(percentage) {
        const alpha = percentage / 100;
        return `rgba(0, 255, 0, ${alpha})`;
    }

    function renderizarGraficoDobleEntrada(data) {
        const ctx = document.getElementById('graficoDobleEntrada').getContext('2d');
        if (chartDobleEntrada) chartDobleEntrada.destroy();

        if (data.length === 0) return;

        const estados = {};
        data.forEach(item => {
            if (!estados[item.estado_liquidacion]) {
                estados[item.estado_liquidacion] = 0;
            }
            estados[item.estado_liquidacion] += parseFloat(item.total_horas);
        });

        const labels = Object.keys(estados);
        const values = Object.values(estados);

        chartDobleEntrada = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Horas por Estado',
                    data: values,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    datalabels: {
                        anchor: 'center',
                        align: 'center',
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                            return `${value.toFixed(2)}h (${percentage})`;
                        },
                        color: '#fff',
                    }
                }
            }
        });
    }

    $('#btnGenerarReporte').click(generarReporte);
    generarReporte(); // Carga inicial
});
</script>
