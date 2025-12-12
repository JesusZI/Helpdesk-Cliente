'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var dt_historiales_list_table = document.querySelector('.datatables-historiales-list');

    if (dt_historiales_list_table) {
        var dt_historiales = new DataTable(dt_historiales_list_table, {
            ajax: {
                url: 'ajax/historiales.ajax.php',
                type: 'POST',
                data: function (d) {
                    return { accion: 'mostrarHistoriales' };
                }
            },
            columns: [
                { data: 'id' },
                { data: 'ticket_id' },
                { data: 'usuario_id' },
                { data: 'accion' },
                { data: 'fecha' },
                { data: 'actions' }
            ],
            order: [4, 'desc'],
            language: {
                paginate: {
                    next: '<i class="bx bx-chevron-right"></i>',
                    previous: '<i class="bx bx-chevron-left"></i>'
                }
            }
        });
    }
});
