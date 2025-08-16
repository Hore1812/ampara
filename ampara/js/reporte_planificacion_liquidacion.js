window.onload = function() {
    let chartBarras = null;
    let chartColaboradores = null;
    let tablaDobleEntrada = null;
    let tablaColaboradores = null;
    let chartDobleEntrada = null;

    const chartColors = [
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(201, 203, 207, 0.8)'
    ];

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
        let headerRow = '<tr class="text-center"><th>Contrato</th><th>Horas Planificadas</th>';
        estadosArray.forEach((estado, index) => {
            colorMapping[estado] = chartColors[index % chartColors.length];
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
                bodyRow += `<td style="background-color: ${color};">${horas}h <span style="color: rgba(0,0,0,0.5);">(${porcentaje.toFixed(2)}%)</span></td>`;
              
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
                            <td style="background-color: #012060; color: white;"><strong>Total</strong></td>
                            <td style="background-color: #012060; color: white;"><strong>${totalHorasPlanificadas.toFixed(2)}h</strong></td>`;
        estadosArray.forEach((estado, index) => {
            const totalHoras = totales[estado] || 0;
            const totalPorcentaje = (totalHoras / totalHorasPlanificadas * 100).toFixed(2);
            const color = chartColors[index % chartColors.length];
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
        estadosArray.forEach((estado, index) => {
            colorMapping[estado] = chartColors[index % chartColors.length];
            datasets.push({
                label: estado,
                data: labels.map(contrato => contratos[contrato].estados[estado] || 0),
                backgroundColor: colorMapping[estado],
            });
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
                    title: {
                        display: true,
                        text: 'Horas Planificadas vs. Liquidadas por Contrato',
                        font: {
                            size: 18,
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.x !== null) {
                                    const total = context.chart.data.datasets.reduce((acc, dataset) => acc + dataset.data[context.dataIndex], 0);
                                    const percentage = total > 0 ? (context.parsed.x / total * 100).toFixed(2) : 0;
                                    label += `${context.parsed.x.toFixed(2)}h (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        display: function(context) {
                            return context.dataset.data[context.dataIndex] > 0;
                        },
                        anchor: 'center',
                        align: 'center',
                        formatter: (value, context) => {
                            const dataset = context.chart.data.datasets[context.datasetIndex];
                            if (dataset.label === 'Horas Planificadas') {
                                return `Plan: ${value.toFixed(2)}h`;
                            }
                            const total = dataset.data.reduce((acc, cur) => acc + cur, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(2) : 0;
                            return `${value.toFixed(2)}h\n(${percentage}%)`;
                        },
                        color: 'black',
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
                    backgroundColor: chartColors[0],
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Horas Asignadas por Colaborador',
                        font: {
                            size: 18,
                        }
                    },
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.x !== null) {
                                    const total = context.chart.data.datasets[0].data.reduce((acc, value) => acc + value, 0);
                                    const percentage = total > 0 ? (context.parsed.x / total * 100).toFixed(2) : 0;
                                    label += `${context.parsed.x.toFixed(2)}h (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'start',
                        formatter: (value, context) => {
                            let sum = 0;
                            let dataArr = context.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                            return `${value.toFixed(2)}h\n(${percentage}%)`;
                        },
                        color: 'black',
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
        // MODIFICADO: La tarjeta de Horas Liquidadas ahora muestra solo las horas completadas.
        $('#totalHorasLiquidadas').text(totalHorasCompletadas.toFixed(2) + 'h');
        $('#porcentajeGeneral').text(porcentajeGeneral + '%');
        $('#porcentajeGeneralProgress').css('width', porcentajeGeneral + '%').attr('aria-valuenow', porcentajeGeneral);
        $('#porcentajeCompletado').text(porcentajeCompletado + '%');
        $('#porcentajeCompletadoProgress').css('width', porcentajeCompletado + '%').attr('aria-valuenow', porcentajeCompletado);
    }

   function getCellColor(percentage) {
        const alpha = percentage / 200;
         return `rgba(100, 220, 220, ${alpha})`;
    }


    function isColorLight(color) {
        const rgb = color.match(/\d+/g);
        if (!rgb) return false;
        const r = parseInt(rgb[0]);
        const g = parseInt(rgb[1]);
        const b = parseInt(rgb[2]);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness > 155;
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
                    backgroundColor: chartColors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Horas por Estado',
                        font: {
                            size: 18,
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    const total = context.chart.data.datasets[0].data.reduce((acc, value) => acc + value, 0);
                                    const percentage = total > 0 ? (context.parsed / total * 100).toFixed(2) : 0;
                                    label += `${context.parsed.toFixed(2)}h (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                            return `${percentage}`;
                        },
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    }

    document.getElementById('btnGenerarReporte').addEventListener('click', generarReporte);
    generarReporte(); // Carga inicial
};
