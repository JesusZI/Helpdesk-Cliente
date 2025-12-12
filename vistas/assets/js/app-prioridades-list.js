/**
 * App Prioridades List
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  var dt_prioridades_list_table = document.querySelector('.datatables-prioridades-list');

  // Select2 para dropdowns en offcanvas
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

  // DataTable para Prioridades
  if (dt_prioridades_list_table) {
    var dt_prioridad = new DataTable(dt_prioridades_list_table, {
      ajax: {
        url: 'ajax/prioridades.ajax.php',
        type: 'POST',
        data: function(d) {
          return { accion: 'mostrarPrioridades' }
        }
      },
      columns: [
        // Columnas según los datos devueltos del servidor
        { data: 'id' },
        { data: 'nombre' },
        { data: 'tiempo_respuesta' },
        { data: 'color' },
        { data: 'actions' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 1,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          targets: 1,
          responsivePriority: 2,
          render: function (data, type, full, meta) {
            const name = full['nombre'];
            
            // Crear salida para Prioridades
            const rowOutput = `
              <div class="d-flex align-items-center">
                <div class="d-flex flex-column justify-content-center">
                  <span class="text-heading text-wrap fw-medium">${name}</span>
                </div>
              </div>`;
            return rowOutput;
          }
        },
        {
          // Tiempo de respuesta
          targets: 2,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return '<div class="text-sm-end">' + full['tiempo_respuesta'] + '</div>';
          }
        },
        {
          // Color
          targets: 3,
          orderable: false,
          render: function (data, type, full, meta) {
            const color = full['color'];
            return '<div class="text-sm-end"><span class="badge" style="background-color: ' + color + ';">' + color + '</span></div>';
          }
        },
        {
          // Acciones - Ya está pre-renderizado desde el servidor
          targets: 4,
          title: 'Acciones',
          searchable: false,
          orderable: false
        }
      ],
      order: [1, 'desc'],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Buscar prioridad',
                text: '_INPUT_'
              }
            }
          ]
        },
        topEnd: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: {
            pageLength: {
              menu: [10, 25, 50, 100],
              text: '_MENU_'
            },
            buttons: [
              {
                text: `<i class="icon-base bx bx-plus icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Agregar Prioridad</span>`,
                className: 'add-new btn btn-primary',
                attr: {
                  'data-bs-toggle': 'offcanvas',
                  'data-bs-target': '#offcanvasEcommercePrioridadList'
                }
              }
            ]
          }
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      },
      language: {
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // Para popup responsive
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Detalles de prioridad: ' + data['nombre'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== ''
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              return `<div class="table-responsive"><table class="table"><tbody>${data}</tbody></table></div>`;
            }
            return false;
          }
        }
      }
    });
  }

  // Manejar eventos de botones después de cargar datos
  $(dt_prioridades_list_table).on('click', '.btnEditarPrioridad', function() {
    var idPrioridad = $(this).attr('idPrioridad');
    
    // Cargar datos de la prioridad para editar
    $.ajax({
      url: "ajax/prioridades.ajax.php",
      method: "POST",
      data: {
        idPrioridad: idPrioridad
      },
      dataType: "json",
      success: function(respuesta) {
        $("#idPrioridad").val(respuesta["id"]);
        $("#editarPrioridad").val(respuesta["nombre"]);
        $("#editarTiempoRespuesta").val(respuesta["tiempo_respuesta"]);
        $("#editarColor").val(respuesta["color"]);
      },
      error: function(xhr, status, error) {
        console.error("Error al cargar datos:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudieron cargar los datos de la prioridad"
        });
      }
    });
  });

  $(dt_prioridades_list_table).on('click', '.btnEliminarPrioridad', function() {
    var idPrioridad = $(this).attr('idPrioridad');
    
    Swal.fire({
      title: '¿Está seguro de borrar la prioridad?',
      text: '¡Si no lo está puede cancelar la acción!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar prioridad!'
    }).then(function(result) {
      if (result.value) {
        window.location = 'index.php?ruta=prioridades&idPrioridad=' + idPrioridad;
      }
    });
  });

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
  const eCommercePrioridadListForm = document.getElementById('eCommercePrioridadListForm');
  const editarPrioridadForm = document.getElementById('editarPrioridadForm');

  // Validación del formulario para agregar nueva prioridad
  if (eCommercePrioridadListForm) {
    const fv = FormValidation.formValidation(eCommercePrioridadListForm, {
      fields: {
        prioridadTitle: {
          validators: {
            notEmpty: {
              message: 'Por favor ingrese el nombre de la prioridad'
            }
          }
        },
        tiempoRespuesta: {
          validators: {
            notEmpty: {
              message: 'El tiempo de respuesta es requerido'
            },
            numeric: {
              message: 'El valor debe ser numérico'
            },
            between: {
              min: 1,
              max: 168,
              message: 'El tiempo debe estar entre 1 y 168 horas'
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
      var nombre = $("#ecommerce-prioridad-title").val();
      var tiempoRespuesta = $("#ecommerce-tiempo-respuesta").val();
      var color = $("#html5-color-input").val();
      
      $.ajax({
        url: "ajax/prioridades.ajax.php",
        method: "POST",
        data: {
          nuevaPrioridad: nombre,
          nuevoTiempoRespuesta: tiempoRespuesta,
          nuevoColor: color
        },
        success: function(respuesta) {
          if (respuesta === "ok") {
            Swal.fire({
              icon: "success",
              title: "¡La prioridad ha sido guardada correctamente!",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            }).then(function(result) {
              if (result.value) {
                window.location = "prioridades";
              }
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error al guardar",
              text: respuesta
            });
          }
        },
        error: function(xhr, status, error) {
          console.error("Error AJAX:", error);
          Swal.fire({
            icon: "error",
            title: "Error de comunicación",
            text: "No se pudo conectar con el servidor"
          });
        }
      });
    });
  }

  
})();