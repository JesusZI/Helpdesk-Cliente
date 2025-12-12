/**
 * Funciones comunes reutilizables para la aplicación
 */

// Inicialización de Select2 con iconos
function initializeSelect2Icons(selector) {
  if (typeof $.fn.select2 === 'function') {
    $(selector).select2({
      templateResult: function (state) {
        if (!state.id) {
          return state.text;
        }
        var $state = $(
          '<span><i class="' + $(state.element).data('icon') + '"></i> ' + state.text + '</span>'
        );
        return $state;
      },
      templateSelection: function (state) {
        if (!state.id) {
          return state.text;
        }
        return $(
          '<span><i class="' + $(state.element).data('icon') + '"></i> ' + state.text + '</span>'
        );
      },
      escapeMarkup: function (markup) {
        return markup;
      }
    });
  } else {
    console.error('Select2 no está cargado correctamente.');
  }
}

// Inicialización de Quill Editor
function initializeQuill(selector, options = {}) {
  if (typeof Quill === 'function') {
    const defaultOptions = {
      theme: 'snow',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }]
        ]
      },
      placeholder: 'Escriba aquí...'
    };
    
    const mergedOptions = {...defaultOptions, ...options};
    return new Quill(selector, mergedOptions);
  } else {
    console.error('Quill no está cargado correctamente.');
    return null;
  }
}

// Mostrar alertas con SweetAlert2
function showAlert(title, message, type, callback) {
  if (typeof Swal === 'function') {
    Swal.fire({
      title: title,
      text: message,
      icon: type,
      confirmButtonText: 'Aceptar'
    }).then(callback || function(){});
  } else {
    alert(title + ': ' + message);
    if (callback) callback({value: true});
  }
}

// Función para serializar formularios incluyendo contenido de editores Quill
function serializeFormWithQuill(formId, quillSelectors = {}) {
  const formData = {};
  const form = document.getElementById(formId);
  
  if (!form) return formData;
  
  // Serializar campos regulares
  const formElements = form.elements;
  for (let i = 0; i < formElements.length; i++) {
    const element = formElements[i];
    if (element.name && element.name !== '') {
      formData[element.name] = element.value;
    }
  }
  
  // Agregar contenido de editores Quill
  for (const [key, selector] of Object.entries(quillSelectors)) {
    const editor = document.querySelector(selector);
    if (editor && editor.querySelector('.ql-editor')) {
      formData[key] = editor.querySelector('.ql-editor').innerHTML;
    }
  }
  
  return formData;
}

// Función de utilidad para el manejo de errores
function manejarError(error, mensaje) {
  console.error(mensaje, error);
  if (typeof Swal === 'function') {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: mensaje
    });
  } else {
    alert(mensaje);
  }
}

// Verificar si una librería está cargada
function verificarLibreria(nombre, objeto) {
  if (typeof objeto === 'undefined') {
    console.error(`La librería ${nombre} no está disponible`);
    return false;
  }
  return true;
}

// Inicialización segura de Select2
function inicializarSelect2Seguro(selector, opciones = {}) {
  try {
    if (!verificarLibreria('jQuery', $)) return;
    if (!verificarLibreria('Select2', $.fn.select2)) return;
    
    $(selector).select2(opciones);
    return true;
  } catch (e) {
    manejarError(e, `Error al inicializar Select2 en ${selector}`);
    return false;
  }
}

// Inicialización segura de Quill
function inicializarQuillSeguro(selector, opciones = {}) {
  try {
    if (!verificarLibreria('Quill', Quill)) return;
    
    const elemento = document.querySelector(selector);
    if (!elemento) {
      console.error(`Elemento no encontrado: ${selector}`);
      return null;
    }
    
    return new Quill(elemento, opciones);
  } catch (e) {
    manejarError(e, `Error al inicializar Quill en ${selector}`);
    return null;
  }
}

// Realizar petición AJAX segura
function ajaxSeguro(opciones) {
  try {
    if (!verificarLibreria('jQuery', $)) return;
    
    const opcionesPorDefecto = {
      error: function(xhr, status, error) {
        manejarError(error, `Error en la solicitud AJAX: ${status}`);
      }
    };
    
    $.ajax({...opcionesPorDefecto, ...opciones});
  } catch (e) {
    manejarError(e, 'Error al realizar la petición AJAX');
  }
}

// Función para obtener contenido HTML de un editor Quill
function obtenerContenidoQuill(quill) {
  if (!quill) return '';
  return quill.root.innerHTML;
}

// Función para establecer contenido HTML en un editor Quill
function establecerContenidoQuill(quill, contenido) {
  if (!quill) return false;
  quill.root.innerHTML = contenido || '';
  return true;
}

// Inicializar componentes al cargar el documento
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar todos los select2 con clase select2-icons
  $('.select2-icons').each(function() {
    initializeSelect2Icons(this);
  });
});
