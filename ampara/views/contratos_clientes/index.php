<?php
// views/contratos_clientes/index.php

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-file-signature me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contratoModal">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Contrato
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtros</h5>
            <form method="GET" action="contratos_clientes.php" class="row g-3 align-items-end">
                 <div class="col-md-3">
                    <label for="id_lider_filtro" class="form-label">Líder</label>
                    <select name="id_lider_filtro" id="id_lider_filtro" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php foreach ($lideres as $lider): ?>
                            <option value="<?php echo $lider['ID']; ?>" <?php echo ($filtros['id_lider_filtro'] == $lider['ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lider['COLABORADOR']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="activo_filtro" class="form-label">Estado</label>
                    <select name="activo" id="activo_filtro" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?php echo ($filtros['activo'] === '1') ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo ($filtros['activo'] === '0') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-2"></i>Filtrar</button>
                    <a href="contratos_clientes.php" class="btn btn-secondary"><i class="fas fa-sync-alt me-2"></i>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="card">
        <div class="card-body">
            <table id="tablaContratos" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Líder</th>
                        <th>Fechas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contratos as $contrato): ?>
                        <tr>
                            <td><?php echo $contrato['idcontratocli']; ?></td>
                            <td><?php echo htmlspecialchars($contrato['nombre_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['nombre_lider']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['fechainicio']); ?> a <?php echo htmlspecialchars($contrato['fechafin'] ?? 'Indefinido'); ?></td>
                            <td>
                                <?php if ($contrato['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-secondary btn-sm edit-btn" title="Editar Contrato"
                                        data-bs-toggle="modal"
                                        data-bs-target="#contratoModal"
                                        data-contrato='<?php echo htmlspecialchars(json_encode($contrato), ENT_QUOTES, 'UTF-8'); ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="contratos_clientes.php" method="POST" class="d-inline">
                                    <input type="hidden" name="idcontratocli" value="<?php echo $contrato['idcontratocli']; ?>">
                                    <?php if ($contrato['activo']): ?>
                                        <input type="hidden" name="accion" value="desactivar">
                                        <button type="submit" class="btn btn-warning btn-sm" title="Desactivar" onclick="return confirm('¿Está seguro?');">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="accion" value="activar">
                                        <button type="submit" class="btn btn-success btn-sm" title="Activar" onclick="return confirm('¿Está seguro?');">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Contrato -->
<div class="modal fade" id="contratoModal" tabindex="-1" aria-labelledby="contratoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="contratos_clientes.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="contratoModalLabel">Agregar Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idcontratocli" id="idcontratocli">
                    <input type="hidden" name="accion" id="accion" value="registrar">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="idcliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" id="idcliente" name="idcliente" required>
                                <option value="">-- Seleccione Cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['idcliente']; ?>"><?php echo htmlspecialchars($cliente['nombrecomercial']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lider" class="form-label">Líder <span class="text-danger">*</span></label>
                            <select class="form-select" id="lider" name="lider" required>
                                <option value="">-- Seleccione Líder --</option>
                                 <?php foreach ($lideres as $lider): ?>
                                    <option value="<?php echo $lider['ID']; ?>"><?php echo htmlspecialchars($lider['COLABORADOR']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fechainicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fechainicio" name="fechainicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fechafin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fechafin" name="fechafin">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="horasfijasmes" class="form-label">Horas Fijas/Mes</label>
                            <input type="number" class="form-control" id="horasfijasmes" name="horasfijasmes" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="costohorafija" class="form-label">Costo por Hora Fija</label>
                            <input type="number" step="0.01" class="form-control" id="costohorafija" name="costohorafija" value="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mesescontrato" class="form-label">Meses de Contrato</label>
                            <input type="number" class="form-control" id="mesescontrato" name="mesescontrato" value="0">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="totalhorasfijas" class="form-label">Total Horas Fijas</label>
                            <input type="number" class="form-control" id="totalhorasfijas" name="totalhorasfijas" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="costohoraextra" class="form-label">Costo Hora Extra</label>
                            <input type="number" step="0.01" class="form-control" id="costohoraextra" name="costohoraextra" value="0.00">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="planhoraextrames" class="form-label">Plan Horas Extra/Mes</label>
                            <input type="number" class="form-control" id="planhoraextrames" name="planhoraextrames" value="0">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="montofijomes" class="form-label">Monto Fijo/Mes</label>
                            <input type="number" step="0.01" class="form-control" id="montofijomes" name="montofijomes" value="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="planmontomes" class="form-label">Plan Monto/Mes</label>
                            <input type="number" step="0.01" class="form-control" id="planmontomes" name="planmontomes" value="0.00">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                             <label for="tipobolsa" class="form-label">Tipo de Bolsa</label>
                            <input type="text" class="form-control" id="tipobolsa" name="tipobolsa">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <input type="text" class="form-control" id="status" name="status">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tipohora" class="form-label">Tipo de Hora</label>
                            <input type="text" class="form-control" id="tipohora" name="tipohora">
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaContratos').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
        responsive: true,
        order: [[0, 'desc']]
    });

    const contratoModal = document.getElementById('contratoModal');
    contratoModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = contratoModal.querySelector('form');

        form.reset();
        contratoModal.querySelector('.modal-title').textContent = 'Agregar Contrato';
        form.querySelector('#accion').value = 'registrar';
        form.querySelector('#idcontratocli').value = '';

        const contratoData = button.getAttribute('data-contrato');
        if (contratoData) {
            const contrato = JSON.parse(contratoData);
            contratoModal.querySelector('.modal-title').textContent = 'Editar Contrato';
            form.querySelector('#accion').value = 'editar';

            for (const key in contrato) {
                const input = form.querySelector(`#${key}`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = contrato[key] == '1';
                    } else {
                        input.value = contrato[key];
                    }
                }
            }
        }
    });
});
</script>
