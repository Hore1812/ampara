$(document).ready(function() {
    // Nota: Se eliminó el $(document).ready() duplicado para mayor corrección.

    $('#tablaLiquidaciones').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[0, 'desc']],
        dom: '<"top"lf>rt<"bottom"ip>',
        responsive: true
    });

    // Limpiar filtros
    $('#limpiarFiltros').click(function(e) {
        e.preventDefault();
        $('#filtrosForm').find('select').val('');
        window.location.href = 'liquidaciones.php';
    });

    // Modal ver colaboradores
    $(document).on('click', '.ver-colaboradores', function() {
        const idLiquidacion = $(this).data('id');
        $('#tituloLiquidacion').text(idLiquidacion);

        $.ajax({
            url: 'ajax/obtener_colaboradores.php',
            method: 'POST',
            data: { idliquidacion: idLiquidacion },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    let totalPorcentaje = 0;
                    let totalCalculo = 0;
                    const totalHoras = response.total_horas || 0;

                    response.data.forEach(colab => {
                        html += `
                            <tr>
                                <td>${colab.ID}</td>
                                <td>${colab.COLABORADOR}</td>
                                <td>${colab.Porcentaje}%</td>
                                <td>${colab.CALCULO}</td>
                                <td>${totalHoras}</td>
                                <td>${colab.COMENTARIO}</td>
                            </tr>
                        `;
                        totalPorcentaje += parseInt(colab.Porcentaje);
                        totalCalculo += parseFloat(colab.CALCULO);
                    });

                    $('#tablaColaboradores').html(html);
                    $('#totalPorcentaje').text(totalPorcentaje + '%');
                    $('#totalCalculo').text(totalCalculo.toFixed(2));

                    const modalColaboradoresElement = document.getElementById('modalColaboradores');
                    if (modalColaboradoresElement) {
                        const modalInstance = bootstrap.Modal.getInstance(modalColaboradoresElement) || new bootstrap.Modal(modalColaboradoresElement);
                        modalInstance.show();
                    }
                } else {
                    const modalErrorElement = document.getElementById('modalError');
                    if(modalErrorElement) {
                        modalErrorElement.querySelector('#mensajeError').textContent = response.message || 'Error al cargar colaboradores.';
                        const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                        modalInstance.show();
                    } else {
                        alert(response.message || 'Error al cargar colaboradores.');
                    }
                }
            },
            error: function() {
                const modalErrorElement = document.getElementById('modalError');
                if(modalErrorElement) {
                    modalErrorElement.querySelector('#mensajeError').textContent = 'Error de conexión al cargar los colaboradores.';
                    const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                    modalInstance.show();
                } else {
                    alert('Error de conexión al cargar los colaboradores.');
                }
            }
        });
    });

    // Modal eliminar liquidación
    $(document).on('click', '.eliminar-liquidacion', function() {
        const idLiquidacion = $(this).data('id');
        $('#idEliminar').val(idLiquidacion);

        const modalEliminarElement = document.getElementById('modalEliminar');
        if (modalEliminarElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalEliminarElement) || new bootstrap.Modal(modalEliminarElement);
            modalInstance.show();
        }
    });

    // Modal historico colaborador (apertura inicial)
    $('#colaborador').change(function() {
        const idColaborador = $(this).val();
        const nombreColaborador = $(this).find('option:selected').text();

        if (idColaborador) {
            $('#tituloColaborador').text(nombreColaborador);
            $('#modalHistoricoColaborador').data('idColaboradorActual', idColaborador);
            cargarHistoricoColaborador(idColaborador, $('#anioColab').val(), $('#mesColab').val(), $('#estadoColab').val());

            const modalHistoricoElement = document.getElementById('modalHistoricoColaborador');
            if(modalHistoricoElement) {
                const modalInstance = bootstrap.Modal.getInstance(modalHistoricoElement) || new bootstrap.Modal(modalHistoricoElement);
                modalInstance.show();
            }
        } else {
            $('#modalHistoricoColaborador').removeData('idColaboradorActual');
        }
    });

    // Filtrar historico colaborador (dentro del modal)
    $('#filtrosColaboradorForm').submit(function(e) {
        e.preventDefault();
        const idColaborador = $('#modalHistoricoColaborador').data('idColaboradorActual');
        const anio = $('#anioColab').val();
        const mes = $('#mesColab').val();
        const clienteIdcon = $('#clienteIdcon').val();

        if (idColaborador) {
            cargarHistoricoColaborador(idColaborador, anio, mes, clienteIdcon);
        }
    });

    // Cerrar modal y resetear select colaborador de la página principal
    $('#modalHistoricoColaborador').on('hidden.bs.modal', function() {
        $('#colaborador').val('');
        $('#filtrosColaboradorForm')[0].reset();
    });

    // Funcionalidad para registrar.php y editar.php
    if ($('#formLiquidacion').length) {

        $('#tipohora').change(function() {
            const tipoHora = $(this).val();
            $('#cliente').prop('disabled', !tipoHora);
            if (!tipoHora) {
                $('#cliente').html('<option value="">Seleccionar</option>').val('');
                $('#lider').val('');
                $('#idlider').val('');
                return;
            }
            $.ajax({
                url: 'ajax/obtener_clientes.php',
                method: 'POST', data: { tipohora: tipoHora }, dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Seleccionar</option>';
                    if (response.success) {
                        response.data.forEach(cliente => {
                            const parts = cliente.CLIENTE.split(' – ');
                            const nombreCliente = parts.length > 1 ? parts[1] : cliente.CLIENTE;
                            options += `<option value="${cliente.idcontratocli}">${nombreCliente}</option>`;
                        });
                    }
                    $('#cliente').html(options);
                },
                error: function() { alert('Error al cargar clientes'); }
            });
        });

        $('#cliente').change(function() {
            const idContrato = $(this).val();
            if (!idContrato) {
                $('#lider').val('');
                $('#idlider').val('');
                return;
            }
            $.ajax({
                url: 'ajax/obtener_lider.php',
                method: 'POST', data: { idcontrato: idContrato }, dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#lider').val(response.data.nombrecorto);
                        $('#idlider').val(response.data.lider);
                    } else { $('#lider').val(''); $('#idlider').val(''); }
                },
                error: function() { alert('Error al cargar líder'); }
            });
        });

        $('#tema').change(function() {
            const idTema = $(this).val();
            if (!idTema) {
                $('#encargado').val('');
                $('#idencargado').val('');
                return;
            }
            $.ajax({
                url: 'ajax/obtener_encargado.php',
                method: 'POST', data: { idtema: idTema }, dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#encargado').val(response.data.nombrecorto);
                        $('#idencargado').val(response.data.idempleado);
                    } else { $('#encargado').val(''); $('#idencargado').val(''); }
                },
                error: function() { alert('Error al cargar encargado'); }
            });
        });

        $('#estado').change(function() {
            if ($(this).val() === 'Completo') {
                $('#seccionDistribucion').slideDown();
                if ($('.colaborador-row').length === 0) agregarColaborador();
            } else {
                $('#seccionDistribucion').slideUp();
            }
        });

        $('#agregarColaborador').click(function() {
            if ($('.colaborador-row').length < 6) agregarColaborador();
            else alert('Máximo 6 colaboradores permitidos');
        });

        $(document).on('click', '.eliminar-colaborador', function() {
            $(this).closest('.colaborador-row').remove();
            actualizarIndicesColaboradores();
            actualizarOpcionesColaboradores();
        });

        $('#formLiquidacion').on('submit', function(e) {
            if ($('#estado').val() === 'Completo') {
                let total = 0;
                $('.porcentaje-input').each(function() { total += parseInt($(this).val()) || 0; });
                if (total !== 100) {
                    e.preventDefault();

                    const modalErrorElement = document.getElementById('modalError');
                    if(modalErrorElement) {
                        modalErrorElement.querySelector('#mensajeError').textContent = `La suma total de porcentajes debe ser exactamente 100%. Actual: ${total}%`;
                        const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                        modalInstance.show();
                    } else {
                        alert(`La suma total de porcentajes debe ser exactamente 100%. Actual: ${total}%`);
                    }
                }
            }
        });
    }

    // Lógica para el Modal de Perfil de Usuario
    $('#enlacePerfilUsuario').on('click', function(e) {
        e.preventDefault();

        const modalPerfil = new bootstrap.Modal(document.getElementById('modalPerfilUsuario'));
        const loadingDiv = $('#loadingPerfil');
        const formDiv = $('#formPerfilUsuario');
        const saveButton = $('#guardarPerfilBtn');

        loadingDiv.show();
        formDiv.hide();
        saveButton.hide();
        $('#perfilError').hide().text('');
        modalPerfil.show();

        $.ajax({
            url: 'ajax/obtener_perfil_usuario.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const perfil = response.data;

                    // Poblar el formulario
                    $('#nombreUsuarioEnModal').text(perfil.nombrecorto || 'Usuario');
                    $('#perfilFotoPreview').attr('src', perfil.rutafoto || 'img/fotos/empleados/usuario01.png');
                    $('#perfilNombres').val(perfil.nombres);
                    $('#perfilPaterno').val(perfil.paterno);
                    $('#perfilMaterno').val(perfil.materno);
                    $('#perfilNombreCorto').val(perfil.nombrecorto);
                    $('#perfilDni').val(perfil.dni);
                    $('#perfilNacimiento').val(perfil.nacimiento);
                    $('#perfilLugarNacimiento').val(perfil.lugarnacimiento);
                    $('#perfilEstadoCivil').val(perfil.estadocivil);
                    $('#perfilDomicilio').val(perfil.domicilio);
                    $('#perfilCorreoPersonal').val(perfil.correopersonal);
                    $('#perfilTelCelular').val(perfil.telcelular);
                    $('#perfilTelFijo').val(perfil.telfijo);
                    $('#perfilContactoEmergencia').val(perfil.contactoemergencia);
                    $('#perfilFoto').val(''); // Limpiar el input de archivo

                    // Limpiar campos de contraseña
                    $('#perfilPasswordActual').val('');
                    $('#perfilPasswordNuevo').val('');
                    $('#perfilPasswordConfirmar').val('');

                    loadingDiv.hide();
                    formDiv.show();
                    saveButton.show();
                } else {
                    loadingDiv.html(`<p class="text-danger">${response.message || 'Error al cargar el perfil.'}</p>`);
                }
            },
            error: function() {
                loadingDiv.html('<p class="text-danger">Error de conexión al intentar obtener los datos del perfil.</p>');
            }
        });
    });

    // Manejar el envío del formulario de perfil
    $('#formPerfilUsuario').on('submit', function(e) {
        e.preventDefault();

        const errorDiv = $('#perfilError');
        errorDiv.hide().text('');

        // Validación de contraseña
        const passNuevo = $('#perfilPasswordNuevo').val();
        const passConfirmar = $('#perfilPasswordConfirmar').val();
        const passActual = $('#perfilPasswordActual').val();

        if (passNuevo !== passConfirmar) {
            errorDiv.text('Las nuevas contraseñas no coinciden.').show();
            return;
        }

        if (passNuevo && !passActual) {
            errorDiv.text('Debe ingresar su contraseña actual para poder cambiarla.').show();
            return;
        }

        const formData = new FormData(this);
        const saveButton = $('#guardarPerfilBtn');
        saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        $.ajax({
            url: 'ajax/actualizar_perfil.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Crear un alert de Bootstrap dinámicamente
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;

                    // Añadir el alert al contenedor principal
                    $('.main-content').prepend(alertHtml);

                    // Cerrar modal
                    const modalPerfil = bootstrap.Modal.getInstance(document.getElementById('modalPerfilUsuario'));
                    modalPerfil.hide();

                    // Actualizar navbar si los datos cambiaron
                    if(response.newData) {
                        if(response.newData.nombre_usuario_display) {
                            // Target the text node of the user dropdown link
                            $('#navbarDropdownUser').contents().filter(function() {
                                return this.nodeType === 3; // Node.TEXT_NODE
                            }).first().replaceWith(' ' + response.newData.nombre_usuario_display);
                        }
                        if(response.newData.ruta_foto_usuario) {
                             $('.user-avatar').attr('src', response.newData.ruta_foto_usuario + '?t=' + new Date().getTime());
                        }
                    }
                } else {
                    errorDiv.text(response.message || 'Ocurrió un error desconocido.').show();
                }
            },
            error: function() {
                errorDiv.text('Error de conexión. No se pudo guardar el perfil.').show();
            },
            complete: function() {
                saveButton.prop('disabled', false).text('Guardar Cambios');
            }
        });
    });

    // Preview de la foto de perfil al seleccionarla
    $('#perfilFoto').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#perfilFotoPreview').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Funciones auxiliares
    function cargarHistoricoColaborador(idColaborador, anio = null, mes = null, clienteIdcon = null) {
        $.ajax({
            url: 'ajax/obtener_historico_colaborador.php',
            method: 'POST',
            data: { idcolaborador: idColaborador, anio: anio, mes: mes, clienteIdcon: clienteIdcon },
            dataType: 'json',
            success: function(response) {
                var tablaHistorico = $('#tablaHistoricoColaborador');
                if ($.fn.DataTable.isDataTable(tablaHistorico)) {
                    tablaHistorico.DataTable().destroy();
                    tablaHistorico.find('tbody').empty();
                }

                if (response.success && response.data.length > 0) {
                    tablaHistorico.DataTable({
                        language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                        dom: '<"top"lf>rt<"bottom"ip>',
                        responsive: true,
                        destroy: true,
                        data: response.data,
                        columns: [
                            { data: 'ID' },
                            { data: 'FECHA', render: function(data){ return formatDate(data); }},
                            { data: 'CLIENTE' },
                            { data: 'TEMA' },
                            { data: 'ASUNTO' },
                            { data: 'MOTIVO' },
                            { data: 'LIDER' },
                            { data: 'ENCARGADO' },
                            { data: 'ACUMULADO', className: 'dt-body-right' },
                            { data: 'HORAS' },
                            { data: 'TIPOHORA' }
                        ],
                        footerCallback: function(row, data, start, end, display) {
                            var api = this.api();
                            var sumAcumulado = api.column(8, { page: 'current' }).data().reduce((a, b) => (parseFloat(a) || 0) + (parseFloat(b) || 0), 0);
                            var sumHoras = api.column(9, { page: 'current' }).data().reduce((a, b) => (parseInt(a) || 0) + (parseInt(b) || 0), 0);
                            $('#totalAcumuladoHistorico').text(sumAcumulado.toFixed(2));
                            $('#totalHorasHistorico').text(sumHoras);
                            var pageInfo = api.page.info();
                            $('#conteoRegistrosHistorico').text(`Mostrando ${pageInfo.recordsDisplay} de ${pageInfo.recordsTotal} registros`);
                        }
                    });
                } else {
                    tablaHistorico.find('tbody').html('<tr><td colspan="11" class="text-center">No se encontraron datos.</td></tr>');
                    $('#totalAcumuladoHistorico').text('0.00');
                    $('#conteoRegistrosHistorico').text('Mostrando 0 de 0 registros');
                    if (!response.success && response.message) {
                         const modalErrorElement = document.getElementById('modalError');
                        if(modalErrorElement) {
                            modalErrorElement.querySelector('#mensajeError').textContent = response.message;
                            const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                            modalInstance.show();
                        } else { alert(response.message); }
                    }
                }
            },
            error: function() {
                $('#tablaHistoricoColaborador tbody').html('<tr><td colspan="11" class="text-center">Error de conexión.</td></tr>');
                $('#totalAcumuladoHistorico').text('0.00');
                $('#conteoRegistrosHistorico').text('Mostrando 0 de 0 registros');
                const modalErrorElement = document.getElementById('modalError');
                if(modalErrorElement) {
                    modalErrorElement.querySelector('#mensajeError').textContent = 'Error de conexión al cargar el histórico.';
                    const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                    modalInstance.show();
                } else { alert('Error de conexión al cargar el histórico.');}
            }
        });
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    function agregarColaborador() {
        const index = ($('.colaborador-row').length ? Math.max(...$('.colaborador-row').map(function() { return $(this).data('index'); }).get()) : 0) + 1;

        $.ajax({
            url: 'ajax/obtener_colaboradores_disponibles.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccionar</option>';
                    response.data.forEach(colab => {
                        options += `<option value="${colab.ID}" data-nombre="${colab.COLABORADOR}">${colab.COLABORADOR}</option>`;
                    });

                    const html = `
                        <div class="row mb-2 colaborador-row" data-index="${index}">
                            <div class="col-md-4">
                                <label class="form-label">Colaborador ${index}</label>
                                <select name="colaboradores[${index}][id]" class="form-select colaborador-select" required>
                                    ${options}
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Porcentaje</label>
                                <input type="number" name="colaboradores[${index}][porcentaje]"
                                       class="form-control porcentaje-input" min="1" max="100" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Comentario</label>
                                <input type="text" name="colaboradores[${index}][comentario]" class="form-control">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm eliminar-colaborador">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                    $('#contenedorColaboradores').append(html);
                    actualizarOpcionesColaboradores();
                } else {
                     const modalErrorElement = document.getElementById('modalError');
                    if(modalErrorElement) {
                        modalErrorElement.querySelector('#mensajeError').textContent = response.message || 'No se pudieron cargar colaboradores.';
                        const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                        modalInstance.show();
                    } else {alert(response.message || 'No se pudieron cargar colaboradores.');}
                }
            },
            error: function() {
                 const modalErrorElement = document.getElementById('modalError');
                if(modalErrorElement) {
                    modalErrorElement.querySelector('#mensajeError').textContent = 'Error al cargar lista de colaboradores.';
                    const modalInstance = bootstrap.Modal.getInstance(modalErrorElement) || new bootstrap.Modal(modalErrorElement);
                    modalInstance.show();
                } else {alert('Error al cargar lista de colaboradores.');}
            }
        });
    }

    function actualizarIndicesColaboradores() {
        $('.colaborador-row').each(function(i) {
            const newIndex = i + 1;
            $(this).attr('data-index', newIndex);
            $(this).find('.col-md-4:first-child label.form-label').text(`Colaborador ${newIndex}`);
            $(this).find('select.colaborador-select').attr('name', `colaboradores[${newIndex}][id]`);
            $(this).find('input.porcentaje-input').attr('name', `colaboradores[${newIndex}][porcentaje]`);
            $(this).find('input[type="text"]').attr('name', `colaboradores[${newIndex}][comentario]`);
        });
    }

    function actualizarOpcionesColaboradores() {
        const colaboradoresSeleccionados = [];
        $('.colaborador-select').each(function() {
            const selectedId = $(this).val();
            if (selectedId) colaboradoresSeleccionados.push(selectedId);
        });

        $('.colaborador-select').each(function() {
            const currentSelect = $(this);
            const currentSelectedId = currentSelect.val();
            currentSelect.find('option').each(function() {
                const option = $(this);
                const optionId = option.val();
                if (optionId && optionId !== currentSelectedId && colaboradoresSeleccionados.includes(optionId)) {
                    option.prop('disabled', true);
                } else {
                    option.prop('disabled', false);
                }
            });
        });
    }

    $(document).on('change', '.colaborador-select', function() {
        actualizarOpcionesColaboradores();
    });

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Lógica para el modal de confirmación de cierre de sesión
    $('#enlaceCerrarSesion').on('click', function(e) {
        e.preventDefault();

        const modalCancelarElement = document.getElementById('modalCancelar');
        if (modalCancelarElement) {
            const modalTitle = modalCancelarElement.querySelector('.modal-title');
            const modalBody = modalCancelarElement.querySelector('.modal-body');
            const modalFooter = modalCancelarElement.querySelector('.modal-footer');

            modalTitle.textContent = 'Confirmar Cierre de Sesión';
            modalBody.textContent = '¿Está seguro de que desea cerrar la sesión?';

            // Limpiar y configurar el footer
            modalFooter.innerHTML = '';
            const btnNo = document.createElement('button');
            btnNo.type = 'button';
            btnNo.className = 'btn btn-secondary';
            btnNo.textContent = 'No';
            btnNo.setAttribute('data-bs-dismiss', 'modal');

            const btnSi = document.createElement('button');
            btnSi.type = 'button';
            btnSi.className = 'btn btn-danger';
            btnSi.textContent = 'Sí, cerrar sesión';
            btnSi.id = 'btnConfirmarLogout';

            modalFooter.appendChild(btnNo);
            modalFooter.appendChild(btnSi);

            const modal = new bootstrap.Modal(modalCancelarElement);

            // Añadir el listener al botón de confirmación
            document.getElementById('btnConfirmarLogout').addEventListener('click', function() {
                window.location.href = 'logout.php';
            }, { once: true }); // Use 'once' to avoid multiple bindings

            modal.show();
        }
    });
});
