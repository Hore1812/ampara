<?php
$page_title = "Reporte de Progreso de Colaboradores";
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
$colaboradores = obtenerColaboradores(); 

?>

<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="mb-0"><i class="fas fa-chart-line me-2"></i><?php echo $page_title; ?></h3>
    </div>

    <!-- Filtros del Reporte -->
    <div class="card mb-3">
        <div class="card-body p-2">
            <form id="filtrosProgresoForm" class="row gx-2 gy-2 align-items-end">
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
                    <select id="idcolaborador" name="idcolaborador" class="form-select form-select-sm">
                        <option value="">Todos los Colaboradores</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?php echo $colaborador['ID']; ?>"><?php echo htmlspecialchars($colaborador['COLABORADOR']); ?></option>
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

    <!-- Contenedor para la Tabla de Doble Entrada -->
    <div id="contenedorDobleEntrada" class="card mb-3" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-th me-2"></i>Matriz de Cumplimiento Mensual</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaProgresoDobleEntrada" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <!-- Cabeceras de meses se insertarán dinámicamente -->
                    </thead>
                    <tbody>
                        <!-- Filas de colaboradores se insertarán dinámicamente -->
                    </tbody>
                </table>
            </div>
            <div class="mt-2 small">
                <strong>Leyenda:</strong>
                <span class="px-2 me-2" style="background-color: rgba(211, 245, 211, 0.7);"> > 90%</span>
                <span class="px-2 me-2" style="background-color: rgba(255, 248, 209, 0.7);">50% - 90%</span>
                <span class="px-2" style="background-color: rgba(255, 222, 222, 0.7);"> < 50%</span>
            </div>
        </div>
    </div>

    <!-- Contenedor para Gráfico y Tabla de Detalles -->
    <div id="contenedorResultadosDetalle" style="display: none;">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-percent me-2"></i>Cumplimiento de Meta de Horas (%)</h5>
            </div>
            <div id="contenedorCanvasCumplimiento" class="card-body" style="min-height: 400px;">
                <canvas id="graficoCumplimiento"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                 <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detalle de Horas por Colaborador</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaProgreso" class="table table-striped table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>Colaborador</th>
                                <th>Año</th>
                                <th>Mes</th>
                                <th>Meta de Horas</th>
                                <th>Horas Completadas</th>
                                <th>% Cumplimiento</th>
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
    let chartCumplimiento = null;
    let tablaProgreso = null;
    const mesesNombres = [null, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    Chart.register(ChartDataLabels);

    function formatearNumero(numero, decimales = 1) {
        return parseFloat(numero || 0).toLocaleString('es-ES', {
            minimumFractionDigits: decimales,
            maximumFractionDigits: decimales
        });
    }

    // --- Funciones para Gráfico y Tabla de Detalles (Versión Anterior) ---
    function renderizarGrafico(data) {
        const ctx = document.getElementById('graficoCumplimiento').getContext('2d');
        if (chartCumplimiento) chartCumplimiento.destroy();

        const paletaMeses = [
            'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)', 'rgba(83, 109, 254, 0.7)', 'rgba(46, 204, 113, 0.7)',
            'rgba(241, 196, 15, 0.7)', 'rgba(230, 126, 34, 0.7)', 'rgba(142, 68, 173, 0.7)'
        ];
        const getColorPorMes = (mes) => paletaMeses[(mes - 1) % paletaMeses.length];

        const labels = data.map(item => `${item.NombreColaborador} (${mesesNombres[item.Mes].substring(0,3)})`);
        const porcentajes = data.map(item => parseFloat(item.PorcentajeCumplimiento));
        const coloresBarras = data.map(item => getColorPorMes(item.Mes));

        chartCumplimiento = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                originalData: data,
                datasets: [{
                    label: '% Cumplimiento',
                    data: porcentajes,
                    backgroundColor: coloresBarras,
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: true,
                        anchor: 'center',
                        align: 'center',
                        formatter: function(value, context) {
                            const horas = context.chart.config.data.originalData[context.dataIndex].HorasCompletadas;
                            return `${formatearNumero(horas, 1)}h (${formatearNumero(value, 0)}%)`;
                        },
                        color: function(context) {
                            const bgColor = context.dataset.backgroundColor[context.dataIndex];
                            const rgb = bgColor.match(/\d+/g);
                            if (!rgb) return '#000';
                            const yiq = ((parseInt(rgb[0]) * 299) + (parseInt(rgb[1]) * 587) + (parseInt(rgb[2]) * 114)) / 1000;
                            return (yiq < 128) ? 'white' : 'black';
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                }
            }
        });
    }

    function renderizarTabla(data) {
        if (tablaProgreso) tablaProgreso.destroy();
        let tbody = $('#tablaProgreso tbody');
        tbody.empty();
        data.forEach(item => {
            tbody.append(`
                <tr>
                    <td>${item.NombreColaborador}</td>
                    <td>${item.Anio}</td>
                    <td>${mesesNombres[item.Mes]}</td>
                    <td>${formatearNumero(item.HorasMeta, 0)}</td>
                    <td>${formatearNumero(item.HorasCompletadas)}</td>
                    <td>${formatearNumero(item.PorcentajeCumplimiento, 1)}%</td>
                </tr>
            `);
        });
        tablaProgreso = $('#tablaProgreso').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
            responsive: true,
            order: []
        });
    }

    // --- Función para Tabla de Doble Entrada ---
    function getCellColor(porcentaje) {
        if (porcentaje >= 90) return 'rgba(211, 245, 211, 0.7)';
        if (porcentaje >= 50) return 'rgba(255, 248, 209, 0.7)';
        return 'rgba(255, 222, 222, 0.7)';
    }

    function renderizarTablaDobleEntrada(data, meses) {
        const tabla = $('#tablaProgresoDobleEntrada');
        const thead = tabla.find('thead');
        const tbody = tabla.find('tbody');
        thead.empty();
        tbody.empty();

        let headerRow = '<tr><th class="bg-dark text-white">Colaborador</th><th class="bg-dark text-white">Meta</th>';
        meses.forEach(mes => {
            headerRow += `<th class="bg-dark text-white text-center">${mesesNombres[mes]}</th>`;
        });
        headerRow += '</tr>';
        thead.append(headerRow);

        data.forEach(colaborador => {
            let bodyRow = `<tr><td><strong>${colaborador.NombreColaborador}</strong></td><td class="text-center">${colaborador.HorasMeta}</td>`;
            meses.forEach(mes => {
                const mesData = colaborador.datos_mes[mes];
                if (mesData) {
                    const porcentaje = parseFloat(mesData.PorcentajeCumplimiento);
                    const color = getCellColor(porcentaje);
                    bodyRow += `<td style="background-color: ${color};" class="text-center">`;
                    bodyRow += `<span>${formatearNumero(mesData.HorasCompletadas)}h</span><br>`;
                    bodyRow += `<small>(${formatearNumero(porcentaje)}%)</small>`;
                    bodyRow += `</td>`;
                } else {
                    bodyRow += '<td></td>';
                }
            });
            bodyRow += '</tr>';
            tbody.append(bodyRow);
        });
    }
    
    function generarReporte() {
        $('#spinnerCarga').show();
        $('#contenedorResultadosDetalle, #contenedorDobleEntrada, #noDatos, #errorReporte').hide();

        $.ajax({
            url: 'ajax/obtener_progreso_colaboradores.php',
            method: 'POST',
            data: $('#filtrosProgresoForm').serialize(),
            dataType: 'json',
            success: function(response) {
                $('#spinnerCarga').hide();
                if (response.success && response.data && response.data.length > 0) {
                    // El backend ahora devuelve los datos pivotados y los meses
                    // Necesitamos "des-pivotar" los datos para el gráfico y la tabla de detalles
                    let flatData = [];
                    response.data.forEach(colaborador => {
                        Object.keys(colaborador.datos_mes).forEach(mes => {
                            flatData.push({
                                NombreColaborador: colaborador.NombreColaborador,
                                Anio: $('#anio').val(), // Tomamos el año del filtro
                                Mes: mes,
                                HorasMeta: colaborador.HorasMeta,
                                HorasCompletadas: colaborador.datos_mes[mes].HorasCompletadas,
                                PorcentajeCumplimiento: colaborador.datos_mes[mes].PorcentajeCumplimiento
                            });
                        });
                    });

                    $('#contenedorResultadosDetalle').show();
                    $('#contenedorDobleEntrada').show();
                    
                    renderizarGrafico(flatData);
                    renderizarTabla(flatData);
                    renderizarTablaDobleEntrada(response.data, response.meses);

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

    $('#btnGenerarReporte').click(generarReporte);
    generarReporte();
});
</script>
