/*=============================================
CARGAR LA TABLA DINÁMICA DE DEPARTAMENTOS
=============================================*/


$(document).ready(function() {
  const tableName = '.datatables-departamentos-list';
  const dt_departamento = $(tableName).DataTable({
    "ajax": {
      "url": "ajax/departamentos.ajax.php",
      "type": "POST",
      "data": {
        "accion": "mostrarDepartamentos"
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
      { "data": "descripcion" },
      { "data": "actions", "orderable": false }
    ],
    "order": [[1, 'asc']],
    "dom": '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "buttons": [
      {
        text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Agregar Departamento</span>',
        className: 'add-new btn btn-primary mx-3',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasEcommerceDepartamentoList'
        }
      }
    ],
    "responsive": {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function(row) {
            const data = row.data();
            return 'Detalles del Departamento: ' + data.nombre;
          }
        }),
        type: 'column',
        renderer: function(api, rowIdx, columns) {
          const data = columns
            .map(function(col) {
              return col.title !== '' && col.title !== undefined ? 
                `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}>
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

  /*=============================================
  EDITAR DEPARTAMENTO
  =============================================*/
  $(document).on('click', '.btnEditarDepartamento', function() {
    const idDepartamento = $(this).attr('idDepartamento');
    
 
    const $modal = $(this).closest('.dtr-bs-modal');
    if ($modal.length) {
      $modal.modal('hide');
      $('.modal-backdrop').remove();
    }
    
  
    $.ajax({
      url: 'ajax/departamentos.ajax.php',
      method: 'POST',
      data: {
        idDepartamento: idDepartamento
      },
      dataType: 'json',
      success: function(respuesta) {
        if (respuesta) {
          $('#idDepartamento').val(respuesta.id);
          $('#editarDepartamento').val(respuesta.nombre);
          $('#editarDescripcion').val(respuesta.descripcion);
        }
      },
      error: function(error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Hubo un error al cargar los datos del departamento',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  /*=============================================
  ELIMINAR DEPARTAMENTO
  =============================================*/
  $(document).on('click', '.btnEliminarDepartamento', function() {
    const idDepartamento = $(this).attr('idDepartamento');
    
    Swal.fire({
      title: '¿Está seguro de borrar este departamento?',
      text: "¡Esta acción no se puede revertir!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar departamento!'
    }).then((result) => {
      if (result.isConfirmed) {
      
        const $modal = $(this).closest('.dtr-bs-modal');
        if ($modal.length) {
          $modal.modal('hide');
        }
        
     
        window.location = 'index.php?ruta=departamentos&idDepartamento=' + idDepartamento;
      }
    });
  });

  /*=============================================
  MANEJAR FORMULARIO DE NUEVO DEPARTAMENTO
  =============================================*/
  let isSubmittingDepartamento = false; 

  $('#eCommerceDepartamentoListForm').submit(function(e) {
    e.preventDefault();
    
   
    if (isSubmittingDepartamento) return;
    isSubmittingDepartamento = true;
    
    
    if ($("#ecommerce-departamento-title").val() === "") {
      Swal.fire({
        icon: 'error',
        title: 'El nombre del departamento no puede ir vacío',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmittingDepartamento = false;
      return;
    }
    
    
    $('.data-submit').prop('disabled', true);
    $('.data-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    
   
    const formData = new FormData();
    formData.append("nuevoDepartamento", $("#ecommerce-departamento-title").val());
    formData.append("nuevaDescripcion", $("#ecommerce-departamento-descripcion").val());
    
    
    $.ajax({
      url: 'ajax/departamentos.ajax.php',
      method: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(respuesta) {
        console.log("Respuesta del servidor:", respuesta);
        
        
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Agregar');
        isSubmittingDepartamento = false;
        
        if(respuesta === "ok") {
        
          const offcanvas = document.getElementById('offcanvasEcommerceDepartamentoList');
          const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
          if (bsOffcanvas) {
            bsOffcanvas.hide();
          }
          
        
          $('#eCommerceDepartamentoListForm').trigger('reset');
          
     
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Departamento creado correctamente',
            confirmButtonText: 'Aceptar'
          }).then(() => {
       
            dt_departamento.ajax.reload();
          });
        } else {
          console.error('Respuesta del servidor:', respuesta);
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: 'Error al crear el departamento. Consulte la consola para más detalles.',
            confirmButtonText: 'Aceptar'
          });
        }
      },
      error: function(xhr, status, error) {
  
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Agregar');
        isSubmittingDepartamento = false;
        
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
    if (!isSubmittingDepartamento) {
      $('#eCommerceDepartamentoListForm').submit();
    }
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