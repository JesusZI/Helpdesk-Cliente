/*=============================================
CARGAR LA TABLA DINÁMICA DE HISTORIALES
=============================================*/


$(document).ready(function() {
  const tableName = '.datatables-historiales-list';
  const dt_historial = $(tableName).DataTable({
    "ajax": {
      "url": "ajax/historiales.ajax.php",
      "type": "POST",
      "data": {
        "accion": "mostrarHistoriales"
      },
      "dataSrc": "data"
    },
    "columns": [
     
      { 
        "data": null,
        "defaultContent": "",
        "orderable": false,
        "searchable": false,
        "responsivePriority": 2,
        "className": "control"
      },
      { 
        "data": "ticket_info",
        "render": function(data, type, full, meta) {
          return `
            <div class="d-flex align-items-center">
              <div class="d-flex flex-column justify-content-center">
                <span class="fw-medium">#${full.ticket_id} - ${full.ticket_titulo || 'Sin título'}</span>
                <small class="text-muted">${full.fecha}</small>
              </div>
            </div>`;
        }
      },
      { 
        "data": "usuario_nombre",
        "render": function(data, type, full, meta) {
          return `<span>${data}</span>`;
        }
      },
      { 
        "data": "accion",
        "render": function(data, type, full, meta) {
          let badgeClass = '';
          
          if (data.includes('creó')) {
            badgeClass = 'bg-success';
          } else if (data.includes('actualizó')) {
            badgeClass = 'bg-info';
          } else if (data.includes('comentó')) {
            badgeClass = 'bg-primary';
          } else if (data.includes('cerró')) {
            badgeClass = 'bg-secondary';
          } else {
            badgeClass = 'bg-warning';
          }
          
          return `<span class="badge ${badgeClass}">${data}</span>`;
        }
      },
      { 
        "data": "detalles",
        "render": function(data, type, full, meta) {
          if (!data) return '<span>Sin detalles</span>';
          
        
          if (data.length > 80) {
            return `<span title="${data}">${data.substring(0, 80)}...</span>`;
          }
          return `<span>${data}</span>`;
        }
      },
      { 
        "data": "actions", 
        "orderable": false,
        "render": function(data, type, full, meta) {
          return `
          <div class="d-flex align-items-center">
            <a href="index.php?ruta=consultar-ticket&idTicket=${full.ticket_id}" 
               class="btn btn-sm btn-icon btn-info me-2" title="Ver ticket">
              <i class="bx bx-show"></i>
            </a>
            <button type="button" class="btn btn-sm btn-icon btn-primary btnVerDetallesHistorial"
                    data-bs-toggle="modal" data-bs-target="#detallesHistorialModal"
                    data-historial='${JSON.stringify(full)}' title="Ver detalles">
              <i class="bx bx-info-circle"></i>
            </button>
          </div>`;
        }
      }
    ],
    "order": [[1, 'desc']], 
    "dom": '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "responsive": {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function(row) {
            const data = row.data();
            return 'Detalle del Historial';
          }
        }),
        type: 'column',
        renderer: function(api, rowIdx, columns) {
          const data = columns
            .map(function(col) {
              return col.title !== '' && col.title !== undefined ? 
                `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                  <td>${col.title}:</td>
                  <td>${col.data}</td>
                </tr>` : '';
            })
            .join('');

          return data ? 
            `<div class="table-responsive">
              <table class="table">
                <tbody>${data}</tbody>
              </table>
            </div>` : false;
        }
      }
    },
    "language": {
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ningún dato disponible en esta tabla",
      "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
      "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sInfoPostFix": "",
      "sSearch": "Buscar:",
      "sUrl": "",
      "sInfoThousands": ",",
      "sLoadingRecords": "Cargando...",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
      },
      "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
      }
    }
  });

  
  setTimeout(function() {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-search', classToAdd: 'mb-0 mb-md-6' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2', classToAdd: 'border-top' },
      { selector: '.dt-layout-start', classToAdd: 'px-3 mt-0' },
      { selector: '.dt-layout-end', classToAdd: 'px-3 column-gap-2 mt-0 mb-md-0 mb-4' },
      { selector: '.dt-layout-full', classToAdd: 'table-responsive' }
    ];

    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
  
  
  $(document).on('click', '.btnVerDetallesHistorial', function() {
    const historialData = $(this).data('historial');
    
   
    $('#modalHistorialTicketId').text('#' + historialData.ticket_id);
    $('#modalHistorialTicketTitulo').text(historialData.ticket_titulo || 'Sin título');
    $('#modalHistorialUsuario').text(historialData.usuario_nombre);
    $('#modalHistorialAccion').text(historialData.accion);
    $('#modalHistorialFecha').text(historialData.fecha);
    $('#modalHistorialDetalles').html(historialData.detalles || '<em>Sin detalles disponibles</em>');
  });
});

 
  function gestionarBackdrop() {
    
    const backdrops = document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop');
    
    
    if (backdrops.length > 1) {
      for (let i = 0; i < backdrops.length - 1; i++) {
        backdrops[i].remove();
      }
    }
  }

  
  $(document).on('hidden.bs.modal', function () {
    gestionarBackdrop();
  });

  $(document).on('hidden.bs.offcanvas', function () {
    gestionarBackdrop();
  });

  $(document).on('shown.bs.modal', function () {
    gestionarBackdrop();
  });

  $(document).on('shown.bs.offcanvas', function (e) {
    gestionarBackdrop();
    
   
    const offcanvasElement = e.target;
    if (offcanvasElement) {
      offcanvasElement.style.zIndex = '1080';
    }
  });