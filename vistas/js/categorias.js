/*=============================================
CARGAR LA TABLA DINÁMICA DE CATEGORÍAS
=============================================*/


$(document).ready(function() {
  $('.datatables-category-list').DataTable({
    "ajax": {
      "url": "ajax/categorias.ajax.php",
      "type": "POST",
      "data": {
        "accion": "mostrarCategorias"
      },
      "dataSrc": "data"
    },
    "columns": [
      { 
        "data": null, 
        "render": function(data, type, row, meta) {
         
          return meta.row + 1;
        }
      },
      { 
        "data": null,
        "render": function(data, type, full, meta) {
          return `
            <div class="d-flex align-items-center">
              <div class="d-flex flex-column justify-content-center">
                <span class="text-heading text-wrap fw-medium">${full.categories}</span>
                <span class="text-truncate mb-0 d-none d-sm-block"><small>${full.category_detail || ''}</small></span>
              </div>
            </div>`;
        }
      },
      { 
        "data": "total_products",
        "render": function(data, type, full, meta) {
          return '<div class="text-sm-end"><span class="badge" style="background-color: ' + data + ';">' + data + '</span></div>';
        }
      },
      { 
        "data": "total_earnings",
        "render": function(data, type, full, meta) {
          return "<div class='mb-0 text-sm-end'><i class='" + data + "'></i></div>";
        }
      },
      { "data": "actions", "orderable": false }
    ],
    "order": [[0, 'asc']], 
    "dom": '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "buttons": [
      {
        text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Agregar Categoría</span>',
        className: 'add-new btn btn-primary mx-3',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasEcommerceCategoryList'
        }
      }
    ],
    "responsive": {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function(row) {
            const data = row.data();
            return 'Detalles de ' + data.categories;
          }
        }),
        type: 'column',
        renderer: function(api, rowIdx, columns) {
          const data = columns
            .map(function(col) {
              return col.title !== '' ? 
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
});

/*=============================================
AGREGAR CATEGORÍA
=============================================*/
$(document).on('submit', '#eCommerceCategoryListForm', function(e) {
  e.preventDefault();
  
  
  var nombre = $('#ecommerce-category-title').val();
  var descripcion = $('#nuevaDescripcion').val();
  var color = $('#html5-color-input').val();
  var icono = $('#select2Icons').val();
  
  
  if(nombre === '') {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El nombre de la categoría es obligatorio',
      confirmButtonText: 'Cerrar'
    });
    return;
  }
  
  
  var datos = new FormData();
  datos.append('nuevaCategoria', nombre);
  datos.append('nuevaDescripcion', descripcion);
  datos.append('nuevoColor', color);
  datos.append('nuevoIcono', icono);
  
  $.ajax({
    url: 'ajax/categorias.ajax.php',
    method: 'POST',
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function() {
      $('.data-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');
    },
    success: function(response) {
      if(response === 'ok') {
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Categoría creada correctamente',
          confirmButtonText: 'Cerrar'
        }).then(function() {
          $('#offcanvasEcommerceCategoryList').offcanvas('hide');
          $('.datatables-category-list').DataTable().ajax.reload();
        });
      } else if(response === 'error-validacion') {
        Swal.fire({
          icon: 'error',
          title: 'Error de validación',
          text: 'La categoría no puede ir vacía o llevar caracteres especiales',
          confirmButtonText: 'Cerrar'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error al crear la categoría: ' + response,
          confirmButtonText: 'Cerrar'
        });
      }
    },
    error: function() {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error de conexión con el servidor',
        confirmButtonText: 'Cerrar'
      });
    },
    complete: function() {
      $('.data-submit').prop('disabled', false).html('Agregar');
    }
  });
});

/*=============================================
EDITAR CATEGORÍA
=============================================*/
$(".datatables-category-list").on("click", ".btnEditarCategoria", function(){
  var idCategoria = $(this).attr("idCategoria");
  
  var datos = new FormData();
  datos.append("idCategoria", idCategoria);
  
  $.ajax({
    url: "ajax/categorias.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function(respuesta){
      $("#idCategoria").val(respuesta["id"]);
      $("#editarCategoria").val(respuesta["nombre"]);
      $("#editarDescripcion").val(respuesta["descripcion"]);
      $("#editarColor").val(respuesta["color"]);
      $("#editarIcono").val(respuesta["icono"]);
    }
  });
});

/*=============================================
ELIMINAR CATEGORÍA
=============================================*/
$(".datatables-category-list").on("click", ".btnEliminarCategoria", function(){
  var idCategoria = $(this).attr("idCategoria");
  
  Swal.fire({
    title: '¿Está seguro de borrar la categoría?',
    text: '¡Si no lo está puede cancelar la acción!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Si, borrar categoría!'
  }).then(function(result){
    if(result.value){
      window.location = 'index.php?ruta=categorias&idCategoria='+idCategoria;
    }
  });
});

/*=============================================
VALIDAR CATEGORÍA EXISTENTE
=============================================*/
$("#ecommerce-category-title").change(function(){
  $(".alert").remove();
  
  var categoria = $(this).val();
  
  var datos = new FormData();
  datos.append("validarCategoria", categoria);
  
  $.ajax({
    url: "ajax/categorias.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function(respuesta){
      if(respuesta){
        Swal.fire({
          icon: "error",
          title: "La categoría ya existe en la base de datos",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        
        $("#ecommerce-category-title").val("");
      }
    }
  });
});

/*=============================================
LIMPIAR FORMULARIO AL CERRAR OFFCANVAS
=============================================*/
$('#offcanvasEcommerceCategoryList').on('hidden.bs.offcanvas', function () {
  $('#eCommerceCategoryListForm')[0].reset();
  $('#nuevaDescripcion').val('');
  $('#html5-color-input').val('#666EE8');
  $('#select2Icons').val('bx bx-bug').trigger('change');
});

/*=============================================
MANEJAR ENVÍO DEL FORMULARIO DE EDITAR
=============================================*/
$(document).on('submit', '#editarCategoriaForm', function(e) {
  e.preventDefault();
  
  var formData = new FormData(this);
  
  $.ajax({
    url: 'index.php?ruta=categorias',
    method: 'POST',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function() {
      $('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');
    },
    success: function(response) {
    
      location.reload();
    },
    error: function() {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error de conexión con el servidor',
        confirmButtonText: 'Cerrar'
      });
    },
    complete: function() {
      $('button[type="submit"]').prop('disabled', false).html('Guardar cambios');
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