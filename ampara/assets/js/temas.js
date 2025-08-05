document.addEventListener('DOMContentLoaded', function () {
    // DataTable initialization
    const temasTable = document.getElementById('tablaTemas');
    if (temasTable) {
        new DataTable(temasTable, {
            language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
            responsive: true,
            order: [[0, 'desc']]
        });
    }

    // Modal logic for add/edit
    const temaModal = document.getElementById('temaModal');
    if (temaModal) {
        temaModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const temaId = button.getAttribute('data-id');

            const modalTitle = temaModal.querySelector('.modal-title');
            const form = temaModal.querySelector('form');
            const idInput = form.querySelector('#idtema');
            const accionInput = form.querySelector('#accion');
            const descripcionInput = form.querySelector('#descripcion');
            const idencargadoInput = form.querySelector('#idencargado');
            const comentarioInput = form.querySelector('#comentario');
            const activoInput = form.querySelector('#activo');

            if (temaId && temaId !== '0') {
                // Editing existing tema
                modalTitle.textContent = 'Editar Tema';
                accionInput.value = 'editar';
                idInput.value = temaId;
                descripcionInput.value = button.getAttribute('data-descripcion');
                idencargadoInput.value = button.getAttribute('data-idencargado');
                comentarioInput.value = button.getAttribute('data-comentario');
                activoInput.checked = button.getAttribute('data-activo') === '1';
            } else {
                // Adding new tema
                modalTitle.textContent = 'Agregar Tema';
                accionInput.value = 'registrar';
                form.reset(); // Clear form fields
                idInput.value = '';
                activoInput.checked = true;
            }
        });
    }
});
