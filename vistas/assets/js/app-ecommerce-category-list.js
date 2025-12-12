/**
 * App eCommerce Category List
 */

'use strict';

// Comment editor
const commentEditor = document.querySelector('.comment-editor');

if (commentEditor) {
  new Quill(commentEditor, {
    modules: {
      toolbar: '.comment-toolbar'
    },
    placeholder: 'Ingresar Descripción',
    theme: 'snow'
  });
}

// Select2 para dropdowns en offcanvas
document.addEventListener('DOMContentLoaded', function (e) {
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder')
      });
    });
  }

  // Filter form control to default size
  setTimeout(() => {
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

// Validación de formulario
(function () {
  const eCommerceCategoryListForm = document.getElementById('eCommerceCategoryListForm');

  // Validación del formulario para agregar nueva categoría
  if (eCommerceCategoryListForm) {
    const fv = FormValidation.formValidation(eCommerceCategoryListForm, {
      fields: {
        categoryTitle: {
          validators: {
            notEmpty: {
              message: 'Por favor ingrese el título de la categoría'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: 'is-valid',
          rowSelector: '.form-control-validation'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function() {
      // Aquí procesamos el formulario cuando es válido
      var nombre = $("#ecommerce-category-title").val();
      var descripcion = $(".comment-editor .ql-editor").html();
      var color = $("#html5-color-input").val();
      var icono = $("#select2Icons").val();
      
      $.ajax({
        url: "ajax/categorias.ajax.php",
        method: "POST",
        data: {
          nuevaCategoria: nombre,
          nuevaDescripcion: descripcion,
          nuevoColor: color,
          nuevoIcono: icono
        },
        success: function(respuesta) {
          if (respuesta === "ok") {
            Swal.fire({
              icon: "success",
              title: "¡La categoría ha sido guardada correctamente!",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            }).then(function(result) {
              if (result.value) {
                window.location = "categorias";
              }
            });
          }
        }
      });
    });
  }
})();