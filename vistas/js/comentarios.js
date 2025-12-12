/*=============================================
CARGAR COMENTARIOS
=============================================*/
$(document).ready(function() {
 
  if ($("#comentarios-lista").length > 0) {
    cargarComentarios();
  }

 
  $("#formAgregarComentario").on("submit", function(e) {
    e.preventDefault();
    
    const ticketId = $("input[name='ticketId']").val();
    const usuarioId = $("input[name='usuarioId']").val();
    const contenido = $("#contenidoComentario").val();
    const esPrivado = $("#esPrivado").is(":checked") ? 1 : 0;
    
    if (!contenido.trim()) {
      Swal.fire({
        icon: 'error',
        title: 'El comentario no puede estar vacío',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      return;
    }
    
   
    if (!usuarioId) {
      console.error('Usuario ID no encontrado:', usuarioId);
      Swal.fire({
        icon: 'error',
        title: 'Error de autenticación',
        text: 'No se pudo identificar el usuario. Intente iniciar sesión nuevamente.',
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
      });
      return;
    }
    
    
    const archivoInput = document.getElementById('archivoComentario');
    if (archivoInput.files.length > 0) {
      const archivo = archivoInput.files[0];
      const maxSize = 5 * 1024 * 1024; // 5MB en bytes
      const extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
      const extension = archivo.name.split('.').pop().toLowerCase();
      
     
      if (archivo.size > maxSize) {
        Swal.fire({
          icon: 'error',
          title: 'Archivo demasiado grande',
          text: 'El archivo debe ser menor a 5MB',
          showConfirmButton: true,
          confirmButtonText: 'Cerrar'
        });
        return;
      }
      
      
      if (!extensionesPermitidas.includes(extension)) {
        Swal.fire({
          icon: 'error',
          title: 'Formato de archivo no permitido',
          text: 'Los formatos permitidos son: ' + extensionesPermitidas.join(', '),
          showConfirmButton: true,
          confirmButtonText: 'Cerrar'
        });
        return;
      }
    }
    
   
    $("#formAgregarComentario button[type='submit']").prop('disabled', true);
    $("#formAgregarComentario button[type='submit']").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
    
   
    const formData = new FormData(this);
    
   
    console.log('Enviando comentario para ticket:', ticketId);
    console.log('Usuario ID:', usuarioId);
    console.log('Es privado:', esPrivado);
    
    $.ajax({
      url: "ajax/comentarios.ajax.php",
      method: "POST",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(respuesta) {
        console.log('Respuesta del servidor:', respuesta);
        
        
        $("#formAgregarComentario button[type='submit']").prop('disabled', false);
        $("#formAgregarComentario button[type='submit']").html('Agregar Comentario');
        
        if (respuesta === "ok") {
        
          $("#contenidoComentario").val("");
          $("#archivoComentario").val("");
          $("#esPrivado").prop("checked", false);
          $("#filePreview").addClass('d-none');
          
        
          cargarComentarios();
          
         
          Swal.fire({
            icon: 'success',
            title: 'Comentario agregado correctamente',
            showConfirmButton: false,
            timer: 1500
          });
        } else {
          console.error('Error al guardar comentario:', respuesta);
          Swal.fire({
            icon: 'error',
            title: 'Error al guardar el comentario',
            text: 'Por favor, intente nuevamente: ' + respuesta,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
          });
        }
      },
      error: function(xhr, status, error) {
     
        $("#formAgregarComentario button[type='submit']").prop('disabled', false);
        $("#formAgregarComentario button[type='submit']").html('Agregar Comentario');
        
        console.error('Error AJAX:', error);
        console.error('Estado:', status);
        console.error('Respuesta:', xhr.responseText);
        
        Swal.fire({
          icon: 'error',
          title: 'Error en la comunicación con el servidor',
          text: 'Por favor, intente nuevamente. Detalles: ' + error,
          showConfirmButton: true,
          confirmButtonText: 'Cerrar'
        });
      }
    });
  });
});

/*=============================================
FUNCIÓN PARA CARGAR COMENTARIOS
=============================================*/
function cargarComentarios() {
  const ticketId = $("input[name='ticketId']").val();
  
  if (!ticketId) {
    console.error('ID del ticket no encontrado');
    $("#comentarios-lista").html('<div class="alert alert-danger">Error: ID del ticket no encontrado</div>');
    return;
  }
  
  console.log('Cargando comentarios para ticket:', ticketId);
  
  
  $("#comentarios-lista").html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2">Cargando comentarios...</p></div>');
  
  $.ajax({
    url: "ajax/comentarios.ajax.php",
    method: "POST",
    data: {
      ticketId: ticketId,
      accion: "mostrarComentarios" 
    },
    dataType: "json",
    success: function(respuesta) {
      console.log('Respuesta de comentarios:', respuesta);
      
      if (respuesta && respuesta.error) {
        $("#comentarios-lista").html(`<div class="alert alert-danger">${respuesta.error}</div>`);
        return;
      }
      
      if (!respuesta || respuesta.length === 0) {
        $("#comentarios-lista").html('<div class="alert alert-info">No hay comentarios para este ticket.</div>');
        return;
      }
      
      let html = '';
      
      respuesta.forEach(function(comentario) {
       
        const esPrivadoClase = comentario.es_privado == 1 ? 'border-warning bg-warning-subtle' : '';
        const esPrivadoEtiqueta = comentario.es_privado == 1 ? '<span class="badge bg-warning ms-2">Privado</span>' : '';
        
       
        const fechaFormateada = comentario.fecha_creacion_formateada || comentario.fecha_creacion || 'Fecha desconocida';
        
        html += `
          <div class="card mb-3 ${esPrivadoClase}">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <strong>${comentario.nombre_usuario || 'Usuario'}</strong> ${esPrivadoEtiqueta}
              </div>
              <small>${fechaFormateada}</small>
            </div>
            <div class="card-body">
              <p class="card-text">${comentario.contenido}</p>`;
        
       
        if (comentario.archivos && comentario.archivos.length > 0) {
          html += `<div class="mt-3 border-top pt-2">
                    <h6><i class="bx bx-paperclip me-1"></i>Archivos adjuntos:</h6>
                    <div class="list-group">`;
          
          comentario.archivos.forEach(function(archivo) {
           
            let icono = 'bx-file';
            const extension = archivo.nombre.split('.').pop().toLowerCase();
            
            if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
              icono = 'bx-image';
            } else if (['pdf'].includes(extension)) {
              icono = 'bx-file-pdf';
            } else if (['doc', 'docx'].includes(extension)) {
              icono = 'bx-file-doc';
            } else if (['xls', 'xlsx'].includes(extension)) {
              icono = 'bx-file-spreadsheet';
            }
            
           
            const rutaArchivo = archivo.ruta;
            
           
            if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
              html += `
                <div class="list-group-item">
                  <div class="d-flex align-items-center mb-2">
                    <i class="bx ${icono} fs-4 me-2"></i>
                    <div>
                      <div class="text-body">${archivo.nombre}</div>
                      <div class="text-muted small">${obtenerTamanoFormateado(archivo.tamano || 0)}</div>
                    </div>
                  </div>
                  <div class="mt-2 text-center">
                    <a href="${rutaArchivo}" target="_blank">
                      <img src="${rutaArchivo}" class="img-fluid img-thumbnail" style="max-height: 200px;" alt="${archivo.nombre}">
                    </a>
                  </div>
                </div>`;
            } else {
              html += `
                <a href="${rutaArchivo}" class="list-group-item list-group-item-action d-flex align-items-center" target="_blank">
                  <i class="bx ${icono} fs-4 me-2"></i>
                  <div>
                    <div class="text-body">${archivo.nombre}</div>
                    <div class="text-muted small">${obtenerTamanoFormateado(archivo.tamano || 0)}</div>
                  </div>
                </a>`;
            }
          });
          
          html += `</div></div>`;
        }
        
        html += `</div>
          </div>`;
      });
      
      $("#comentarios-lista").html(html);
    },
    error: function(xhr, status, error) {
      console.error('Error al cargar comentarios:', error);
      console.error('Estado:', status);
      console.error('Respuesta:', xhr.responseText);
      
      let errorMessage = 'Error al cargar los comentarios. Por favor, recargue la página.';
      try {
    
        const responseText = xhr.responseText;
        if (responseText && responseText.includes('<b>')) {
        
          errorMessage += '<br>El servidor reportó un error. Revise los logs del servidor.';
        } else {
          errorMessage += '<br>Detalles: ' + error;
        }
      } catch (e) {
        errorMessage += '<br>Detalles: Error desconocido';
      }
      
      $("#comentarios-lista").html('<div class="alert alert-danger">' + errorMessage + '</div>');
    }
  });
}

/*=============================================
FORMATEAR TAMAÑO DE ARCHIVO EN KB, MB, ETC.
=============================================*/
function obtenerTamanoFormateado(bytes) {
  if (!bytes) return "Desconocido";
  
  const kb = bytes / 1024;
  if (kb < 1024) {
    return kb.toFixed(2) + " KB";
  }
  
  const mb = kb / 1024;
  if (mb < 1024) {
    return mb.toFixed(2) + " MB";
  }
  
  const gb = mb / 1024;
  return gb.toFixed(2) + " GB";
}

 
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