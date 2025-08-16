<?php
$page_title = "Test Gráfico en Modal";
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Prueba de Gráfico en Modal</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTest">
      Abrir Modal con Gráfico
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="modalTest" tabindex="-1" aria-labelledby="modalTestLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTestLabel">Gráfico de Prueba</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <canvas id="myChartTest"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
require_once 'includes/footer.php';
?>
<!-- Chart.js y plugin datalabels -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalTest = document.getElementById('modalTest');
    let myChartTest = null;
    
    // Registrar el plugin globalmente
    Chart.register(ChartDataLabels);

    modalTest.addEventListener('shown.bs.modal', function () {
        console.log('Modal mostrado, renderizando gráfico de prueba');
        if (myChartTest) {
            myChartTest.destroy();
        }
        const ctx = document.getElementById('myChartTest').getContext('2d');
        myChartTest = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Rojo', 'Azul', 'Amarillo', 'Verde', 'Morado', 'Naranja'],
                datasets: [{
                    label: '# de Votos',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    datalabels: {
                        display: true,
                        anchor: 'end',
                        align: 'top',
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value*100 / sum).toFixed(2) + '%';
                            return `${value} (${percentage})`;
                        },
                        font: {
                            weight: 'bold',
                            size: 16
                        }
                    }
                }
            }
        });
    });
});
</script>
