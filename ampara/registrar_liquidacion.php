<?php
require_once 'includes/header.php';
require_once 'funciones.php';

$tiposHora = obtenerTiposHora();
$temas = obtenerTemas();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">REGISTRAR LIQUIDACIÓN</h1>
        <a href="liquidaciones.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form id="formLiquidacion" method="POST" action="guardar_liquidacion.php">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="tipohora" class="form-label">Tipo de Hora</label>
                        <select id="tipohora" name="tipohora" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($tiposHora as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['tipohora']) ?>">
                                    <?= htmlspecialchars($tipo['tipohora']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cliente" class="form-label">Cliente</label>
                        <select id="cliente" name="cliente" class="form-select" disabled required>
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="lider" class="form-label">Líder</label>
                        <input type="text" id="lider" class="form-control" readonly>
                        <input type="hidden" id="idlider" name="lider">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="tema" class="form-label">Tema</label>
                        <select id="tema" name="tema" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($temas as $tema): ?>
                                <option value="<?= $tema['idtema'] ?>">
                                    <?= htmlspecialchars($tema['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="encargado" class="form-label">Encargado</label>
                        <input type="text" id="encargado" class="form-control" readonly>
                        <input type="hidden" id="idencargado" name="acargode">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="datetime-local" id="fecha" name="fecha" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo</label>
                    <textarea id="motivo" name="motivo" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="asunto" class="form-label">Asunto</label>
                        <select id="asunto" name="asunto" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <option value="Análisis y revisión">Análisis y revisión</option>
                            <option value="Horas audio">Horas audio</option>
                            <option value="Reunión">Reunión</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cantidahoras" class="form-label">Cantidad de horas</label>
                        <input type="number" id="cantidahoras" name="cantidahoras" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="Programado">Programado</option>
                            <option value="En revisión">En revisión</option>
                            <option value="En proceso">En proceso</option>
                            <option value="Completo">Completo</option>
                        </select>
                    </div>
                </div>
                
                <!-- Sección de distribución horaria (solo visible si estado es Completo) -->
                <div id="seccionDistribucion" style="display: none;">
                    <h2 class="mt-4 mb-3 text-orange">Distribución Horaria</h2>
                    
                    <div id="contenedorColaboradores">
                        <!-- Se agregarán dinámicamente los inputs para colaboradores -->
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
                    <button type="submit" class="btn btn-primary guardar-liquidacion">
                        <i class="fas fa-save"></i> Guardar Liquidación
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
                ¿Está seguro que desea cancelar el registro? Todos los datos no guardados se perderán.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, continuar</button>
                <a href="liquidaciones.php" class="btn btn-warning">Sí, cancelar</a>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once 'includes/modales.php'; ?>
<?php require_once 'includes/footer.php'; ?>