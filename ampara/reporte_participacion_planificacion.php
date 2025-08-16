<?php
$page_title = "Reporte de Participación por Planificación";
require_once 'includes/header.php';
require_once 'funciones.php';

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
$participantes_activos = obtenerColaboradores();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-chart-bar me-2"></i><?php echo $page_title; ?></h1>
        <a href="reporte_planificacion_liquidacion.php" class="btn btn-info">
            <i class="fas fa-file-invoice-dollar me-2"></i>Ir a Reporte General
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-filter me-1"></i>Filtros del Reporte</div>
        <div class="card-body">
            <form id="filtrosReporteParticipacionForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="anio_participacion" class="form-label">Año</label>
                    <select id="anio_participacion" name="anio" class="form-select">
                        <?php foreach ($anios_disponibles as $anio): ?>
                            <option value="<?php echo $anio; ?>" <?php echo ($anio == $current_year) ? 'selected' : ''; ?>><?php echo $anio; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="mes_participacion" class="form-label">Mes</label>
                    <select id="mes_participacion" name="mes" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses_espanol as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cliente_participacion" class="form-label">Cliente</label>
                    <select id="cliente_participacion" name="idcliente" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($clientes_activos as $cliente): ?>
                            <option value="<?php echo $cliente['idcliente']; ?>"><?php echo htmlspecialchars($cliente['nombrecomercial']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="col-md-3">
                    <label for="participante_filtro" class="form-label">Participante</label>
                    <select id="participante_filtro" name="idparticipante" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($participantes_activos as $participante): ?>
                            <option value="<?php echo $participante['ID']; ?>"><?php echo htmlspecialchars($participante['COLABORADOR']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="button" id="btnGenerarReporteParticipacion" class="btn btn-primary px-5">
                        <i class="fas fa-sync-alt me-2"></i>Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="spinnerCargaReporteParticipacion" class="text-center my-5" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-2">Cargando datos del reporte...</p>
    </div>
    <div id="errorReporteParticipacion" class="alert alert-danger" style="display: none;"></div>
    <div id="noDatosReporteParticipacion" class="alert alert-warning text-center" style="display: none;">
        <i class="fas fa-info-circle me-2"></i>No se encontraron datos para los filtros seleccionados.
    </div>

    <div id="graficosReporteParticipacion" class="row" style="display: none;">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Distribución de Horas por Participante</h5></div>
                <div class="card-body" style="height: 500px;">
                    <canvas id="graficoParticipacion"></canvas>
                    <p class="text-muted small text-center mt-2 no-data-participacion-msg" style="display:none;">No se encontraron datos para los filtros seleccionados.</p>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted small mt-1 text-center">Reporte basado en la vista: <code>vista_planificacion_vs_participantes_completado</code>.</p>
</div>

<?php
require_once 'includes/modales.php';
require_once 'includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
$(document).ready(function() {
    let chartParticipacion = null;
    const meses_espanol = { '1': 'Enero', '2': 'Febrero', '3': 'Marzo', '4': 'Abril', '5': 'Mayo', '6': 'Junio', '7': 'Julio', '8': 'Agosto', '9': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre' };

    Chart.register(ChartDataLabels);
    Chart.defaults.font.family = "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";

    function destruirGraficoExistente() {
        if (chartParticipacion) {
            chartParticipacion.destroy();
            chartParticipacion = null;
        }
    }

    function formatearNumero(numero, decimales = 2) {
        return parseFloat(numero || 0).toLocaleString('es-ES', { minimumFractionDigits: decimales, maximumFractionDigits: decimales });
    }

    function renderizarGraficoConsolidado(datos, filtros) {
        destruirGraficoExistente();
        const ctx = document.getElementById('graficoParticipacion').getContext('2d');
        $('.no-data-participacion-msg').hide();

        const data_consolidada = {
            horas_planificadas: 0,
            total_horas_completadas: 0,
            participantes: {}
        };

        datos.forEach(plan => {
            data_consolidada.horas_planificadas += plan.horas_planificadas;
            data_consolidada.total_horas_completadas += plan.total_horas_completadas;
            plan.participantes.forEach(p => {
                if(!data_consolidada.participantes[p.id]){
                    data_consolidada.participantes[p.id] = { id: p.id, nombre: p.nombre, horas_completadas: 0 };
                }
                data_consolidada.participantes[p.id].horas_completadas += p.horas_completadas;
            });
        });
        
        const participantes_ordenados = Object.values(data_consolidada.participantes).sort((a, b) => b.horas_completadas - a.horas_completadas);
        const totalCompletadasGeneral = participantes_ordenados.reduce((sum, p) => sum + p.horas_completadas, 0);

        if (participantes_ordenados.length === 0) {
            $('#graficoParticipacion').parent().find('.no-data-participacion-msg').text('No hay datos de participación para esta selección.').show();
            return;
        }

        const labels = participantes_ordenados.map(p => p.nombre);
        const horasCompletadasData = participantes_ordenados.map(p => p.horas_completadas);
        
        const cliente_texto = filtros.idcliente ? $('#cliente_participacion option:selected').text() : 'Todos';
        const mes_texto = filtros.mes ? meses_espanol[filtros.mes] : 'Todos';
        const cumplimiento = data_consolidada.horas_planificadas > 0 ? (totalCompletadasGeneral / data_consolidada.horas_planificadas) * 100 : 0;
        const titleText = `Reporte Consolidado (${cliente_texto} - ${mes_texto} ${filtros.anio}) | Plan: ${formatearNumero(data_consolidada.horas_planificadas, 1)}h | Comp: ${formatearNumero(totalCompletadasGeneral, 1)}h (${cumplimiento.toFixed(1)}%)`;
        
        const backgroundColors = labels.map((_, i) => `rgba(${54 + (i * 35) % 200}, ${162 - (i * 25) % 150}, ${235 - (i * 20) % 200}, 0.7)`);
        const borderColors = labels.map((_, i) => `rgba(${54 + (i * 35) % 200}, ${162 - (i * 25) % 150}, ${235 - (i * 20) % 200}, 1)`);

        chartParticipacion = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Horas Completadas',
                    data: horasCompletadasData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                scales: { x: { beginAtZero: true, title: { display: true, text: 'Horas' } } },
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: titleText, font: { size: 16 } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                let value = context.parsed.x;
                                label += formatearNumero(value, 1) + 'h';
                                if (totalCompletadasGeneral > 0) {
                                    const porcentaje = (value / totalCompletadasGeneral) * 100;
                                    label += ` (${porcentaje.toFixed(1)}% del total)`;
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold' },
                        formatter: (value, context) => {
                            if (totalCompletadasGeneral > 0 && value > 0) {
                                const porcentaje = (value / totalCompletadasGeneral) * 100;
                                return `${formatearNumero(value, 1)}h (${porcentaje.toFixed(1)}%)`;
                            }
                            return value > 0 ? formatearNumero(value, 1) + 'h' : '';
                        }
                    }
                }
            }
        });
    }

    function generarReporte() {
        $('#spinnerCargaReporteParticipacion').show();
        $('#graficosReporteParticipacion, #noDatosReporteParticipacion, #errorReporteParticipacion').hide();
        destruirGraficoExistente();

        const filtros = {
            anio: $('#anio_participacion').val(),
            mes: $('#mes_participacion').val(),
            idcliente: $('#cliente_participacion').val(),
            idparticipante: $('#participante_filtro').val()
        };

        $.ajax({
            url: 'ajax/obtener_reporte_participacion.php',
            method: 'POST',
            data: filtros,
            dataType: 'json',
            success: function(response) {
                $('#spinnerCargaReporteParticipacion').hide();
                if (response.success && response.data && response.data.length > 0) {
                    $('#graficosReporteParticipacion').show();
                    renderizarGraficoConsolidado(response.data, filtros);
                } else {
                    $('#noDatosReporteParticipacion').text(response.message || 'No se encontraron datos para los filtros seleccionados.').show();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#spinnerCargaReporteParticipacion').hide();
                console.error("Error AJAX: ", textStatus, errorThrown, jqXHR.responseText);
                $('#errorReporteParticipacion').text('Error de conexión o en el servidor al generar el reporte.').show();
            }
        });
    }

    $('#btnGenerarReporteParticipacion').on('click', generarReporte);
    generarReporte();
});
</script>