<?php
$page_title = "Reporte General de Planificaciones";
require_once 'includes/header.php';
require_once 'funciones.php';

// Valores iniciales para los filtros
$anio_actual = date('Y');
$mes_actual = date('m');

$anio_filtro = $_GET['anio'] ?? $anio_actual;
$mes_filtro = $_GET['mes'] ?? ''; // Por defecto, no filtrar por mes para mostrar todo el año
$cliente_filtro = $_GET['idcliente'] ?? '';

// Para poblar los selects del formulario de filtros
$anios_disponibles = [];
$current_year = date('Y');
for ($i = $current_year + 2; $i >= $current_year - 5; $i--) {
    $anios_disponibles[] = $i;
}
$meses_espanol = [
    '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', 
    '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', 
    '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];
$clientes_activos = obtenerClientesActivosParaSelect();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-chart-line me-3"></i><?php echo $page_title; ?></h1>
        <a href="planificaciones.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Volver</a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filtrosReporteGeneralForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="anioFiltro" class="form-label">Año</label>
                    <select id="anioFiltro" name="anio" class="form-select">
                        <?php foreach ($anios_disponibles as $a): ?>
                            <option value="<?php echo $a; ?>" <?php echo ($a == $anio_filtro) ? 'selected' : ''; ?>><?php echo $a; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="mesFiltro" class="form-label">Mes</label>
                    <select id="mesFiltro" name="mes" class="form-select">
                        <option value="">Todo el Año</option>
                        <?php foreach ($meses_espanol as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" <?php echo ($num == $mes_filtro) ? 'selected' : ''; ?>><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="clienteFiltro" class="form-label">Cliente</label>
                    <select id="clienteFiltro" name="idcliente" class="form-select">
                        <option value="">Todos los Clientes</option>
                        <?php foreach ($clientes_activos as $cliente): ?>
                            <option value="<?php echo $cliente['idcliente']; ?>" <?php echo ($cliente['idcliente'] == $cliente_filtro) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cliente['nombrecomercial']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor para la Carga y Errores -->
    <div id="spinnerCargaReporte" class="text-center my-5">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;"></div>
        <p class="mt-3 fs-5">Cargando...</p>
    </div>
    <div id="errorReporte" class="alert alert-danger text-center" style="display: none;"></div>
    <div id="noDatosReporte" class="alert alert-info text-center" style="display: none;">No se encontraron datos.</div>

    <!-- KPIs -->
    <div id="kpisReporteGeneral" class="row mb-3" style="display: none;">
        <div class="col"><div class="card text-center"><div class="card-header bg-primary text-white p-1">H. Planificadas</div><div class="card-body p-2"><h4 class="my-0" id="kpiTotalHorasPlanificadas"></h4></div></div></div>
        <div class="col"><div class="card text-center"><div class="card-header bg-info text-white p-1">H. Liquidadas</div><div class="card-body p-2"><h4 class="my-0" id="kpiTotalHorasLiquidadasTodosEstados"></h4></div></div></div>
        <div class="col"><div class="card text-center"><div class="card-header bg-success text-white p-1">H. Completas</div><div class="card-body p-2"><h4 class="my-0" id="kpiTotalHorasLiquidadasCompletas"></h4></div></div></div>
        <div class="col"><div class="card text-center"><div class="card-header bg-warning text-dark p-1">% Cump. (Completas)</div><div class="card-body p-2"><h4 class="my-0" id="kpiPorcentajeCumplimiento"></h4></div></div></div>
        <div class="col"><div class="card text-center"><div class="card-header bg-secondary text-white p-1">% Cump. (General)</div><div class="card-body p-2"><h4 class="my-0" id="kpiPorcentajeCumplimientoTodosEstados"></h4></div></div></div>
    </div>

    <!-- Tabla Doble Entrada -->
    <div id="contenedorTablaDobleEntrada" class="card mb-3" style="display: none;">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-table me-2"></i>Matriz de Horas por Cliente vs. Estado</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaDobleEntradaClientes" class="table table-bordered table-hover">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
            </div>
            <div class="mt-2 small"><strong>Leyenda:</strong><span class="px-2 ms-2 me-2" style="background-color: rgba(75, 192, 192, 0.1);"></span>Horas > 0</div>
        </div>
    </div>

    <!-- Gráfico por Cliente -->
    <div id="contenedorGrafico" class="card" style="display: none;">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Planificado vs. Liquidado por Cliente</h5></div>
        <div class="card-body" style="min-height: 450px;"><canvas id="graficoPorCliente"></canvas></div>
    </div>
    
    <div id="contenedorColaboradores" class="row mt-4" style="display: none;">
    <!-- Gráfico de Colaboradores -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-chart me-2"></i>Participación de Horas Completadas por Colaborador</h5></div>
            <div class="card-body" style="min-height: 400px;"><canvas id="graficoPorColaborador"></canvas></div>
        </div>
    </div>
    <!-- Tabla de Detalles de Colaboradores -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Detalle por Colaborador</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaDetalleColaboradores" class="table table-sm table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Colaborador</th>
                                <th class="text-end">H. Completadas</th>
                                <th class="text-end">% Cumplimiento Meta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Contenido se llenará con JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/reporte_planificacion.js"></script>