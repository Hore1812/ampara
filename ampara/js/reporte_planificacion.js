document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filtrosReporteGeneralForm');
    const spinner = document.getElementById('spinnerCargaReporte');
    const errorDiv = document.getElementById('errorReporte');
    const noDatosDiv = document.getElementById('noDatosReporte');
    const kpisContainer = document.getElementById('kpisReporteGeneral');
    const tablaContainer = document.getElementById('contenedorTablaDobleEntrada');
    const graficoContainer = document.getElementById('contenedorGrafico');
    const colaboradoresContainer = document.getElementById('contenedorColaboradores');

    let graficoClientes = null;
    let graficoColaboradores = null;
    let isLoading = false;

    function fetchData() {
        if (isLoading) return;
        isLoading = true;

        spinner.style.display = 'block';
        errorDiv.style.display = 'none';
        noDatosDiv.style.display = 'none';
        kpisContainer.style.display = 'none';
        tablaContainer.style.display = 'none';
        graficoContainer.style.display = 'none';
        colaboradoresContainer.style.display = 'none';

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        // Fetch para el reporte general de planificación
        fetch('ajax/obtener_reporte_general_planificacion.php', {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.por_cliente && data.data.por_cliente.labels.length > 0) {
                actualizarKPIs(data.data.kpis);
                actualizarTablaDobleEntrada(data.data.por_cliente, data.data.lista_estados);
                actualizarGraficoClientes(data.data.por_cliente);

                kpisContainer.style.display = 'flex';
                tablaContainer.style.display = 'block';
                graficoContainer.style.display = 'block';
            } else {
                noDatosDiv.style.display = 'block';
                noDatosDiv.textContent = data.message || 'No se encontraron datos para los filtros seleccionados.';
            }
        })
        .catch(error => {
            console.error('Error en fetch de reporte general:', error);
            errorDiv.textContent = 'Error de conexión al cargar el reporte general. Verifique la consola para más detalles.';
            errorDiv.style.display = 'block';
        });

        // Fetch para el reporte de colaboradores
        fetch('ajax/obtener_reporte_colaboradores.php', {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                actualizarSeccionColaboradores(data.data);
                colaboradoresContainer.style.display = 'flex';
            } else {
                // No mostrar error si simplemente no hay datos, pero sí en la consola.
                console.log(data.message || 'No se encontraron datos de colaboradores.');
            }
            spinner.style.display = 'none';
        })
        .catch(error => {
            console.error('Error en fetch de reporte de colaboradores:', error);
            // Podríamos mostrar un error específico para esta sección si fuera necesario
            spinner.style.display = 'none';
        })
        .finally(() => {
            isLoading = false;
        });
    }

    function actualizarKPIs(kpis) {
        document.getElementById('kpiTotalHorasPlanificadas').textContent = parseFloat(kpis.total_planificadas).toFixed(2);
        document.getElementById('kpiTotalHorasLiquidadasTodosEstados').textContent = parseFloat(kpis.total_liquidadas_todos_estados).toFixed(2);
        document.getElementById('kpiTotalHorasLiquidadasCompletas').textContent = parseFloat(kpis.total_liquidadas_completas).toFixed(2);
        document.getElementById('kpiPorcentajeCumplimiento').textContent = parseFloat(kpis.porcentaje_cumplimiento).toFixed(2) + '%';
        document.getElementById('kpiPorcentajeCumplimientoTodosEstados').textContent = parseFloat(kpis.porcentaje_cumplimiento_todos_estados).toFixed(2) + '%';
    }

    function actualizarTablaDobleEntrada(data, estados) {
        const tablaHead = document.querySelector('#tablaDobleEntradaClientes thead');
        const tablaBody = document.querySelector('#tablaDobleEntradaClientes tbody');
        const tablaFoot = document.querySelector('#tablaDobleEntradaClientes tfoot');

        // Limpiar
        tablaHead.innerHTML = '';
        tablaBody.innerHTML = '';
        tablaFoot.innerHTML = '';

        // Encabezado
        let headerRow = '<tr><th>Cliente</th>';
        estados.forEach(estado => headerRow += `<th class="text-center">${estado}</th>`);
        headerRow += '<th class="text-center table-dark">Total Liquidado</th><th class="text-center table-dark">Total Planificado</th></tr>';
        tablaHead.innerHTML = headerRow;

        // Cuerpo
        data.labels.forEach((cliente, index) => {
            let bodyRow = `<tr><td>${cliente}</td>`;
            let totalLiquidadoCliente = 0;
            estados.forEach(estado => {
                let horas = 0;
                data.datasets.forEach(dataset => {
                    if (dataset.label === 'Liq. - ' + estado) {
                        horas = dataset.data[index] || 0;
                        totalLiquidadoCliente += horas;
                    }
                });
                bodyRow += `<td class="text-end" style="background-color: ${horas > 0 ? 'rgba(75, 192, 192, 0.1)' : 'transparent'};">${parseFloat(horas).toFixed(2)}</td>`;
            });
            const horasPlanificadasCliente = data.datasets.find(d => d.label === 'Horas Planificadas').data[index] || 0;
            bodyRow += `<td class="text-end table-secondary"><strong>${parseFloat(totalLiquidadoCliente).toFixed(2)}</strong></td>`;
            bodyRow += `<td class="text-end table-secondary"><strong>${parseFloat(horasPlanificadasCliente).toFixed(2)}</strong></td>`;
            bodyRow += '</tr>';
            tablaBody.innerHTML += bodyRow;
        });
        
        // Pie de tabla (Totales)
        let footerRow = '<tr class="table-dark"><td><strong>TOTALES</strong></td>';
        const totalesPorEstado = {};
        let granTotalLiquidado = 0;
        let granTotalPlanificado = 0;

        estados.forEach(estado => {
            totalesPorEstado[estado] = 0;
            data.datasets.forEach(dataset => {
                if (dataset.label === 'Liq. - ' + estado) {
                    totalesPorEstado[estado] = dataset.data.reduce((a, b) => a + b, 0);
                }
            });
            granTotalLiquidado += totalesPorEstado[estado];
            footerRow += `<td class="text-end"><strong>${parseFloat(totalesPorEstado[estado]).toFixed(2)}</strong></td>`;
        });
        
        const planificadasDataset = data.datasets.find(d => d.label === 'Horas Planificadas');
        if(planificadasDataset) {
            granTotalPlanificado = planificadasDataset.data.reduce((a, b) => a + b, 0);
        }

        footerRow += `<td class="text-end"><strong>${parseFloat(granTotalLiquidado).toFixed(2)}</strong></td>`;
        footerRow += `<td class="text-end"><strong>${parseFloat(granTotalPlanificado).toFixed(2)}</strong></td>`;
        footerRow += '</tr>';
        tablaFoot.innerHTML = footerRow;
    }

    function actualizarGraficoClientes(data) {
        const ctx = document.getElementById('graficoPorCliente').getContext('2d');
        if (graficoClientes) {
            graficoClientes.destroy();
        }
        graficoClientes = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Horas' } }
                },
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Horas Planificadas vs. Horas Liquidadas por Cliente' }
                }
            }
        });
    }

    function actualizarSeccionColaboradores(data) {
        // Actualizar el gráfico de colaboradores
        const labels = data.map(item => item.NombreColaborador);
        const horasCompletadas = data.map(item => parseFloat(item.HorasCompletadas));

        const ctx = document.getElementById('graficoPorColaborador').getContext('2d');
        if (graficoColaboradores) {
            graficoColaboradores.destroy();
        }
        graficoColaboradores = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Horas Completadas',
                    data: horasCompletadas,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0
                },
                scales: {
                    x: { beginAtZero: true, title: { display: true, text: 'Horas' } }
                },
                plugins: {
                    legend: { display: false },
                }
            }
        });

        // Actualizar la tabla de detalles de colaboradores
        const tablaBody = document.querySelector('#tablaDetalleColaboradores tbody');
        tablaBody.innerHTML = '';
        data.forEach(item => {
            const porcentaje = parseFloat(item.PorcentajeCumplimiento).toFixed(2);
            const fila = `
                <tr>
                    <td>${item.NombreColaborador}</td>
                    <td class="text-end">${parseFloat(item.HorasCompletadas).toFixed(2)}</td>
                    <td class="text-end">${porcentaje}%</td>
                </tr>
            `;
            tablaBody.innerHTML += fila;
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchData();
    });

    // Carga inicial
    // fetchData();
});