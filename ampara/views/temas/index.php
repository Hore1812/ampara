<?php
// views/temas/index.php - The "View" for the Temas module

// The controller (temas.php) has already prepared all the necessary variables:
// $page_title, $temas, $empleados, $filtros

require_once __DIR__ . '/../../includes/header.php'; // Go up two levels to find includes
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-tags me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#temaModal" data-id="0">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Tema
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtros</h5>
            <form method="GET" action="temas.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="descripcion_filtro" class="form-label">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion_filtro" class="form-control" value="<?php echo htmlspecialchars($filtros['descripcion']); ?>">
                </div>
                <div class="col-md-3">
                    <label for="idencargado_filtro" class="form-label">Encargado</label>
                    <select name="idencargado" id="idencargado_filtro" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php foreach ($empleados as $empleado): ?>
                            <option value="<?php echo $empleado['idempleado']; ?>" <?php echo ($filtros['idencargado'] == $empleado['idempleado']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empleado['nombrecorto']); ?> (<?php echo $empleado['tema_count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="activo_filtro" class="form-label">Estado</label>
                    <select name="activo" id="activo_filtro" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?php echo ($filtros['activo'] === '1') ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo ($filtros['activo'] === '0') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-2"></i>Filtrar</button>
                    <a href="temas.php" class="btn btn-secondary"><i class="fas fa-sync-alt me-2"></i>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Themes Table -->
    <div class="card">
        <div class="card-body">
            <table id="tablaTemas" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Encargado</th>
                        <th>Comentario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($temas)): ?>
                        <?php foreach ($temas as $tema): ?>
                            <tr>
                                <td><?php echo $tema['idtema']; ?></td>
                                <td><?php echo htmlspecialchars($tema['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($tema['nombre_encargado'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($tema['comentario']); ?></td>
                                <td>
                                    <?php if ($tema['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-secondary btn-sm edit-btn" title="Editar Tema"
                                            data-bs-toggle="modal"
                                            data-bs-target="#temaModal"
                                            data-id="<?php echo $tema['idtema']; ?>"
                                            data-descripcion="<?php echo htmlspecialchars($tema['descripcion']); ?>"
                                            data-idencargado="<?php echo $tema['idencargado']; ?>"
                                            data-comentario="<?php echo htmlspecialchars($tema['comentario']); ?>"
                                            data-activo="<?php echo $tema['activo']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="temas.php" method="POST" class="d-inline">
                                        <input type="hidden" name="idtema" value="<?php echo $tema['idtema']; ?>">
                                        <?php if ($tema['activo']): ?>
                                            <input type="hidden" name="accion" value="desactivar">
                                            <button type="submit" class="btn btn-warning btn-sm" title="Desactivar Tema" onclick="return confirm('¿Está seguro que desea desactivar este tema?');">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="accion" value="activar">
                                            <button type="submit" class="btn btn-success btn-sm" title="Activar Tema" onclick="return confirm('¿Está seguro que desea activar este tema?');">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No se encontraron temas con los filtros seleccionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Tema -->
<div class="modal fade" id="temaModal" tabindex="-1" aria-labelledby="temaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="temas.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="temaModalLabel">Agregar Tema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idtema" id="idtema">
                    <input type="hidden" name="accion" id="accion" value="registrar">

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="mb-3">
                        <label for="idencargado" class="form-label">Encargado</label>
                        <select class="form-select" id="idencargado" name="idencargado">
                            <option value="">-- Sin Asignar --</option>
                            <?php foreach ($empleados as $empleado): ?>
                                <option value="<?php echo $empleado['idempleado']; ?>"><?php echo htmlspecialchars($empleado['nombrecorto']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<!-- Include the dedicated JavaScript file for this view -->
<script src="assets/js/temas.js"></script>
