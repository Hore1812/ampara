<?php
require_once 'includes/header.php';
require_once 'funciones.php';

$filtros = [
    'anio' => $_GET['anio'] ?? null,
    'mes' => $_GET['mes'] ?? null,
    'cliente' => $_GET['cliente'] ?? null,
    'lider' => $_GET['lider'] ?? null,
    'estado' => $_GET['estado'] ?? null
];

$liquidaciones = obtenerLiquidaciones($filtros);
$colaboradores = obtenerColaboradores();
$clientes = obtenerClientes();
$contratos = obtenerContratosActivosParaSelect();
$lideres = obtenerLideres();
$estadisticas = obtenerEstadisticasHoras($filtros);
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">HISTORICO DE LIQUIDACIONES</h1>
            <?php if (isset($_SESSION['tipo_usuario']) && ($_SESSION['tipo_usuario'] == 1 || $_SESSION['tipo_usuario'] == 2)): ?>
    <a href="registrar_liquidacion.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Liquidación
    </a>
<?php endif; ?>
    </div>
   <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <?php if (($estadisticas['programado'] ?? 0) > 0): ?>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body estadisticas-card-body">
                    <h3 class="card-title">Programado</h3>
                    <h2 class="card-text"><?= $estadisticas['programado'] ?></h2>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($estadisticas['en_revision'] ?? 0) > 0): ?>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body estadisticas-card-body">
                    <h3 class="card-title">En revisión</h3>
                    <h2 class="card-text"><?= $estadisticas['en_revision'] ?></h2>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($estadisticas['en_proceso'] ?? 0) > 0): ?>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body estadisticas-card-body">
                    <h3 class="card-title">En proceso</h3>
                    <h2 class="card-text"><?= $estadisticas['en_proceso'] ?></h2>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($estadisticas['completo'] ?? 0) > 0): ?>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body estadisticas-card-body">
                    <h3 class="card-title">Completo</h3>
                    <h2 class="card-text"><?= $estadisticas['completo'] ?></h2>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php 
        // La tarjeta "Total Horas" se muestra siempre que haya un total, 
        // o puedes decidir mostrarla solo si alguna de las otras categorías tiene horas.
        // Por ahora, se mostrará si el total es mayor a cero.
        if (($estadisticas['total'] ?? 0) > 0): 
        ?>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body estadisticas-card-body">
                    <h3 class="card-title">Total Horas</h3>
                    <h2 class="card-text"><?= $estadisticas['total'] ?></h2>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filtrosForm" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label for="anio" class="form-label">Año</label>
                    <select id="anio" name="anio" class="form-select">
                        <option value="">Todos</option>
                        <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                            <option value="<?= $i ?>" <?= ($filtros['anio'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="mes" class="form-label">Mes</label>
                    <select id="mes" name="mes" class="form-select">
                        <option value="">Todos</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= ($filtros['mes'] == $i) ? 'selected' : '' ?>>
                                <?= DateTime::createFromFormat('!m', $i)->format('F') ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cliente" class="form-label">Cliente</label>
                    <select id="cliente" name="cliente" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['idcliente'] ?>" <?= ($filtros['cliente'] == $cliente['idcliente']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombrecomercial']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="lider" class="form-label">Líder</label>
                    <select id="lider" name="lider" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($lideres as $lider): ?>
                            <option value="<?= $lider['idempleado'] ?>" <?= ($filtros['lider'] == $lider['idempleado']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lider['nombrecorto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Programado" <?= ($filtros['estado'] == 'Programado') ? 'selected' : '' ?>>Programado</option>
                        <option value="En revisión" <?= ($filtros['estado'] == 'En revisión') ? 'selected' : '' ?>>En revisión</option>
                        <option value="En proceso" <?= ($filtros['estado'] == 'En proceso') ? 'selected' : '' ?>>En proceso</option>
                        <option value="Completo" <?= ($filtros['estado'] == 'Completo') ? 'selected' : '' ?>>Completo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="colaborador" class="form-label">Colaborador</label>
                    <select id="colaborador" name="colaborador" class="form-select">
                        <option value="">Seleccionar</option>
                        <?php foreach ($colaboradores as $colab): ?>
                            <option value="<?= $colab['ID'] ?>"><?= htmlspecialchars($colab['COLABORADOR']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <!-- <button type="button" id="limpiarFiltros" class="btn btn-orange"><a id="limpiarf" style="text-decoration:none;color:white;"href="index.php">Limpiar</a></button> -->
                    <button type="button" id="limpiarFiltros" class="btn btn-orange">Limpiar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla de liquidaciones -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaLiquidaciones" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FECHA</th>
                            <th>CLIENTE</th>
                            <th>TEMA</th>
                            <th>ASUNTO</th>
                            <th>MOTIVO</th>
                            <th>LÍDER</th>
                            <th>ENCARGADO</th>
                            <th>ESTADO</th>
                            <th>HORAS</th>
                            <th>TIPO HORA</th>
                            <th>OPCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liquidaciones as $liq): ?>
                            <tr>
                                <td><?= $liq['ID'] ?></td>
                                <td><?= date('d/m/Y', strtotime($liq['FECHA'])) ?></td>
                                <td><?= htmlspecialchars($liq['CLIENTE']) ?></td>
                                <td><?= htmlspecialchars($liq['TEMA']) ?></td>
                                <td><?= htmlspecialchars($liq['ASUNTO']) ?></td>
                                <td><?= htmlspecialchars($liq['MOTIVO']) ?></td>
                                <td><?= htmlspecialchars($liq['LIDER']) ?></td>
                                <td><?= htmlspecialchars($liq['ENCARGADO']) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $liq['ESTADO'] == 'Programado' ? 'bg-info' : '' ?>
                                        <?= $liq['ESTADO'] == 'En revisión' ? 'bg-warning' : '' ?>
                                        <?= $liq['ESTADO'] == 'En proceso' ? 'bg-primary' : '' ?>
                                        <?= $liq['ESTADO'] == 'Completo' ? 'bg-success' : '' ?>">
                                        <?= $liq['ESTADO'] ?>
                                    </span>
                                </td>
                                <td><?= $liq['HORAS'] ?></td>
                                <td><?= htmlspecialchars($liq['TIPOHORA']) ?></td>
                                <td>
<!-- inicio -->
 <div class="d-flex flex-column flex-md-row gap-1 justify-content-center">
    <?php // Botón Ver Colaboradores (Siempre visible si hay un tipo de usuario) ?>
    <?php if ($_SESSION['tipo_usuario']): ?>
        <button class="btn btn-sm btn-primary ver-colaboradores" data-id="<?= $liq['ID'] ?>" 
                data-bs-toggle="tooltip" data-bs-placement="top" title="Ver Colaboradores"
                <?php if ($liq['ESTADO'] !== 'Completo') echo 'disabled'; ?>>
            <i class="fas fa-users"></i>
        </button>
    <?php endif; ?>

    <?php // Botón Editar Liquidación ?>
    <?php if ($_SESSION['tipo_usuario'] == 1 || ($_SESSION['tipo_usuario'] == 2 &&$_SESSION['idemp'] == $liq['ID_LIDER'])): ?>
        <a href="editar_liquidacion.php?id=<?= $liq['ID'] ?>" class="btn btn-sm btn-secondary"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Liquidación">
            <i class="fas fa-edit"></i>
        </a>
    <?php endif; ?>

    <?php // Botón Eliminar Liquidación ?>
    <?php if ($_SESSION['tipo_usuario'] == 1): ?>
        <button class="btn btn-sm btn-orange eliminar-liquidacion" data-id="<?= $liq['ID'] ?>"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar Liquidación">
            <i class="fas fa-trash"></i>
        </button>
    <?php endif; ?>
</div>

<!-- fin -->

                            </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Colaboradores -->
<div class="modal fade" id="modalColaboradores" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Colaboradores de la Liquidación <span id="tituloLiquidacion"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>COLABORADOR</th>
                            <th>PORCENTAJE</th>
                            <th>CÁLCULO</th>
                            <th>HORAS</th>
                            <th>COMENTARIO</th>
                        </tr>
                    </thead>
                    <tbody id="tablaColaboradores">
                        <!-- Datos se cargan via AJAX -->
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">TOTAL</td>
                            <td id="totalPorcentaje">0%</td>
                            <td id="totalCalculo">0</td>
                            <td></td> <!-- Empty cell for Horas column -->
                            <td></td> <!-- Empty cell for Comentario column -->
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historico Colaborador -->
<div class="modal fade" id="modalHistoricoColaborador" tabindex="-1" aria-hidden="true">
    <!-- <div class="modal-dialog modal-xl"> -->
        <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">HISTORICO DE COLABORADOR <span id="tituloColaborador"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filtrosColaboradorForm" class="row g-3 mb-3 align-items-end">
    <div class="col-md-3">
        <label for="anioColab" class="form-label">Año</label>
        <select id="anioColab" name="anio" class="form-select">
            <option value="">Todos</option>
            <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="mesColab" class="form-label">Mes</label>
        <select id="mesColab" name="mes" class="form-select">
            <option value="">Todos</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>">
                    <?= DateTime::createFromFormat('!m', $i)->format('F') ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
   <div class="col-md-3">
    <label for="clienteIdcon" class="form-label">Contrato Cliente</label>
        <select id="clienteIdcon" name="clienteIdcon" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($contratos as $contrato): ?>
           <option value="<?= $contrato['idcontratocli'];?>">
            <?= htmlspecialchars($contrato['descripcion_completa_contrato']) ?>
            </option>
            
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>
                
                <div class="table-responsive">
                    <table id="tablaHistoricoColaborador" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>FECHA</th>
                                <th>CLIENTE</th>
                                <th>TEMA</th>
                                <th>ASUNTO</th>
                                <th>MOTIVO</th>
                                <th>LÍDER</th>
                                <th>ENCARGADO</th>
                                <th>ACUMULADO</th>
                                <th>HORAS</th>
                                <th>TIPO HORA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Datos se cargan via AJAX -->
                        </tbody>
                        <tfoot>
    <tr>
        <th colspan="8" style="text-align:right; font-weight:bold;">TOTALES:</th>
        <th id="totalAcumuladoHistorico" style="text-align:center; font-weight:bold;"></th>
        <th id="totalHorasHistorico" style="text-align:center; font-weight:bold;"></th>
        <th></th> <!-- Celda vacía para alinear con la columna de Tipo Hora -->
    </tr>
    <tr>
        <th colspan="11" id="conteoRegistrosHistorico" style="text-align:left;">Mostrando 0 de 0 registros</th>
    </tr>
</tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/modales.php'; ?>
<?php require_once 'includes/footer.php'; ?>