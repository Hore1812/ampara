$(document).ready(function() {
    // Inicializar DataTable
      $(document).ready(function() {
   
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
                    const totalHoras = response.total_horas || 0; // Obtener el total de horas
                    
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
                    
                    // Asegurar que el modal es una instancia de Bootstrap
                    const modalColaboradoresElement = document.getElementById('modalColaboradores');
                    if (modalColaboradoresElement) {
                        const modalInstance = bootstrap.Modal.getInstance(modalColaboradoresElement) || new bootstrap.Modal(modalColaboradoresElement);
                        modalInstance.show();
                    }
                } else {
                    // Considerar usar el modal de error genérico
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
        $('#idEliminar').val(idLiquidacion); // Asumiendo que #idEliminar es el input hidden en el form del modal
        
        const modalEliminarElement = document.getElementById('modalEliminar');
        if (modalEliminarElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalEliminarElement) || new bootstrap.Modal(modalEliminarElement);
            modalInstance.show();
        }
    });
    
    // Los manejadores de clic para '.guardar-liquidacion', '.guardar-cambios', 
    // y el '#btnCancelar' de los formularios principales, así como el submit de '#formLiquidacion'
    // son ahora manejados por js/modal_confirm_logic.js para centralizar la lógica
    // de confirmación y el uso de los modales genéricos de includes/modales.php.

    // Modal historico colaborador (apertura inicial)
    $('#colaborador').change(function() { // Este es el select en la página de filtros principal
        const idColaborador = $(this).val();
        const nombreColaborador = $(this).find('option:selected').text();
        
        if (idColaborador) {
            $('#tituloColaborador').text(nombreColaborador); // En el modal de histórico
            $('#modalHistoricoColaborador').data('idColaboradorActual', idColaborador); // Guardar ID para el filtro
            cargarHistoricoColaborador(idColaborador, $('#anioColab').val(), $('#mesColab').val(), $('#estadoColab').val()); // Cargar con filtros actuales
            
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
        $('#colaborador').val(''); // Resetea el select de la página de filtros
        // Opcional: resetear filtros dentro del modal de histórico
        $('#filtrosColaboradorForm')[0].reset(); 
    });
    
    // Funcionalidad para registrar.php y editar.php (Lógica AJAX y UI específica de esos formularios)
    if ($('#formLiquidacion').length) { // Solo ejecutar si estamos en una página con ese formulario
        
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
                error: function() { alert('Error al cargar clientes'); } // Considerar modal de error
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
                error: function() { alert('Error al cargar líder'); } // Considerar modal de error
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
                error: function() { alert('Error al cargar encargado'); } // Considerar modal de error
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
            else alert('Máximo 6 colaboradores permitidos'); // Considerar modal de error/info
        });
        
        $(document).on('click', '.eliminar-colaborador', function() {
            $(this).closest('.colaborador-row').remove();
            actualizarIndicesColaboradores();
            actualizarOpcionesColaboradores();
        });
        
        // La validación de suma de porcentajes y la lógica de #btnCancelar 
        // para #formLiquidacion ahora son manejadas por js/modal_confirm_logic.js
        // Sin embargo, la validación de suma de porcentajes ANTES de mostrar el modal de confirmación
        // podría quedarse aquí o moverse a modal_confirm_logic.js si se generaliza.
        // Por ahora, la dejo aquí, pero se ejecutará antes de que modal_confirm_logic.js muestre su modal.
        $('#formLiquidacion').on('submit', function(e) { // Adjuntarse al evento submit también
            if ($('#estado').val() === 'Completo') {
                let total = 0;
                $('.porcentaje-input').each(function() { total += parseInt($(this).val()) || 0; });
                if (total !== 100) {
                    // Si la validación aquí falla, el modal de confirmación de modal_confirm_logic.js no debería mostrarse.
                    // Esto significa que el e.preventDefault() aquí es importante.
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
            // Si la validación pasa, no hacemos e.preventDefault() aquí, 
            // para permitir que el manejador de submit en modal_confirm_logic.js actúe.
        });
    } // Fin de if ($('#formLiquidacion').length)
    
    // Funciones auxiliares (cargarHistoricoColaborador, formatDate, agregarColaborador, etc.)
    // Se mantienen como están, pero los alert() podrían reemplazarse por el uso del modal de error.
  
});
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
                    tablaHistorico.find('tbody').empty(); // Limpiar antes de rellenar si se destruye
                }
    
                if (response.success && response.data.length > 0) {
                    // Re-inicializar DataTable con el footerCallback para los totales
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

                            // Sum for 'Acumulado' column (index 8)
                            var sumAcumulado = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                                return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                            }, 0);

                            // Sum for 'Horas' column (index 9)
                            var sumHoras = api.column(9, { page: 'current' }).data().reduce(function (a, b) {
                                return (parseInt(a) || 0) + (parseInt(b) || 0);
                            }, 0);

                            // Update footer
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
                // Manejo de error similar al de arriba
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
            url: 'ajax/obtener_colaboradores_disponibles.php', // Asumo que este endpoint existe y devuelve todos los activos
            method: 'POST', // o GET, según tu endpoint
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
        $('.colaborador-row').each(function(i) { // 'i' es el índice base 0
            const newIndex = i + 1; // El índice visual y para el name del array
            $(this).attr('data-index', newIndex); 
            // Actualizar el texto del label del colaborador
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

    // Lógica para el Modal de Perfil de Usuario
    $('#enlacePerfilUsuario').on('click', function(e) {
        e.preventDefault();

        const modalPerfil = new bootstrap.Modal(document.getElementById('modalPerfilUsuario'));
        const loadingDiv = $('#loadingPerfil');
        const contentDiv = $('#contentPerfil');

        // Mostrar spinner y ocultar contenido
        loadingDiv.show();
        contentDiv.hide();
        modalPerfil.show();

        $.ajax({
            url: 'ajax/obtener_perfil_usuario.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const perfil = response.data;
                    const nombreCompleto = `${perfil.nombres || ''} ${perfil.paterno || ''} ${perfil.materno || ''}`.trim();
                    
                    $('#perfilFoto').attr('src', perfil.rutafoto || 'img/fotos/empleados/usuario01.png');
                    $('#perfilNombreCorto').text(perfil.nombrecorto || 'N/A');
                    $('#perfilCargo').text(perfil.cargo || 'N/A');
                    $('#perfilNombreCompleto').text(nombreCompleto);
                    $('#perfilDni').text(perfil.dni || 'N/A');
                    $('#perfilCorreoPersonal').text(perfil.correopersonal || 'N/A');
                    $('#perfilUsername').text(perfil.username || 'N/A');
                    $('#perfilCorreoCorp').text(perfil.correocorporativo || 'N/A');
                    $('#perfilArea').text(perfil.area || 'N/A');

                    // Ocultar spinner y mostrar contenido
                    loadingDiv.hide();
                    contentDiv.show();
                } else {
                    loadingDiv.html(`<p class="text-danger">${response.message || 'Error al cargar el perfil.'}</p>`);
                }
            },
            error: function() {
                loadingDiv.html('<p class="text-danger">Error de conexión al intentar obtener los datos del perfil.</p>');
            }
        });
    });
});
