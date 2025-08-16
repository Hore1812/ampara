<?php
require_once 'includes/header.php';
require_once 'funciones.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$idLiquidacion = $_GET['id'];
$liquidacion = obtenerLiquidacion($idLiquidacion);

if (!$liquidacion) {
    header('Location: liquidaciones.php');
    exit;
}

$tiposHora = obtenerTiposHora();
$temas = obtenerTemas();
$colaboradoresLiquidacion = obtenerColaboradoresPorLiquidacion($idLiquidacion);
$colaboradoresDisponibles = obtenerColaboradores();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">EDITAR REGISTRO DE LIQUIDACIONES</h1>
        <a href="liquidaciones.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form id="formLiquidacion" method="POST" action="actualizar_liquidacion.php">
                <input type="hidden" name="idliquidacion" value="<?= $idLiquidacion ?>">
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="tipohora" class="form-label">Tipo de Hora</label>
                        <select id="tipohora" name="tipohora" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($tiposHora as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['tipohora']) ?>" 
                                    <?= ($tipo['tipohora'] == $liquidacion['tipohora']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['tipohora']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cliente" class="form-label">Cliente</label>
                        <select id="cliente" name="cliente" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <?php 
                            $clientesTipoHora = obtenerClientesPorTipoHora($liquidacion['tipohora']);
                            foreach ($clientesTipoHora as $cliente): 
                                $parts = explode(' – ', $cliente['CLIENTE']);
                                $idCliente = $parts[0];
                                $nombreCliente = $parts[1] ?? '';
                            ?>
                                <option value="<?= $cliente['idcontratocli'] ?>" 
                                    <?= ($cliente['idcontratocli'] == $liquidacion['idcontratocli']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nombreCliente) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="lider" class="form-label">Líder</label>
                        <input type="text" id="lider" class="form-control" readonly 
                               value="<?= htmlspecialchars(obtenerLiderPorContrato($liquidacion['idcontratocli'])['nombrecorto'] ?? '') ?>">
                        <input type="hidden" id="idlider" name="lider" 
                               value="<?= $liquidacion['lider'] ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="tema" class="form-label">Tema</label>
                        <select id="tema" name="tema" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($temas as $tema): ?>
                                <option value="<?= $tema['idtema'] ?>" 
                                    <?= ($tema['idtema'] == $liquidacion['tema']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tema['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="encargado" class="form-label">Encargado</label>
                        <input type="text" id="encargado" class="form-control" readonly 
                               value="<?= htmlspecialchars(obtenerEncargadoPorTema($liquidacion['tema'])['nombrecorto'] ?? '') ?>">
                        <input type="hidden" id="idencargado" name="acargode" 
                               value="<?= $liquidacion['acargode'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="datetime-local" id="fecha" name="fecha" class="form-control" 
                               value="<?= date('Y-m-d\TH:i', strtotime($liquidacion['fecha'])) ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo</label>
                    <textarea id="motivo" name="motivo" class="form-control" rows="3" required><?= htmlspecialchars($liquidacion['motivo']) ?></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="asunto" class="form-label">Asunto</label>
                        <select id="asunto" name="asunto" class="form-select" required>
                            <option value="Análisis y revisión" <?= ($liquidacion['asunto'] == 'Análisis y revisión') ? 'selected' : '' ?>>Análisis y revisión</option>
                            <option value="Horas audio" <?= ($liquidacion['asunto'] == 'Horas audio') ? 'selected' : '' ?>>Horas audio</option>
                            <option value="Reunión" <?= ($liquidacion['asunto'] == 'Reunión') ? 'selected' : '' ?>>Reunión</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cantidahoras" class="form-label">Cantidad de horas</label>
                        <input type="number" id="cantidahoras" name="cantidahoras" class="form-control" 
                               min="1" value="<?= $liquidacion['cantidahoras'] ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="Programado" <?= ($liquidacion['estado'] == 'Programado') ? 'selected' : '' ?>>Programado</option>
                            <option value="En revisión" <?= ($liquidacion['estado'] == 'En revisión') ? 'selected' : '' ?>>En revisión</option>
                            <option value="En proceso" <?= ($liquidacion['estado'] == 'En proceso') ? 'selected' : '' ?>>En proceso</option>
                            <option value="Completo" <?= ($liquidacion['estado'] == 'Completo') ? 'selected' : '' ?>>Completo</option>
                        </select>
                    </div>
                </div>
                
                <!-- Sección de distribución horaria (solo visible si estado es Completo) -->
                <div id="seccionDistribucion" style="<?= ($liquidacion['estado'] == 'Completo') ? '' : 'display: none;' ?>">
                    <h2 class="mt-4 mb-3 text-orange">Distribución Horaria</h2>
                    
                    <div id="contenedorColaboradores">
                        <?php if ($liquidacion['estado'] == 'Completo'): ?>
                            <?php foreach ($colaboradoresLiquidacion as $index => $colab): ?>
                                <div class="row mb-2 colaborador-row" data-index="<?= $index + 1 ?>">
                                    <div class="col-md-4">
                                        <label class="form-label">Colaborador <?= $index + 1 ?></label>
                                        <select name="colaboradores[<?= $index + 1 ?>][id]" class="form-select colaborador-select" required>
                                            <option value="">Seleccionar</option>
                                            <?php foreach ($colaboradoresDisponibles as $col): ?>
                                                <option value="<?= $col['ID'] ?>" 
                                                    <?= ($col['ID'] == $colab['ID']) ? 'selected' : '' ?> 
                                                    data-nombre="<?= htmlspecialchars($col['COLABORADOR']) ?>">
                                                    <?= htmlspecialchars($col['COLABORADOR']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Porcentaje</label>
                                        <input type="number" name="colaboradores[<?= $index + 1 ?>][porcentaje]" 
                                               class="form-control porcentaje-input" min="1" max="100" 
                                               value="<?= $colab['Porcentaje'] ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Comentario</label>
                                        <input type="text" name="colaboradores[<?= $index + 1 ?>][comentario]" 
                                               class="form-control" value="<?= htmlspecialchars($colab['COMENTARIO']) ?>">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn btn-danger btn-sm eliminar-colaborador">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="agregarColaborador" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Agregar Colaborador
                    </button>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Nota:</strong> La suma total de porcentajes debe ser exactamente 100%.
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" id="btnCancelar" class="btn btn-secondary me-2">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmación Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Confirmación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea cancelar la edición? Todos los cambios no guardados se perderán.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, continuar</button>
                <a href="liquidaciones.php" class="btn btn-warning">Sí, cancelar</a>
            </div>
        </div>
    </div>
</div>


<?php require_once 'includes/modales.php'; ?>
<?php require_once 'includes/footer.php'; ?>