document.addEventListener('DOMContentLoaded', function () {
    const formLiquidacion = document.getElementById('formLiquidacion');
    const btnCancelar = document.getElementById('btnCancelar'); // Para el modal de cancelación específico de la página

    // Instancias de Modales (los obtenemos de includes/modales.php o de la página actual)
    const modalCancelarElement = document.getElementById('modalCancelar'); // Específico de la página
    const modalConfirmarGuardadoElement = document.getElementById('modalConfirmarGuardado'); // De includes/modales.php
    const modalExitoElement = document.getElementById('modalExito'); // De includes/modales.php
    const modalErrorElement = document.getElementById('modalError'); // De includes/modales.php

    let modalCancelarInstance = modalCancelarElement ? new bootstrap.Modal(modalCancelarElement) : null;
    let modalConfirmarGuardadoInstance = modalConfirmarGuardadoElement ? new bootstrap.Modal(modalConfirmarGuardadoElement) : null;
    let modalExitoInstance = modalExitoElement ? new bootstrap.Modal(modalExitoElement) : null;
    let modalErrorInstance = modalErrorElement ? new bootstrap.Modal(modalErrorElement) : null;

    const btnConfirmarGuardarSubmit = document.getElementById('btnConfirmarGuardarSubmit'); // Botón dentro de #modalConfirmarGuardado

    // 1. Lógica para el botón de Cancelar del formulario
    if (btnCancelar && modalCancelarInstance) {
        btnCancelar.addEventListener('click', function () {
            modalCancelarInstance.show();
        });
    }

    // 2. Lógica para la confirmación antes de enviar el formulario
    if (formLiquidacion && modalConfirmarGuardadoInstance) {
        formLiquidacion.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevenir el envío automático
            // Podríamos personalizar el mensaje del modalConfirmarGuardado aquí si es necesario,
            // pero por ahora usa el mensaje genérico.
            // const modalTitle = document.querySelector('#modalConfirmarGuardado .modal-title');
            // const modalBody = document.querySelector('#modalConfirmarGuardado .modal-body');
            // if (formLiquidacion.action.includes('actualizar_liquidacion.php')) {
            //    if(modalTitle) modalTitle.textContent = 'Confirmar Actualización';
            //    if(modalBody) modalBody.innerHTML = '¿Está seguro que desea guardar los cambios en esta liquidación?';
            // } else {
            //    if(modalTitle) modalTitle.textContent = 'Confirmar Registro';
            //    if(modalBody) modalBody.innerHTML = '¿Está seguro que desea registrar esta nueva liquidación?';
            // }
            modalConfirmarGuardadoInstance.show();
        });
    }

    // 3. Lógica para el botón "Sí, guardar" DENTRO del modal #modalConfirmarGuardado
    if (btnConfirmarGuardarSubmit && formLiquidacion) {
        btnConfirmarGuardarSubmit.addEventListener('click', function () {
            if (modalConfirmarGuardadoInstance) {
                modalConfirmarGuardadoInstance.hide();
            }
            formLiquidacion.submit(); // Enviar el formulario
        });
    }

    // 4. Lógica para mostrar modales de Éxito/Error basados en mensajes de sesión (pasados desde PHP)
    // Estos mensajes se deben imprimir en el HTML, por ejemplo, en un div oculto o como variables JS.
    // Aquí asumimos que los mensajes están disponibles como variables globales de JS
    // que se establecen mediante un bloque <script> en el PHP.

    // Buscamos los elementos donde PHP podría haber dejado los mensajes
    const phpExitoMessageElement = document.getElementById('php_session_exito_message');
    const phpErrorMessageElement = document.getElementById('php_session_error_message');

    if (phpExitoMessageElement && phpExitoMessageElement.value && modalExitoInstance) {
        const mensajeExitoBody = modalExitoElement.querySelector('#mensajeExito'); // ID del cuerpo del modal de éxito
        if (mensajeExitoBody) {
            mensajeExitoBody.textContent = phpExitoMessageElement.value;
        }
        modalExitoInstance.show();
        // Opcional: Limpiar el valor para que no se muestre de nuevo si el usuario navega sin recargar
        // phpExitoMessageElement.value = ''; 
    }

    if (phpErrorMessageElement && phpErrorMessageElement.value && modalErrorInstance) {
        const mensajeErrorBody = modalErrorElement.querySelector('#mensajeError'); // ID del cuerpo del modal de error
        if (mensajeErrorBody) {
            mensajeErrorBody.textContent = phpErrorMessageElement.value;
        }
        modalErrorInstance.show();
        // Opcional: Limpiar el valor
        // phpErrorMessageElement.value = '';
    }
});
