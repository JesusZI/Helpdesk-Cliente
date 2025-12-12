'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var dt_faq_list_table = document.querySelector('.datatables-faq-list');

    if (dt_faq_list_table) {
        var dt_faq = new DataTable(dt_faq_list_table, {
            ajax: {
                url: 'ajax/faq.ajax.php',
                type: 'POST',
                data: function (d) {
                    return { accion: 'mostrarFAQ' };
                },
                error: function (xhr, error, thrown) {
                    console.error("Error en la respuesta JSON:", xhr.responseText);
                }
            },
            columns: [
                { data: 'id' },
                { data: 'pregunta' },
                { data: 'respuesta' },
                { data: 'categoria' },
                { data: 'actions' }
            ],
            order: [0, 'asc'],
            language: {
                paginate: {
                    next: '<i class="bx bx-chevron-right"></i>',
                    previous: '<i class="bx bx-chevron-left"></i>'
                }
            }
        });
    }
});
