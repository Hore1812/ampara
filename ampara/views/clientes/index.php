<?php
// views/clientes/index.php

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal" data-id="0">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo Cliente
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtros</h5>
            <form method="GET" action="clientes.php" class="row g-3 align-items-end">
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
                    <a href="clientes.php" class="btn btn-secondary"><i class="fas fa-sync-alt me-2"></i>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="card">
        <div class="card-body">
            <table id="tablaClientes" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Comercial</th>
                        <th>Razón Social</th>
                        <th>RUC</th>
                        <th>Representante</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo $cliente['idcliente']; ?></td>
                            <td><?php echo htmlspecialchars($cliente['nombrecomercial']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['razonsocial']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['ruc']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['representante']); ?></td>
                            <td>
                                <?php if ($cliente['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-secondary btn-sm edit-btn" title="Editar Cliente"
                                        data-bs-toggle="modal"
                                        data-bs-target="#clienteModal"
                                        data-cliente='<?php echo htmlspecialchars(json_encode($cliente), ENT_QUOTES, 'UTF-8'); ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="clientes.php" method="POST" class="d-inline">
                                    <input type="hidden" name="idcliente" value="<?php echo $cliente['idcliente']; ?>">
                                    <?php if ($cliente['activo']): ?>
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

<!-- Modal for Add/Edit Cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="clientes.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="clienteModalLabel">Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idcliente" id="idcliente">
                    <input type="hidden" name="accion" id="accion" value="registrar">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombrecomercial" class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombrecomercial" name="nombrecomercial" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="razonsocial" class="form-label">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="razonsocial" name="razonsocial" required>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ruc" class="form-label">RUC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ruc" name="ruc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sitioweb" class="form-label">Sitio Web</label>
                            <input type="url" class="form-control" id="sitioweb" name="sitioweb">
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-primary">Contacto Principal</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="representante" class="form-label">Representante</label>
                            <input type="text" class="form-control" id="representante" name="representante">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telrepresentante" class="form-label">Teléfono del Representante</label>
                            <input type="text" class="form-control" id="telrepresentante" name="telrepresentante">
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="correorepre" class="form-label">Correo del Representante</label>
                        <input type="email" class="form-control" id="correorepre" name="correorepre">
                    </div>
                    <hr>
                    <h6 class="text-primary">Gerencia</h6>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gerente" class="form-label">Gerente</label>
                            <input type="text" class="form-control" id="gerente" name="gerente">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telgerente" class="form-label">Teléfono del Gerente</label>
                            <input type="text" class="form-control" id="telgerente" name="telgerente">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correogerente" class="form-label">Correo del Gerente</label>
                        <input type="email" class="form-control" id="correogerente" name="correogerente">
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
    $('#tablaClientes').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
        responsive: true,
        order: [[1, 'asc']]
    });

    const clienteModal = document.getElementById('clienteModal');
    clienteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = clienteModal.querySelector('form');

        // Reset form to default state for "Add New"
        form.reset();
        clienteModal.querySelector('.modal-title').textContent = 'Agregar Cliente';
        form.querySelector('#accion').value = 'registrar';
        form.querySelector('#idcliente').value = '';

        const clienteData = button.getAttribute('data-cliente');
        if (clienteData) {
            // Edit mode
            const cliente = JSON.parse(clienteData);
            clienteModal.querySelector('.modal-title').textContent = 'Editar Cliente';
            form.querySelector('#accion').value = 'editar';
            form.querySelector('#idcliente').value = cliente.idcliente;

            // Populate all fields
            for (const key in cliente) {
                const input = form.querySelector(`#${key}`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = cliente[key] == '1';
                    } else {
                        input.value = cliente[key];
                    }
                }
            }
        }
    });
});
</script>
