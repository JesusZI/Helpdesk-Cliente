/*=============================================
CARGAR LA TABLA DINÁMICA DE TICKETS
=============================================*/


$(document).ready(function() {
  const tableName = '.datatables-ticket-list';
  const dt_ticket = $(tableName).DataTable({
    "ajax": {
      "url": "ajax/tickets.ajax.php",
      "type": "POST",
      "data": function(d) {
        d.accion = "mostrarTickets";
        d.estado = $('#filtroEstadoTicket').val();
      },
      "dataSrc": function(json) {
        actualizarContadoresEstados(json.estadisticas || {});
        return json.data;
      }
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
      { "data": "titulo" },
      { 
        "data": "descripcion",
        "render": function(data, type, full, meta) {
          return '<div class="text-wrap">' + data.substring(0, 100) + (data.length > 100 ? '...' : '') + '</div>';
        }
      },
      { 
        "data": "estado",
        "render": function(data, type, full, meta) {
          let badgeClass = '';
          switch(data) {
            case 'abierto':
              badgeClass = 'badge bg-primary';
              break;
            case 'en_proceso':
              badgeClass = 'badge bg-warning';
              break;
            case 'resuelto':
              badgeClass = 'badge bg-success';
              break;
            case 'cerrado':
              badgeClass = 'badge bg-secondary';
              break;
            default:
              badgeClass = 'badge bg-info';
          }
          return '<span class="' + badgeClass + '">' + data.replace('_', ' ') + '</span>';
        }
      },
      { "data": "acciones", "orderable": false }
    ],
    "order": [[1, 'asc']],
    "dom": '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "buttons": [
      {
        text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Agregar Ticket</span>',
        className: 'add-new btn btn-primary mx-3',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasEcommerceTicketList'
        }
      }
    ],
    "responsive": {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function(row) {
            const data = row.data();
            return 'Detalles del Ticket: ' + data.titulo;
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

  
  $('#filtroEstadoTicket').on('change', function() {
    dt_ticket.ajax.reload();
  });

  
  function actualizarContadoresEstados(estadisticas) {
    $('#count-abierto').text(estadisticas.abierto || 0);
    $('#count-en_proceso').text(estadisticas.en_proceso || 0);
    $('#count-resuelto').text(estadisticas.resuelto || 0);
    $('#count-cerrado').text(estadisticas.cerrado || 0);
  }

 
  obtenerEstadisticasEstados();

  function obtenerEstadisticasEstados() {
    $.ajax({
      url: 'ajax/tickets.ajax.php',
      method: 'POST',
      data: { accion: 'estadisticasEstados' },
      dataType: 'json',
      success: function(resp) {
        if (resp && resp.estadisticas) {
          actualizarContadoresEstados(resp.estadisticas);
        }
      }
    });
  }

  
  dt_ticket.on('draw', function() {
    obtenerEstadisticasEstados();
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

  /*=============================================
  EDITAR TICKET
  =============================================*/
  $(document).on('click', '.btnEditarTicket', function() {
    const idTicket = $(this).attr('idTicket');
    
    
    const $modal = $(this).closest('.dtr-bs-modal');
    if ($modal.length) {
      $modal.modal('hide');
      $('.modal-backdrop').remove();
    }
    
    
    $.ajax({
      url: 'ajax/tickets.ajax.php',
      method: 'POST',
      data: {
        idTicket: idTicket
      },
      dataType: 'json',
      success: function(respuesta) {
        if (respuesta) {
          $('#idTicket').val(respuesta.id);
          $('#editarTitulo').val(respuesta.titulo);
          $('#editarDescripcion').val(respuesta.descripcion);
          $('#offcanvasEditTicket select[name="tecnicoAsignadoId"]').val(respuesta.tecnico_asignado_id);
          $('#offcanvasEditTicket select[name="categoriaId"]').val(respuesta.categoria_id);
          $('#offcanvasEditTicket select[name="prioridadId"]').val(respuesta.prioridad_id);
          $('#editarEstado').val(respuesta.estado);
        }
      },
      error: function(error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Hubo un error al cargar los datos del ticket',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  /*=============================================
  ELIMINAR TICKET
  =============================================*/
  $(document).on('click', '.btnEliminarTicket', function() {
    const idTicket = $(this).attr('idTicket');
    
    Swal.fire({
      title: '¿Está seguro de borrar este ticket?',
      text: "¡Esta acción no se puede revertir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar ticket!'
    }).then((result) => {
      if (result.isConfirmed) {
       
        const $modal = $(this).closest('.dtr-bs-modal');
        if ($modal.length) {
          $modal.modal('hide');
        }
        
       
        window.location = 'index.php?ruta=tickets&idTicket=' + idTicket;
      }
    });
  });

  /*=============================================
  MANEJAR FORMULARIO DE NUEVO TICKET
  =============================================*/
  let isSubmittingTicket = false; 

  $('#eCommerceTicketListForm').submit(function(e) {
    e.preventDefault();
    console.log("Formulario de ticket enviado");
    
    
    if (isSubmittingTicket) return;
    isSubmittingTicket = true;
    
   
    if ($("#ecommerce-ticket-title").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'El título del ticket no puede ir vacío',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingTicket = false;
      return;
    }
    
    if ($("#ecommerce-ticket-description").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'La descripción del ticket no puede ir vacía',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingTicket = false;
      return;
    }
    
    
    if ($("#tecnicoAsignadoId").val() === "" || 
        $("#categoriaId").val() === "" ||
        $("#prioridadId").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'Todos los campos son obligatorios',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingTicket = false;
      return;
    }
    
    
    $('.data-submit').prop('disabled', true);
    $('.data-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    
    
    if (!$("input[name='usuarioCreadorId']").val()) {
      
      Swal.fire({
        icon: 'error',
        title: 'Error de autenticación',
        text: 'Debe iniciar sesión para crear un ticket',
        confirmButtonText: 'Aceptar'
      });
      $('.data-submit').prop('disabled', false);
      $('.data-submit').html('Guardar');
      isSubmittingTicket = false;
      return;
    }
    
    
    const formData = new FormData();
    formData.append("nuevoTitulo", $("#ecommerce-ticket-title").val());
    formData.append("nuevaDescripcion", $("#ecommerce-ticket-description").val());
    formData.append("usuarioCreadorId", $("input[name='usuarioCreadorId']").val());
    formData.append("tecnicoAsignadoId", $("#tecnicoAsignadoId").val());
    formData.append("categoriaId", $("#categoriaId").val());
    formData.append("prioridadId", $("#prioridadId").val());
    formData.append("estado", $("#estado").val());
    
    console.log("ID del usuario creador:", $("input[name='usuarioCreadorId']").val());
    
   
    $.ajax({
      url: 'ajax/tickets.ajax.php',
      method: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(respuesta) {
        console.log("Respuesta del servidor:", respuesta);
        
        
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Guardar');
        isSubmittingTicket = false;
        
        if(respuesta === "ok") {
       
          const offcanvas = document.getElementById('offcanvasEcommerceTicketList');
          const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
          if (bsOffcanvas) {
            bsOffcanvas.hide();
          }
          
        
          $('#eCommerceTicketListForm').trigger('reset');
          
          
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Ticket creado correctamente',
            confirmButtonText: 'Aceptar'
          }).then(() => {
           
            dt_ticket.ajax.reload();
          });
        } else {
          console.error('Respuesta del servidor:', respuesta);
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: 'Error al crear el ticket. Consulte la consola para más detalles.',
            confirmButtonText: 'Aceptar'
          });
        }
      },
      error: function(xhr, status, error) {
        
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Guardar');
        isSubmittingTicket = false;
        
        console.error('Error:', error);
        console.error('Estado:', status);
        console.error('Respuesta:', xhr.responseText);
        
        let errorMsg = 'Hubo un problema al procesar la solicitud';
        
      
        if (xhr.responseText && xhr.responseText.includes('foreign key constraint fails')) {
          errorMsg = 'Error de referencia: El ID de usuario no existe en la base de datos';
        }
        
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          html: errorMsg + '<br><small>Detalles técnicos disponibles en la consola</small>',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

 
  $('.data-submit').on('click', function(e) {
    e.preventDefault();
    console.log("Botón submit de ticket clickeado");
    if (!isSubmittingTicket) {
      $('#eCommerceTicketListForm').submit();
    }
  });

  /*=============================================
  MANEJAR FORMULARIO DE EDITAR TICKET
  =============================================*/
  let isSubmittingEdicionTicket = false;
  
  $('#editarTicketForm').submit(function(e) {
    e.preventDefault();
    
   
    if (isSubmittingEdicionTicket) return;
    isSubmittingEdicionTicket = true;
    
   
    if ($("#editarTitulo").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'El título del ticket no puede ir vacío',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingEdicionTicket = false;
      return;
    }
    
    if ($("#editarDescripcion").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'La descripción del ticket no puede ir vacía',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingEdicionTicket = false;
      return;
    }
    
  
    if ($("#offcanvasEditTicket select[name='tecnicoAsignadoId']").val() === "" || 
        $("#offcanvasEditTicket select[name='categoriaId']").val() === "" ||
        $("#offcanvasEditTicket select[name='prioridadId']").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'Todos los campos son obligatorios',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingEdicionTicket = false;
      return;
    }
    
   
    $('#editarTicketForm button[type="submit"]').prop('disabled', true);
    $('#editarTicketForm button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    
   
    setTimeout(() => {
      this.submit();
    }, 100);
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

  
  $(document).on('click', '.btnConsultarTicket', function() {
    const idTicket = $(this).attr('idTicket');
    window.location = 'index.php?ruta=consultar-ticket&idTicket=' + idTicket;
  });

  
  function formatAccionesTicket(row) {
    return `<div class="d-flex align-items-center justify-content-center gap-2">
      <a href="index.php?ruta=consultar-ticket&idTicket=${row.id}" class="btn btn-sm btn-icon btn-info" title="Ver detalles">
        <i class="bx bx-search"></i>
      </a>
      <button class="btn btn-sm btn-icon btn-primary btnEditarTicket" idTicket="${row.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditTicket" title="Editar">
        <i class="bx bx-edit"></i>
      </button>
      <button class="btn btn-sm btn-icon btn-danger btnEliminarTicket" idTicket="${row.id}" title="Eliminar">
        <i class="bx bx-trash"></i>
      </button>
    </div>`;
  }
});
