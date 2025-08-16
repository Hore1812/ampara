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
            <div class="card border-left-primary shadow h-100 py-2" style="background-color: rgba(1, 32, 96, 0.1);">
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
            <div class="card border-left-success shadow h-100 py-2" style="background-color: rgba(28, 200, 138, 0.1);">
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
            <div class="card border-left-warning shadow h-100 py-2" style="background-color: rgba(246, 194, 62, 0.1);">
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
            <div class="card border-left-info shadow h-100 py-2" style="background-color: rgba(54, 185, 204, 0.1);">
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
<script src="js/reporte_planificacion_liquidacion.js"></script>
