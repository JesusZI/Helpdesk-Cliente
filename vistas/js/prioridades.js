/*=============================================
CARGAR LA TABLA DINÁMICA DE PRIORIDADES
=============================================*/


$(document).ready(function() {
  const tableName = '.datatables-prioridades-list';
  const dt_prioridad = $(tableName).DataTable({
    "ajax": {
      "url": "ajax/prioridades.ajax.php",
      "type": "POST",
      "data": {
        "accion": "mostrarPrioridades"
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
      { "data": "nombre" },
      { "data": "tiempo_respuesta" },
      { "data": "color" },
      { "data": "actions", "orderable": false }
    ],
    "order": [[1, 'asc']],
    "dom": '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "buttons": [
      {
        text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Agregar Prioridad</span>',
        className: 'add-new btn btn-primary mx-3',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasEcommercePrioridadList'
        }
      }
    ],
    "responsive": {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function(row) {
            const data = row.data();
            return 'Detalles de Prioridad';
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
    },
    "columnDefs": [
      {
        "targets": 2,
        "render": function(data, type, full, meta) {
          return '<div class="text-sm-end">' + data + '</div>';
        }
      },
      {
        "targets": 3,
        "render": function(data, type, full, meta) {
          return '<div class="text-sm-end"><span class="badge" style="background-color: ' + data + ';">' + data + '</span></div>';
        }
      }
    ]
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
  EDITAR PRIORIDAD
  =============================================*/
  $(document).on('click', '.btnEditarPrioridad', function() {
    const idPrioridad = $(this).attr('idPrioridad');
    
    
    const $modal = $(this).closest('.dtr-bs-modal');
    if ($modal.length) {
      $modal.modal('hide');
      $('.modal-backdrop').remove();
    }
    
   
    $.ajax({
      url: 'ajax/prioridades.ajax.php',
      method: 'POST',
      data: {
        idPrioridad: idPrioridad
      },
      dataType: 'json',
      success: function(respuesta) {
        if (respuesta) {
          $('#idPrioridad').val(respuesta.id);
          $('#editarPrioridad').val(respuesta.nombre);
          $('#editarTiempoRespuesta').val(respuesta.tiempo_respuesta);
          $('#editarColor').val(respuesta.color);
        }
      },
      error: function(error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Hubo un error al cargar los datos de la prioridad',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  /*=============================================
  ELIMINAR PRIORIDAD
  =============================================*/
  $(document).on('click', '.btnEliminarPrioridad', function() {
    const idPrioridad = $(this).attr('idPrioridad');
    
    Swal.fire({
      title: '¿Está seguro de borrar la prioridad?',
      text: "¡Esta acción no se puede revertir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar prioridad!'
    }).then((result) => {
      if (result.isConfirmed) {
      
        const $modal = $(this).closest('.dtr-bs-modal');
        if ($modal.length) {
          $modal.modal('hide');
        }
        
       
        window.location = 'index.php?ruta=prioridades&idPrioridad=' + idPrioridad;
      }
    });
  });

  /*=============================================
  VALIDAR NO REPETIR PRIORIDAD
  =============================================*/
  $("#ecommerce-prioridad-title").change(function() {
    $('.alert').remove();
    
    const prioridad = $(this).val();
    
   
    $.ajax({
      url: 'ajax/prioridades.ajax.php',
      method: 'POST',
      data: {
        validarPrioridad: prioridad
      },
      dataType: 'json',
      success: function(respuesta) {
        if(respuesta) {
          Swal.fire({
            icon: 'error',
            title: 'La prioridad ya existe en la base de datos',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
          });
          
          $("#ecommerce-prioridad-title").val('');
        }
      }
    });
  });

  /*=============================================
  MANEJAR FORMULARIO DE NUEVA PRIORIDAD
  =============================================*/
  let isSubmittingPrioridad = false;

  $('#eCommercePrioridadListForm').submit(function(e) {
    e.preventDefault();
    
 
    if (isSubmittingPrioridad) return;
    isSubmittingPrioridad = true;
    
  
    if ($("#ecommerce-prioridad-title").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'El nombre de la prioridad no puede ir vacío',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingPrioridad = false;
      return;
    }
    
    if ($("#ecommerce-tiempo-respuesta").val() === "" || 
        isNaN($("#ecommerce-tiempo-respuesta").val()) || 
        parseInt($("#ecommerce-tiempo-respuesta").val()) <= 0) {
      Swal.fire({
        icon: 'error',
        title: 'El tiempo de respuesta debe ser un número mayor a 0',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingPrioridad = false;
      return;
    }
    
   
    $('.data-submit').prop('disabled', true);
    $('.data-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    
   
    const formData = new FormData();
    formData.append("nuevaPrioridad", $("#ecommerce-prioridad-title").val());
    formData.append("nuevoTiempoRespuesta", $("#ecommerce-tiempo-respuesta").val());
    formData.append("nuevoColor", $("#html5-color-input").val());
    
   
    $.ajax({
      url: 'ajax/prioridades.ajax.php',
      method: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(respuesta) {
       
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Agregar');
        isSubmittingPrioridad = false;
        
        if(respuesta === "ok") {
         
          const offcanvas = document.getElementById('offcanvasEcommercePrioridadList');
          const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
          if (bsOffcanvas) {
            bsOffcanvas.hide();
          }
          
         
          $('#eCommercePrioridadListForm').trigger('reset');
          
         
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Prioridad creada correctamente',
            confirmButtonText: 'Aceptar'
          }).then(() => {
           
            dt_prioridad.ajax.reload();
          });
        } else {
          console.error('Respuesta del servidor:', respuesta);
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: 'Error al crear la prioridad: ' + respuesta,
            confirmButtonText: 'Aceptar'
          });
        }
      },
      error: function(xhr, status, error) {
      
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Agregar');
        isSubmittingPrioridad = false;
        
        console.error('Error:', error);
        console.error('Estado:', status);
        console.error('Respuesta:', xhr.responseText);
        
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Hubo un problema al procesar la solicitud',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

 
  $('.data-submit').on('click', function(e) {
    e.preventDefault();
    if (!isSubmittingPrioridad) {
      $('#eCommercePrioridadListForm').submit();
    }
  });

  /*=============================================
  MANEJAR FORMULARIO DE EDITAR PRIORIDAD
  =============================================*/
  let isSubmittingEdicionPrioridad = false;
  
  $('#editarPrioridadForm').submit(function(e) {
    e.preventDefault();
    
   
    if (isSubmittingEdicionPrioridad) return;
    isSubmittingEdicionPrioridad = true;
    
   
    if ($("#editarPrioridad").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'El nombre de la prioridad no puede ir vacío',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingEdicionPrioridad = false;
      return;
    }
    
    if ($("#editarTiempoRespuesta").val() === "" || 
        isNaN($("#editarTiempoRespuesta").val()) || 
        parseInt($("#editarTiempoRespuesta").val()) <= 0) {
      Swal.fire({
        icon: 'error',
        title: 'El tiempo de respuesta debe ser un número mayor a 0',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingEdicionPrioridad = false;
      return;
    }
    
    
    $('#editarPrioridadForm button[type="submit"]').prop('disabled', true);
    $('#editarPrioridadForm button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    
   
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
});
