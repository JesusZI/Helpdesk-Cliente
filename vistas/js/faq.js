/*=============================================
CARGAR LA TABLA DINÁMICA DE PREGUNTAS FRECUENTES
=============================================*/


$(document).ready(function() {
  $('.datatables-faq-list').DataTable({
    "ajax": {
      "url": "ajax/faq.ajax.php",
      "type": "POST",
      "data": {
        "accion": "mostrarFaqs"
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
                <span class="text-heading text-wrap fw-medium">${full.question}</span>
                <span class="text-truncate mb-0 d-none d-sm-block"><small>${full.category_name || ''}</small></span>
              </div>
            </div>`;
        }
      },
      { 
        "data": "answer",
        "render": function(data, type, full, meta) {
          return '<div class="text-wrap">' + data.substring(0, 100) + (data.length > 100 ? '...' : '') + '</div>';
        }
      },
      { "data": "actions", "orderable": false }
    ],
    "order": [[0, 'asc']], 
    "dom": '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "buttons": [
      {
        text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Agregar FAQ</span>',
        className: 'add-new btn btn-primary mx-3',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasEcommerceFaqList'
        }
      }
    ],
    "responsive": {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function(row) {
            const data = row.data();
            return 'Detalles de ' + data.question;
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
EDITAR FAQ
=============================================*/
$(".datatables-faq-list").on("click", ".btnEditarFaq", function(){
  var idFaq = $(this).attr("idFaq");
  
  var datos = new FormData();
  datos.append("idFaq", idFaq);
  
  $.ajax({
    url: "ajax/faq.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function(respuesta){
      $("#idFaq").val(respuesta["id"]);
      $("#editarPregunta").val(respuesta["pregunta"]);
      $("#editarRespuesta").val(respuesta["respuesta"]);
      $("#editarCategoriaFaq").val(respuesta["categoria_id"]);
    }
  });
});

/*=============================================
ELIMINAR FAQ
=============================================*/
$(".datatables-faq-list").on("click", ".btnEliminarFaq", function(){
  var idFaq = $(this).attr("idFaq");
  
  Swal.fire({
    title: '¿Está seguro de borrar esta pregunta frecuente?',
    text: '¡Si no lo está puede cancelar la acción!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Si, borrar FAQ!'
  }).then(function(result){
    if(result.value){
      window.location = 'index.php?ruta=faq&idFaq='+idFaq;
    }
  });
});

/*=============================================
VALIDAR PREGUNTA EXISTENTE
=============================================*/
$("#faq-question").change(function(){
  $(".alert").remove();
  
  var pregunta = $(this).val();
  
  var datos = new FormData();
  datos.append("validarPregunta", pregunta);
  
  $.ajax({
    url: "ajax/faq.ajax.php",
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
          title: "La pregunta ya existe en la base de datos",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        
        $("#faq-question").val("");
      }
    }
  });
});

/*=============================================
ENVIAR FORMULARIO DE NUEVA FAQ
=============================================*/
$(document).ready(function() {
 
  let faqEditor;
  let isSubmitting = false; 
  
 
  if (document.getElementById('faq-answer')) {
    faqEditor = new Quill('#faq-answer', {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Escriba la respuesta...',
      theme: 'snow'
    });
  }
  
 
  $('#eCommerceFaqListForm').on('submit', function(e) {
    e.preventDefault();
    
    
    if (isSubmitting) return;
    isSubmitting = true;
    
    const pregunta = $('#faq-question').val();
    let respuesta = '';
    
    
    if (faqEditor) {
      respuesta = faqEditor.root.innerHTML;
    }
    
    const categoria = $('#nuevaCategoriaFaq').val();
    
    if (pregunta === '') {
      Swal.fire({
        icon: 'error',
        title: 'La pregunta no puede estar vacía',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      isSubmitting = false;
      return;
    }
    
    
    $('.data-submit').prop('disabled', true);
    $('.data-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    
    const datos = new FormData();
    datos.append('nuevaPregunta', pregunta);
    datos.append('nuevaRespuesta', respuesta);
    datos.append('nuevaCategoriaFaq', categoria);
    
    $.ajax({
      url: 'ajax/faq.ajax.php',
      method: 'POST',
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      success: function(respuesta) {
        
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Agregar');
        isSubmitting = false;
        
        if (respuesta === 'ok') {
          Swal.fire({
            icon: 'success',
            title: '¡La pregunta frecuente ha sido guardada correctamente!',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
          }).then(function(result) {
            if (result.value) {
             
              const offcanvas = document.getElementById('offcanvasEcommerceFaqList');
              const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
              bsOffcanvas.hide();
              
              
              $('.datatables-faq-list').DataTable().ajax.reload();
              
              
              $('#eCommerceFaqListForm').trigger('reset');
              if (faqEditor) {
                faqEditor.root.innerHTML = '';
              }
            }
          });
        } else if (respuesta === 'error-validacion') {
          Swal.fire({
            icon: 'error',
            title: 'Error en los datos del formulario',
            text: 'La pregunta no puede contener caracteres especiales',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
          });
        } else {
          console.error('Error al guardar:', respuesta);
          Swal.fire({
            icon: 'error',
            title: 'Error al guardar la pregunta',
            text: 'Por favor, intente nuevamente',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
          });
        }
      },
      error: function(xhr, status, error) {
        
        $('.data-submit').prop('disabled', false);
        $('.data-submit').html('Agregar');
        isSubmitting = false;
        
        console.error('Error AJAX:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error en la comunicación con el servidor',
          text: 'Por favor, intente nuevamente',
          showConfirmButton: true,
          confirmButtonText: 'Cerrar'
        });
      }
    });
  });
  
 
  $('.data-submit').on('click', function(e) {
    e.preventDefault();
    
    if (!isSubmitting) {
      $('#eCommerceFaqListForm').submit();
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